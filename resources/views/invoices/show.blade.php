@extends('layouts.app')
@section('content')
<div class="card" style="max-width:700px;margin:24px auto;">

    <div style="display:flex;justify-content:space-between;margin-bottom:30px;">
        <div>
            <h1 style="font-size:22px;margin:0 0 4px 0;">INVOICE</h1>
            <div class="muted">{{ $invoice->invoice_number }}</div>
            <div class="muted">Date: {{ $invoice->created_at->format('d M Y') }}</div>
        </div>
        <div style="text-align:right;">
            <div><strong>Billed to:</strong></div>
            <div>{{ $invoice->client_name }}</div>
            @if($invoice->quote_number)
                <div class="muted">Quote ref: {{ $invoice->quote_number }}</div>
            @endif
            <div style="margin-top:8px;">
                <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Amount</th></tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                @if($item->type === 'section' || $item->type === 'subsection')
                <tr class="section-row"><td colspan="4">{{ $item->name }}</td></tr>
                @else
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td>{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>VAT ({{ $invoice->vat_rate }}%)</td><td>{{ number_format($invoice->vat_amount, 2) }}</td></tr>
        <tr class="grand"><td>Total</td><td>{{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    <div class="actions">
        <a class="btn" href="/invoices/{{ $invoice->id }}/pdf">Download PDF</a>
        <a class="btn secondary" href="/invoices">Back to list</a>
    </div>
</div>
@endsection