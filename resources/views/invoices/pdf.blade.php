<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .company { color: #b8860b; font-weight: bold; font-style: italic; font-size: 18px; }
        .contact { text-align: right; font-size: 11px; }
        h1 { text-align: center; font-size: 24px; letter-spacing: 1px; margin: 20px 0; }
        .meta td { border: none; padding: 2px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; background: #f3f3f3; padding: 8px; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .section-row td { font-weight: bold; background: #fafafa; }
        .right { text-align: right; }
        .totals { width: 260px; margin-left: auto; margin-top: 20px; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; }
        .status.unpaid { background: #fde2e2; color: #b42318; }
        .status.paid { background: #d1fae5; color: #027a48; }
        .status.void { background: #eee; color: #666; }
        .terms { margin-top: 30px; font-size: 11px; }
        .terms ul { padding-left: 16px; }
    </style>
</head>
<body>
    @php $q = $invoice->quote_snapshot ?? []; @endphp

    <div class="header">
        <div class="company">Cayan Events Ke.</div>
        <div class="contact">
            <div><strong>Phone:</strong> 0737 611 658</div>
            <div><strong>E-mail:</strong> cayaneventsanddecor@gmail.com</div>
            <div><strong>Address:</strong> Mokoyeti West Road, Karen</div>
        </div>
    </div>

    <h1>INVOICE</h1>

    <table class="meta">
        <tr><td><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</td></tr>
        <tr><td><strong>Quote Ref:</strong> {{ $invoice->quote_number }}</td></tr>
        <tr><td><strong>Bill to:</strong> {{ $invoice->client_name }}</td></tr>
        @if(!empty($q['venue']))
        <tr><td><strong>Venue:</strong> {{ $q['venue'] }}</td></tr>
        @endif
        @if(!empty($q['no_of_guests']))
        <tr><td><strong>No of Guests:</strong> {{ $q['no_of_guests'] }}</td></tr>
        @endif
        <tr><td><strong>Date:</strong> {{ $invoice->created_at->format('d/m/Y') }}</td></tr>
        @if(!empty($q['contact_person']))
        <tr><td><strong>Contact Person:</strong> {{ $q['contact_person'] }}</td></tr>
        @endif
        <tr><td><strong>Status:</strong> <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td></tr>
    </table>

    <table>
        <thead>
            <tr><th>Qty</th><th>Description</th><th class="right">Unit Price</th><th class="right">Price</th></tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                @if($item->type === 'section' || $item->type === 'subsection')
                    <tr class="section-row"><td colspan="4">{{ $item->name }}</td></tr>
                @else
                    <tr>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->name }}</td>
                        <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">{{ number_format($item->price, 2) }}</td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="4">No items on this invoice.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr><td>TOTAL</td><td class="right">{{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>VAT {{ $invoice->vat_rate }}%</td><td class="right">{{ number_format($invoice->vat_amount, 2) }}</td></tr>
        <tr class="grand"><td>TOTAL VAT INC.</td><td class="right">{{ number_format($invoice->total, 2) }}</td></tr>
    </table>

    <div class="terms">
        <strong>Terms and Conditions</strong>
        <ul>
            <li>Full payment before delivery.</li>
            <li>The client agrees to forfeit the full deposit paid upon booking against cancellation of an order.</li>
            <li>The company has no obligation to deliver until full payment has been made.</li>
        </ul>
    </div>
</body>
</html>