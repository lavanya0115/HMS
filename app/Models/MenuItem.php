<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'qty',
        'unit_type',
        'price',
        'custom_status',
        'meta',
        'is_available',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
