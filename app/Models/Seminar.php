<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seminar extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'title',
        'event_id',
        'description',
        'date',
        'start_time',
        'end_time',
        'amount',
        '_meta',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $logAttributes = [
        'title',
        'event_id',
        'description',
        'date',
        'start_time',
        'end_time',
        'amount',
        '_meta',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'seminar_log';

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
                        if (is_array($newValue)) {
                            $newValue = json_encode($newValue);
                        }
                        if (is_array($oldValue)) {
                            $oldValue = json_encode($oldValue);
                        }
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
        'is_active' => 'boolean',
        '_meta' => 'array'
    ];
}
