<?php

namespace App\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'gst',
        'pan',
        'is_head',
        'address_id',
        'lead_id',
        'exhibitor_id'
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'id');
    }
    public function contactPersons()
    {
        return $this->hasMany(ExhibitorContact::class, 'branch_id', 'id');
    }
    public function leadContactPerson()
    {
        return $this->hasOne(ExhibitorContact::class, 'branch_id', 'id');
    }
}
