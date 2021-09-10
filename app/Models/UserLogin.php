<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class,'agent_id');
    }
    public function merchant()
    {
        return $this->belongsTo(Merchant::class,'merchant_id');
    }
}
