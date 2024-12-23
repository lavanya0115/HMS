<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'invoice_id',
        'item_name',
        'stall_id',
        'rate',
        'gross_value',
        'dis_type',
        'dis_rate',
        'discount',
        'taxable_value',
        'tax_category',
        'tax_value',
        'net_value',
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
