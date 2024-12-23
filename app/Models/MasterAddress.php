<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'Pincode',
        'City',
        'District',
        'State',
    ];
}
