<?php

namespace App\Http\Controllers;

use App\Exceptions\QuoteValidationException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\CayanQuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function __construct(private CayanQuoteService $cayan) {}

    public function index()
    {
        return response()->json(Invoice::orderBy('id', 'desc')->get());
    }

    public function show($id)
    {
        return response()->json(Invoice::with('items')->findOrFail($id));
    }

    public function pdf($id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);

        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice])
            ->setPaper('a4');

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    /**
     * GET /api/quotes/{id}/verify
     *
     * Standalone check: "is this quote valid right now?" without creating
     * anything. Useful for a UI to show a green/red badge before the user
     * even clicks "create invoice".
     */
    public function verifyQuote(Request $request, $id)
    {
        try {
            $quote = $this->cayan->verifyQuote($id);

            return response()->json([
                'valid' => true,
                'quote' => $quote,
            ]);
        } catch (QuoteValidationException $e) {
            return response()->json([
                'valid'  => false,
                'reason' => $e->reason,
                'error'  => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/invoices  { "quote_id": 123 }
     *
     * Re-verifies the quote against cayan-l (never trusts a stale/cached
     * verification) and only then creates the invoice from it.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'quote_id' => 'required',
        ]);

        try {
            $quote = $this->cayan->verifyQuote($validated['quote_id']);
        } catch (QuoteValidationException $e) {
            return response()->json([
                'success' => false,
                'reason'  => $e->reason,
                'error'   => $e->getMessage(),
            ], 422);
        }

        $invoice = DB::transaction(function () use ($quote) {
            $invoice = Invoice::create([
                'invoice_number'  => Invoice::nextNumber(),
                'cayan_quote_id'  => $quote['id'],
                'quote_number'    => $quote['number'] ?? null,
                'client_name'     => $quote['client_name'] ?? '',
                'subtotal'        => $quote['subtotal'] ?? 0,
                'vat_rate'        => $quote['vat_rate'] ?? 0,
                'vat_amount'      => $quote['vat_amount'] ?? 0,
                'total'           => $quote['total'] ?? 0,
                'status'          => 'unpaid',
                'quote_snapshot'  => $quote,
                'verified_at'     => now(),
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

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'id'      => $invoice->id,
            'number'  => $invoice->invoice_number,
        ], 201);
    }
}
