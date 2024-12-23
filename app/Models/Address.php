<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'pincode',
        'city',
        'state',
        'country',
        'addressable_id',
        'addressable_type',
        'is_correct_address',
        'landline_number',
        'area',
        'landmark',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

}
