<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'potential_id',
        'stall_id',
        'sales_person_id',
        'advance_amount',
        'tds_percentage',
        'tds_calculate',
        'tds_amount',
        'balance_amount',
        'proof',
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
