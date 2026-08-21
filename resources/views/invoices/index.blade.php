@extends('layouts.app')
@section('content')
<div class="card">
    <h2>Invoices</h2>
    @if($invoices->count() === 0)
        <p class="empty">No invoices yet..</p>
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
@endsection