<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExhibitorContact extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'exhibitor_id',
        'salutation',
        'name',
        'contact_number',
        'designation',
        'landline_number',
        'email',
        'is_active',
        'lead_id',
        'branch_id',
        'is_primary',
        'created_by',
        'updated_by',
        'deleted_by',
        '_meta'
    ];

    protected $logAttributes = [
        'exhibitor_id',
        'salutation',
        'name',
        'contact_number',
        'designation',
        'landline_number',
        'email',
        'is_active',
        'lead_id'
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'exhibitor_contact_log';

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

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
    }
}
