<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "transactions";

    protected  $guarded = ['id'];
  
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function receiverUser()
    {
        return $this->belongsTo(User::class,'receiver_id');
    }

    public function receiverAgent()
    {
        return $this->belongsTo(Agent::class,'receiver_id');
    }

    public function receiverMerchant()
    {
        return $this->belongsTo(merchant::class,'receiver_id');
    }

    public function getReceiverAttribute()
    {
        $user = null;
        if($this->receiverUser){
            $user = $this->receiverUser;
        } else if($this->receiverAgent){
            $user = $this->receiverAgent;
        } else if($this->receiverMerchant){
            $user = $this->receiverMerchant;
        }
        return $user;
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class,'user_id');
    }
    public function merchant()
    {
        return $this->belongsTo(Merchant::class,'user_id');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function($trx)
        {
           if($trx->charge > 0 || $trx->remark == 'commission'){
               $chargeLog = new ChargeLog();
               $chargeLog->user_id = $trx->user_id;
               $chargeLog->user_type = $trx->user_type;
               $chargeLog->amount = $trx->charge == 0 ? '-'.$trx->amount : $trx->charge;
               $chargeLog->currency_id = $trx->currency_id;
               $chargeLog->trx = $trx->trx;
               $chargeLog->operation_type = $trx->operation_type;
               $chargeLog->remark = $trx->remark;
               $chargeLog->save();
           }
        });
    }

}
