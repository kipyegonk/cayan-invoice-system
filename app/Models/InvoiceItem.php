<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',       // section | subsection | item  (mirrors QuoteItem in cayan-l)
        'section',
        'subsection',
        'name',
        'qty',
        'unit_price',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'qty'        => 'float',
        'unit_price' => 'float',
        'price'      => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
