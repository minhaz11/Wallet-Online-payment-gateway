<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    use HasFactory;
    protected $table = "agents";
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'ver_code_send_at' => 'datetime'
    ];

    protected $guarded = [];

    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class,'user_id')->where('user_type','AGENT');
    }

    public function qrCode()
    {
        return $this->hasOne(QRcode::class,'user_id')->where('user_type','AGENT');
    }

    public function login_logs()
    {
        return $this->hasMany(UserLogin::class,'agent_id');
    }
   
    public function transactions()
    {
        return $this->hasMany(Transaction::class,'user_id')->orderBy('id','desc')->where('user_type','AGENT');
    }


    public function deposits()
    {
        return $this->hasMany(Deposit::class,'user_id')->where('status','!=',0)->where('user_type','AGENT');
    }
   
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class,'user_id')->where('status','!=',0)->where('user_type','AGENT');
    }

    public function scopeActive()
    {
        return $this->where('status', 1);
    }

    public function scopeBanned()
    {
        return $this->where('status', 0);
    }

    public function scopeEmailUnverified()
    {
        return $this->where('ev', 0);
    }

    public function scopeSmsUnverified()
    {
        return $this->where('sv', 0);
    }
    public function scopeEmailVerified()
    {
        return $this->where('ev', 1);
    }

    public function scopeSmsVerified()
    {
        return $this->where('sv', 1);
    }

    public function moneyOutMonthlyLimit()
    {
        $rate = currencyRate();
        return  $this->transactions()->where('user_type','AGENT')->where('user_id',agent()->id)->where('operation_type','money_in')->whereMonth('created_at',\Carbon\Carbon::now()->month)->selectRaw("SUM(amount * $rate) as totalAmount")->get()->sum('totalAmount');
    }

    public function moneyOutDailyLimit()
    {
        $rate = currencyRate();
        return  $this->transactions()->where('user_type','AGENT')->where('user_id',agent()->id)->where('operation_type','money_in')->whereDate('created_at',\Carbon\Carbon::now())->selectRaw("SUM(amount * $rate) as totalAmount")->get()->sum('totalAmount');
    }
}
