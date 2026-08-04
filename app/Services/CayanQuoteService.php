<?php

namespace App\Services;

use App\Exceptions\QuoteValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the cayan-l quoting system's API and decides whether a given
 * quote is valid enough to be turned into an invoice.
 *
 * cayan-l has no dedicated "verify" endpoint, so "valid" here means:
 *   - the quote actually exists (200, not 404)
 *   - its status is one we accept for invoicing (config: cayan.invoiceable_statuses)
 *   - valid_until has not passed
 *   - (optional) the total the caller expects to invoice matches the quote total
 */
class CayanQuoteService
{
   private string $baseUrl;
    private ?string $staticToken;
    private ?string $email;
    private ?string $password;
    /** @var array<int,string> */
    private array $invoiceableStatuses;

    public function __construct()
    {
        $this->baseUrl             = rtrim(config('services.cayan.base_url'), '/');
         $this->staticToken         = config('services.cayan.token') ?: null;
        $this->email               = config('services.cayan.email');
        $this->password            = config('services.cayan.password');
        $this->invoiceableStatuses = config('services.cayan.invoiceable_statuses', ['accepted', 'approved']);
    }

    /**
     * Fetch a quote from cayan-l and run it through validation rules.
     *
     * @param int|string $quoteId          The cayan-l quote id (or number, see resolveId()).
     * @param float|null $expectedTotal    If provided, the quote total must match this
     *                                      (within a small tolerance) or validation fails.
     * @return array The raw quote payload from cayan-l, if valid.
     * @throws QuoteValidationException on any validation failure.
     */
    public function verifyQuote(int|string $quoteId, ?float $expectedTotal = null): array
    {
        $quote = $this->fetchQuote($quoteId);

        $this->assertStatusIsInvoiceable($quote);
        $this->assertNotExpired($quote);

        if ($expectedTotal !== null) {
            $this->assertTotalMatches($quote, $expectedTotal);
        }

        return $quote;
    }

    /**
     * GET /api/quotes/{id} from cayan-l, authenticating (and retrying once
     * on an expired token) as needed.
     *
     * @throws QuoteValidationException if the quote doesn't exist or the API call fails.
     */
    public function fetchQuote(int|string $quoteId): array
    {
        $response = $this->authedRequest()->get("{$this->baseUrl}/api/quotes/{$quoteId}");

        // Token might have expired between calls even though we cached it -
        // force a fresh login once and retry before giving up.
        if ($response->status() === 401) {
            $response = $this->authedRequest(forceRefresh: true)
                ->get("{$this->baseUrl}/api/quotes/{$quoteId}");
        }

        if ($response->status() === 404) {
            throw new QuoteValidationException(
                'quote_not_found',
                "No quote with id/number \"{$quoteId}\" exists in cayan-l."
            );
        }

        if (! $response->successful()) {
            Log::error('cayan-l quote lookup failed', [
                'quote_id' => $quoteId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new QuoteValidationException(
                'cayan_unreachable',
                "cayan-l API returned an unexpected error (HTTP {$response->status()})."
            );
        }

        return $response->json();
    }

    private function assertStatusIsInvoiceable(array $quote): void
    {
        $status = strtolower($quote['status'] ?? '');

        if (! in_array($status, $this->invoiceableStatuses, true)) {
            throw new QuoteValidationException(
                'quote_status_not_invoiceable',
                "Quote #{$this->quoteLabel($quote)} has status \"{$status}\", which is not invoiceable. "
                    . 'Allowed statuses: ' . implode(', ', $this->invoiceableStatuses) . '.'
            );
        }
    }

    private function assertNotExpired(array $quote): void
    {
        $validUntil = $quote['valid_until'] ?? null;

        if (! $validUntil) {
            // No expiry set on the quote - treat as non-expiring, nothing to check.
            return;
        }

        if (now()->startOfDay()->gt(\Illuminate\Support\Carbon::parse($validUntil)->endOfDay())) {
            throw new QuoteValidationException(
                'quote_expired',
                "Quote #{$this->quoteLabel($quote)} expired on {$validUntil} and can no longer be invoiced."
            );
        }
    }

    private function assertTotalMatches(array $quote, float $expectedTotal): void
    {
        $quoteTotal = (float) ($quote['total'] ?? 0);
        $tolerance  = 0.01; // guard against float rounding, not real mismatches

        if (abs($quoteTotal - $expectedTotal) > $tolerance) {
            throw new QuoteValidationException(
                'quote_total_mismatch',
                "Quote #{$this->quoteLabel($quote)} total is {$quoteTotal}, "
                    . "which does not match the requested invoice amount of {$expectedTotal}."
            );
        }
    }

    private function quoteLabel(array $quote): string
    {
        return $quote['number'] ?? ($quote['id'] ?? 'unknown');
    }

    /**
     * Build an HTTP client pre-authenticated with a bearer token, logging in
     * to cayan-l first if there is no cached token (or forceRefresh is set).
     */
    private function authedRequest(bool $forceRefresh = false): \Illuminate\Http\Client\PendingRequest
    {
        $token = $this->getToken($forceRefresh);

        return Http::withToken($token)->acceptJson();
    }

   
}
