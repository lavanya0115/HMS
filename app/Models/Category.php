<?php

namespace App\Models;

use App\Models\User;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $casts = [
        '_meta' => 'array',
    ];

    protected $fillable = [
        'type',
        'title',
        'description',
        'is_active',
        'is_default',
        'parent_id',
        'created_by',
        'updated_by',
        'deleted_by',
        '_meta',
    ];

    protected $logAttributes = [
        'type',
        'name',
        'description',
        'is_active',
        'is_default',
        'parent_id',
        '_meta',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'category_log';

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->select('id', 'name');
    }

    public function updator()
    {
        return $this->belongsTo(User::class, 'updated_by')->select('id', 'name');
    }
}
