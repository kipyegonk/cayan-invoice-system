<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        h1 { font-size: 22px; margin: 0 0 4px 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background: #f3f3f3; padding: 8px; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .section-row td { font-weight: bold; background: #fafafa; }
        .right { text-align: right; }
        .totals { width: 250px; margin-left: auto; margin-top: 20px; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 11px; }
        .status.unpaid { background: #fde2e2; color: #b42318; }
        .status.paid { background: #d1fae5; color: #027a48; }
        .status.void { background: #eee; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>INVOICE</h1>
            <div class="muted">{{ $invoice->invoice_number }}</div>
            <div class="muted">Date: {{ $invoice->created_at->format('d M Y') }}</div>
        </div>
        <div style="text-align: right;">
            <div><strong>Billed to:</strong></div>
            <div>{{ $invoice->client_name }}</div>
            @if($invoice->quote_number)
                <div class="muted">Quote ref: {{ $invoice->quote_number }}</div>
            @endif
            <div style="margin-top: 8px;">
                <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                @if($item->type === 'section' || $item->type === 'subsection')
                    <tr class="section-row">
                        <td colspan="4">{{ $item->name }}</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="right">{{ $item->qty }}</td>
                        <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="right">{{ number_format($item->price, 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>VAT ({{ $invoice->vat_rate }}%)</td>
            <td class="right">{{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

</body>
</html>