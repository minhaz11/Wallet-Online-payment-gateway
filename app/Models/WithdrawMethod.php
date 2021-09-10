<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawMethod extends Model
{
    protected $guarded = ['id'];
    protected $table = "withdraw_methods";

    protected $casts = [
        'user_data' => 'object',
        'user_guards' => 'object',
        'currencies' => 'object',
    ];

    public function curr()
    {
        return Currency::find($this->currencies)->pluck('currency_code','id');
       
    }
}
