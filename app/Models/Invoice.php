<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'cayan_quote_id',
        'quote_number',
        'client_name',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'status',
        'quote_snapshot', // full JSON of the cayan-l quote at time of verification, for audit
        'verified_at',
    ];

    protected $casts = [
        'subtotal'       => 'float',
        'vat_rate'       => 'float',
        'vat_amount'     => 'float',
        'total'          => 'float',
        'quote_snapshot' => 'array',
        'verified_at'    => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function nextNumber(): string
    {
        $last = self::orderBy('id', 'desc')->value('invoice_number');
        if (! $last) {
            return 'INV-0001';
        }
        preg_match('/(\d+)$/', $last, $m);
        $n = isset($m[1]) ? intval($m[1]) + 1 : 1;

        return 'INV-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
