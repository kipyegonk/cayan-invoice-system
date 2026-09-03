<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
        <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .company { color: #8B4513; font-weight: bold; font-style: italic; font-size: 20px; }
        .contact { text-align: right; font-size: 11px; }
        h1 { text-align: center; font-size: 28px; letter-spacing: 2px; margin: 25px 0; font-weight: bold; }
        .meta td { border: none; padding: 3px 0; font-size: 12px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #999; }
        table.items th { text-align: left; background: #f3f3f3; color: #7B241C; padding: 8px; font-size: 11px; text-transform: uppercase; border: 1px solid #999; }
        table.items td { padding: 8px; border: 1px solid #ccc; }
        .section-row td { font-weight: bold; background: #fafafa; }
        .right { text-align: right; }
        .totals { width: 280px; margin-left: auto; margin-top: 20px; border: 1px solid #999; border-collapse: collapse; }
        .totals td { padding: 6px 10px; border: 1px solid #999; }
        .totals .grand { font-weight: bold; background: #f3f3f3; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; }
        .status.unpaid { background: #fde2e2; color: #b42318; }
        .status.paid { background: #d1fae5; color: #027a48; }
        .status.void { background: #eee; color: #666; }
        .terms { margin-top: 30px; font-size: 11px; }
        .terms strong { color: #7B241C; }
        .terms ul { list-style: none; padding-left: 0; }
        .terms li { padding-left: 18px; position: relative; margin-bottom: 6px; }
        .terms li:before { content: "\2610"; position: absolute; left: 0; }
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