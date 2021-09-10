<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPayment extends Model
{
    use HasFactory;

    public function curr()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class,'merchant_id');
    }

    public function payer()
    {
        return $this->belongsTo(User::class,'payer_id');
    }
}
