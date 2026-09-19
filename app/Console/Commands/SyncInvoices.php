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

                $seenQuoteIds = [];

        foreach ($quotes as $quoteData) {
            $quoteId = $quoteData['id'];
            $seenQuoteIds[] = $quoteId;

            $existingInvoice = Invoice::where('cayan_quote_id', $quoteId)->first();

            try {
                $quote = $cayan->verifyQuote($quoteId);
            } catch (QuoteValidationException $e) {
                // Quote is no longer invoiceable (status changed, expired, etc.)
                if ($existingInvoice && $existingInvoice->status === 'unpaid') {
                    $existingInvoice->update(['status' => 'void']);
                    $this->warn("Voided invoice {$existingInvoice->invoice_number} - quote {$quoteId} no longer invoiceable ({$e->reason}).");
                }
                continue;
            }

                        // Quote is currently invoiceable
            if ($existingInvoice) {
                $wasVoid = $existingInvoice->status === 'void';

                DB::transaction(function () use ($existingInvoice, $quote, $wasVoid) {
                    $existingInvoice->update([
                        'status'         => 'unpaid',
                        'quote_snapshot' => $quote,
                        'verified_at'    => now(),
                        'client_name'    => $quote['client_name'] ?? $existingInvoice->client_name,
                        'subtotal'       => $quote['subtotal'] ?? 0,
                        'vat_rate'       => $quote['vat_rate'] ?? 0,
                        'vat_amount'     => $quote['vat_amount'] ?? 0,
                        'total'          => $quote['total'] ?? 0,
                    ]);

                    // Replace items with the latest from cayan-l
                    $existingInvoice->items()->delete();
                    foreach ($quote['items'] ?? [] as $item) {
                        InvoiceItem::create([
                            'invoice_id' => $existingInvoice->id,
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

                if ($wasVoid) {
                    $this->info("Reinstated invoice {$existingInvoice->invoice_number} - quote {$quoteId} is invoiceable again.");
                } else {
                    $this->line("Refreshed invoice {$existingInvoice->invoice_number} from quote {$quoteId}.");
                }
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