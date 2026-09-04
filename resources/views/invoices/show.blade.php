@extends('layouts.app')
@section('content')
@php
    $q = $invoice->quote_snapshot ?? [];
@endphp
<div class="card" style="max-width:750px;margin:24px auto;">

    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
        <div>
            <div style="color:#b8860b;font-weight:700;font-style:italic;font-size:20px;">Cayan Events Ke.</div>
        </div>
        <div style="text-align:right;font-size:13px;">
            <div><strong>Phone:</strong> 0737 611 658</div>
            <div><strong>E-mail:</strong> cayaneventsanddecor@gmail.com</div>
            <div><strong>Address:</strong> Mokoyeti West Road, Karen</div>
        </div>
    </div>

    <h1 style="text-align:center;font-size:26px;letter-spacing:1px;margin:20px 0;">INVOICE</h1>

    <table style="margin-bottom:20px;">
        <tr><td style="border:none;padding:2px 0;"><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</td></tr>
        <tr><td style="border:none;padding:2px 0;"><strong>Quote Ref:</strong> {{ $invoice->quote_number }}</td></tr>
        <tr><td style="border:none;padding:2px 0;"><strong>Bill to:</strong> {{ $invoice->client_name }}</td></tr>
        @if(!empty($q['venue']))
        <tr><td style="border:none;padding:2px 0;"><strong>Venue:</strong> {{ $q['venue'] }}</td></tr>
        @endif
        @if(!empty($q['no_of_guests']))
        <tr><td style="border:none;padding:2px 0;"><strong>No of Guests:</strong> {{ $q['no_of_guests'] }}</td></tr>
        @endif
        <tr><td style="border:none;padding:2px 0;"><strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y') }}</td></tr>
        @if(!empty($q['contact_person']))
        <tr><td style="border:none;padding:2px 0;"><strong>Contact Person:</strong> {{ $q['contact_person'] }}</td></tr>
        @endif
        <tr><td style="border:none;padding:2px 0;"><strong>Status:</strong> <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td></tr>
    </table>

    <table class="items">
        <thead>
            <tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Price</th></tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                @if($item->type === 'section' || $item->type === 'subsection')
                <tr class="section-row"><td colspan="4">{{ $item->name }}</td></tr>
                @else
                <tr>
                    <td>{{ $item->qty }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                </tr>
                @endif
            @empty
                <tr><td colspan="4" class="muted">No items on this invoice.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>TOTAL</td><td>{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>VAT {{ $invoice->vat_rate }}%</td><td>{{ number_format($invoice->vat_amount, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL VAT INC.</td><td>{{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    <div style="margin-top:40px;font-size:13px;">
        <strong>Terms and Conditions</strong>
        <ul style="padding-left:18px;color:#333;">
            <li>Full payment before delivery.</li>
            <li>The client agrees to forfeit the full deposit paid upon booking against cancellation of an order.</li>
            <li>The company has no obligation to deliver until full payment has been made.</li>
        </ul>
    </div>
    <div style="margin-top:40px;">
        <div>Regards,</div>
        <div>{{ $q['contact_person'] ?? '' }}</div>
    </div>

    <table style="width:100%;margin-top:30px;border:none;">
        <tr>
            <td style="border:none;width:50%;">
                Signature:<br><br>
                <div style="border-bottom:1px solid #333;width:200px;">&nbsp;</div>
            </td>
            <td style="border:none;">
                Date:<br>
                <div style="border-bottom:1px solid #333;width:200px;">{{ \Carbon\Carbon::parse($q['quote_date'] ?? $invoice->created_at)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
    <div class="actions">
        <a class="btn" href="/invoices/{{ $invoice->id }}/pdf">Download PDF</a>
        <a class="btn secondary" href="/invoices">Back to list</a>
    </div>
</div>
@endsection