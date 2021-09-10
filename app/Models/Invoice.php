<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public function items()
    {
        return $this->hasMany(InvoiceItem::class,'invoice_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
