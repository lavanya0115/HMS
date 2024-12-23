<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'receipt_id',
        'receipt_date',
        'potential_id',
        'event_id',
        'mode_of_payment',
        'cheque',
        'upi_no',
        'neft',
        'transaction_date',
        'amount',
        '_meta',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        '_meta' => 'array',
    ];
}
