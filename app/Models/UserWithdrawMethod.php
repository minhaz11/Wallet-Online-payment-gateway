<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWithdrawMethod extends Model
{
    use HasFactory;
    protected $casts = [
        'user_data' => 'object'

    ];
    public function withdrawMethod()
    {
        return $this->belongsTo(WithdrawMethod::class,'method_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

   
}
