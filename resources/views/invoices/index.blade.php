@extends('layouts.app')
@section('content')
<div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="card" style="flex:1;min-width:180px;margin:0;">
        <div class="muted">Outstanding ({{ $summary['unpaid_count'] }})</div>
        <div style="font-size:22px;font-weight:700;color:#b42318;">{{ number_format($summary['outstanding'], 2) }}</div>
    </div>
    <div class="card" style="flex:1;min-width:180px;margin:0;">
        <div class="muted">Paid ({{ $summary['paid_count'] }})</div>
        <div style="font-size:22px;font-weight:700;color:#027a48;">{{ number_format($summary['paid'], 2) }}</div>
    </div>
</div>

<form method="GET" action="/invoices" class="card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <input type="text" name="q" value="{{ $search }}" placeholder="Search client, invoice no. or quote ref"
           style="flex:1;min-width:200px;padding:8px;border:1px solid #ccc;border-radius:6px;">
    <select name="status" style="padding:8px;border:1px solid #ccc;border-radius:6px;">
        <option value="">All statuses</option>
        <option value="unpaid" @selected($status === 'unpaid')>Unpaid</option>
        <option value="paid" @selected($status === 'paid')>Paid</option>
    </select>
    <button type="submit" class="btn" style="border:none;cursor:pointer;">Search</button>
    @if($search !== '' || $status !== '')
        <a class="btn secondary" href="/invoices">Clear</a>
    @endif
</form>
<div class="card">
    <h2>Invoices</h2>
    @if($invoices->count() === 0)
        <p class="empty">No invoices yet.</p>
    @else
    <table>
        <thead>
            <tr><th>Number</th><th>Client</th><th>Total</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td><a class="row-link" href="/invoices/{{ $invoice->id }}">{{ $invoice->invoice_number }}</a></td>
                <td>{{ $invoice->client_name }}</td>
                <td>{{ number_format($invoice->total, 2) }}</td>
                <td><span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span></td>
                <td>{{ $invoice->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@if($voided->count() > 0)
<div class="card">
    <h2 class="muted">Voided (quote no longer valid)</h2>
    <table>
        <thead>
            <tr><th>Number</th><th>Client</th><th>Total</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
            @foreach($voided as $invoice)
            <tr>
                <td><a class="row-link" href="/invoices/{{ $invoice->id }}">{{ $invoice->invoice_number }}</a></td>
                <td>{{ $invoice->client_name }}</td>
                <td>{{ number_format($invoice->total, 2) }}</td>
                <td><span class="status void">VOID</span></td>
                <td>{{ $invoice->created_at->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection