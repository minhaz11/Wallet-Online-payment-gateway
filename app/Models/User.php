<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'address' => 'object',
        'ver_code_send_at' => 'datetime'
    ];

    protected $data = [
        'data'=>1
    ];

    public function averageBalance()
    {
        return $this->wallets()->sum('balance');
    }


    public function wallets()
    {
        return $this->hasMany(Wallet::class,'user_id')->where('user_type','USER');
    }
    
    public function qrCode()
    {
        return $this->hasOne(QRcode::class,'user_id')->where('user_type','USER');
    }

    public function login_logs()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('id','desc')->where('user_type','USER');
    }

    public function moneyOutMonthlyLimit()
    {
        $rate = currencyRate();
        return  $this->transactions()->where('user_type','USER')->where('user_id',auth()->id())->where('operation_type','money_out')->whereMonth('created_at',\Carbon\Carbon::now()->month)->selectRaw("SUM(amount * $rate) as totalAmount")->get()->sum('totalAmount');
    }

    public function moneyOutDailyLimit()
    {
        $rate = currencyRate();
        return  $this->transactions()->where('user_type','USER')->where('user_id',auth()->id())->where('operation_type','money_out')->whereDate('created_at',\Carbon\Carbon::now())->selectRaw("SUM(amount * $rate) as totalAmount")->get()->sum('totalAmount');
    }

    public function dailyTransferLimit()
    {
        $rate = currencyRate();
        return  $this->transactions()->where('user_type','USER')->where('user_id',auth()->id())->where('operation_type','transfer_money')->whereDate('created_at',\Carbon\Carbon::now())->selectRaw("SUM(amount * $rate) as totalAmount")->get()->sum('totalAmount');
    }

    public function dailyVoucherLimit()
    {
        return  $this->transactions()->where('user_type','USER')->where('user_id',auth()->id())->where('operation_type','create_voucher')->whereDate('created_at',\Carbon\Carbon::now())->count();
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class)->where('status','!=',0)->where('user_type','USER');
    }
    public function vouchers()
    {
        return $this->hasMany(Voucher::class,'user_id')->where('user_type','USER');
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class)->where('status','!=',0);
    }


    // SCOPES

    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
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

}
