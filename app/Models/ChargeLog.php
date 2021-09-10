<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeLog extends Model
{
    use HasFactory;

    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function agent()
    {
        return  $this->belongsTo(Agent::class,'user_id');
    }
    public function merchant()
    {
        return  $this->belongsTo(Merchant::class,'user_id');
    }
}
