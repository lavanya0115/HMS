<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'potential_id',
        'status',
        'activity_type',
        'remarks',
        'contact_mode',
        '_meta',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function potential()
    {
        return $this->hasOne(Potential::class, 'potential_id');
    }
}
