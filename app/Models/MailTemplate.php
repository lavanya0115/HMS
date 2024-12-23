<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MailTemplate extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $fillable = [
        'target_id',
        'hall_id',
        'subject',
        'message_content',
        'exhibitor_ids',
    ];

    protected $logAttributes = [
        'target_id',
        'hall_id',
        'subject',
        'message_content',
        'exhibitor_ids',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'mail_template_log';

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
        'exhibitor_ids' => 'json',
    ];
    public function exhibitors()
    {
        return $this->belongsToMany(Exhibitor::class, 'mail_template_exhibitor', 'mail_template_id', 'exhibitor_id');
    }
}
