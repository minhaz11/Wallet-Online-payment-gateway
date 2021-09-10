<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'withdraw_information' => 'object'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return  $this->belongsTo(Agent::class,'user_id');
    }
    public function merchant()
    {
        return  $this->belongsTo(Merchant::class,'user_id');
    }
    public function curr()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }
    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }

    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'method_id');
    }

    public function scopePending()
    {
        return $this->where('status', 2);
    }

    public function scopeApproved()
    {
        return $this->where('status', 1);
    }

    public function scopeRejected()
    {
        return $this->where('status', 3);
    }
}
