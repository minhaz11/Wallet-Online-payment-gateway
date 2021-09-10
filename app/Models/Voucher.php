<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->where('user_type','USER');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }
}
