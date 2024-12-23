<?php

namespace App\Models;

use App\Models\ExhibitorProduct;
// use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'image',
        'tags',
        '_meta',
        'created_by',
        'updated_by',
        'deleted_by',

    ];

    protected $logAttributes = [
        'category_id',
        'name',
        'description',
        'image',
        'tags',
        '_meta',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'product_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes)
            ->setDescriptionForEvent(function (string $eventName) {
                $description = "Changes: ";
                $userName = getAuthData()->name ?? '';
                if ($eventName === 'updated') {
                    $changes = $this->getDirty();
                    foreach ($changes as $attribute => $newValue) {
                        $oldValue = $this->getOriginal($attribute);
                        $description .= "$attribute changed from '$oldValue' to '$newValue', ";
                    }
                    $description .= '--by ' . $userName . ' on ' . now()->toDateTimeString();
                    $description = rtrim($description, ', ');
                } elseif ($eventName === 'created') {
                    $description .= 'Record created --by' . $userName . ' on ' . now()->toDateTimeString();
                } elseif ($eventName === 'deleted') {
                    $description .= 'Record deleted --by' . $userName . ' on ' . now()->toDateTimeString();
                }
                return $description;
            });
    }

    protected $casts = [
        'tags' => 'array',
        '_meta' => 'array',
    ];

    public function categoryName()
    {
        return $this->belongsTo(Category::class, 'category_id')->where('type', 'product_type');
    }
    public function exhibitorProduct()
    {
        return $this->hasMany(ExhibitorProduct::class);
    }
}
