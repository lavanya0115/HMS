<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProformaInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'proforma_date',
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
