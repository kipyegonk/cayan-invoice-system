<?php

namespace App\Console\Commands;

use App\Exceptions\QuoteValidationException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\CayanQuoteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncInvoices extends Command
{
    protected $signature = 'invoices:sync';
    protected $description = 'Check cayan-l for accepted quotes not yet invoiced, and create invoices for them';

    public function handle(CayanQuoteService $cayan)
    {
        $baseUrl = rtrim(config('services.cayan.base_url'), '/');
        $token = config('services.cayan.token');

        $response = Http::withToken($token)->acceptJson()->get("{$baseUrl}/api/quotes");

        if (! $response->successful()) {
            $this->error('Could not reach cayan-l: HTTP ' . $response->status());
            return 1;
        }

        $quotes = $response->json();
        $created = 0;

        foreach ($quotes as $quoteData) {
            $quoteId = $quoteData['id'];

            // Skip if we already have an invoice for this quote
            if (Invoice::where('cayan_quote_id', $quoteId)->exists()) {
                continue;
            }

            try {
                $quote = $cayan->verifyQuote($quoteId);
            } catch (QuoteValidationException $e) {
                // Not invoiceable yet (pending, expired, etc.) - skip silently
                continue;
            }

            DB::transaction(function () use ($quote) {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::nextNumber(),
                    'cayan_quote_id' => $quote['id'],
                    'quote_number'   => $quote['number'] ?? null,
                    'client_name'    => $quote['client_name'] ?? '',
                    'subtotal'       => $quote['subtotal'] ?? 0,
                    'vat_rate'       => $quote['vat_rate'] ?? 0,
                    'vat_amount'     => $quote['vat_amount'] ?? 0,
                    'total'          => $quote['total'] ?? 0,
                    'status'         => 'unpaid',
                    'quote_snapshot' => $quote,
                    'verified_at'    => now(),
                ]);

                foreach ($quote['items'] ?? [] as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'type'       => $item['type'] ?? 'item',
                        'section'    => $item['section'] ?? null,
                        'subsection' => $item['subsection'] ?? null,
                        'name'       => $item['name'] ?? null,
                        'qty'        => $item['qty'] ?? null,
                        'unit_price' => $item['unit_price'] ?? null,
                        'price'      => $item['price'] ?? null,
                        'sort_order' => $item['sort_order'] ?? 0,
                    ]);
                }
            });

            $created++;
        }

        $this->info("Sync complete. Created {$created} new invoice(s).");
        Log::info("invoices:sync created {$created} invoice(s)");

        return 0;
    }
}