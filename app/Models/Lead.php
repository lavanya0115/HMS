<?php

namespace App\Models;

use App\Models\Branch;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;
    use softDeletes;
    use HasRoles;
    use LogsActivity;

    protected $fillable = [
        'name',
        'alias_name',
        'lead_no',
        'type',
        'category',
        'currency',
        'lead_source_id',
        'director_name',
        'director_mobile_no',
        'director_email',
        'lead_status',
        'rating',
        'product_category_id',
        'product_id',
        'other_expo_participation',
        'created_by',
        'updated_by',
        'deleted_by',
        '_meta',
        'exhibitor_id',
    ];

    protected $casts = [
        '_meta' => 'array',
        'product_category_id' => 'array',
        'product_id' => 'array',
    ];

    protected $logAttributes = [
        'name',
        'alias_name',
        'lead_no',
        'type',
        'category',
        'currency',
        'lead_source_id',
        'director_name',
        'director_mobile_no',
        'director_email',
        'lead_status',
        'rating',
        'product_category_id',
        'product_id',
        'other_expo_participation',       
        '_meta',
        'exhibitor_id',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'lead_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }

    public function leadSource()
    {
        return $this->hasOne(Category::class, 'id', 'lead_source_id')
            ->where('type', 'lead_source')->select('id', 'name');
    }
    public function leadContactPerson()
    {
        return $this->hasOne(ExhibitorContact::class, 'lead_id');
    }

    public static function generateLeadNo()
    {
        $year = now()->format('y');
        $lastLead = self::where('lead_no', 'like', "lead{$year}%")
            ->orderBy('id', 'desc')
            ->first();


        $lastSequence = $lastLead ? (int) substr($lastLead->lead_no, -6) : 0;
        $newSequence = str_pad($lastSequence + 1, 6, '0', STR_PAD_LEFT);


        return "LEAD{$year}{$newSequence}";
    }
    public function productCategory()
    {
        return $this->belongsTo(Category::class, 'product_category_id', 'id');
    }

    public function branchPrimary()
    {
        return $this->hasOne(Branch::class, 'lead_id')->where('is_head', 1);
    }
    public function potential()
    {
        return $this->hasOne(Potential::class, 'lead_id');
    }
    public function contactPerson()
    {
        return $this->hasOne(ExhibitorContact::class);
    }
    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function leadContact()
    {
        return $this->hasMany(ExhibitorContact::class, 'lead_id', 'id');
    }
    public function branches()
    {
        return $this->hasMany(Branch::class, 'lead_id', 'id');
    }
    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getProductNames()
    {
        $productIds = $this->product_id;
        if (is_array($productIds) && count($productIds) > 0) {
            $productNames = Product::whereIn('id', $productIds)->pluck('name')->toArray();
            return implode(', ', $productNames);
        }
        return null;
    }
    public function getCategoryNames()
    {
        $categoryIds = $this->product_category_id;
        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categoryNames = Category::whereIn('id', $categoryIds)->pluck('name')->toArray();
            return implode(', ', $categoryNames);
        }
        return null;
    }
    public function exhibitor()
    {
        return $this->hasOne(Exhibitor::class);
    }
}
