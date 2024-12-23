<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'razorpay_payment_id',
        'razorpay_order_id',
        'razorpay_signature',
        'status',
        '_meta',
        'failure_reason',
        'payable_id',
        'payable_type',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}
