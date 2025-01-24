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
        'kannada_name',
        'category_id',
        'qty',
        'unit_type',
        'price',
        'tax',
        'tax_amount',
        'mrp',
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
        // 'category_id' => 'json',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
