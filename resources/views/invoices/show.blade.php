@extends('layouts.app')
@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;">
        <div>
            <h2>{{ $invoice->invoice_number }}</h2>
            <p class="muted">{{ $invoice->client_name }} · {{ $invoice->created_at->format('d M Y') }}</p>
        </div>
        <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
    </div>

    <table style="margin-top:20px;">
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