<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'invoice_no',
        'invoice_date',
        'invoice_type',
        'other_invoice_remarks',
        'potential_id',
        'event_id',
        'supply_type',
        'billing_address_type',
        'address',
        'gst',
        'stall_detail_id',
        'terms_conditions',
        'remarks',
        'tol_good_value',
        'discount_value',
        'tol_taxable_value',
        'tol_tax_value',
        'other_charges',
        'tol_other_tax',
        'tol_cess_value',
        'tol_invoice_value',
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
