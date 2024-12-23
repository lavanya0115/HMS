<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventExhibitor extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'event_id',
        'exhibitor_id',
        'stall_no',
        'is_sponsorer',
        'products',
        'tags',
        'order',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
        'is_active',
        'hall_no',
        '_meta'
    ];

    protected $logAttributes = [
        'event_id',
        'exhibitor_id',
        'stall_no',
        'is_sponsorer',
        'products',
        'tags',
        'order',
        'cancelled_by',
        'cancelled_at',
        'cancelled_reason',
        'is_active',
        '_meta'
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'event_exhibitor_log';

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
                    $description .= '--by ' .  $userName  . ' on ' . now()->toDateTimeString();
                    $description = rtrim($description, ', ');
                } elseif ($eventName === 'created') {
                    $description .= 'Record created --by' .  $userName  . ' on ' . now()->toDateTimeString();
                } elseif ($eventName === 'deleted') {
                    $description .= 'Record deleted --by' .  $userName  . ' on ' . now()->toDateTimeString();
                }
                return $description;
            });
    }

    protected $casts = [
        'products' => 'json',
        'tags' => 'json',
        '_meta' => 'json',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class, 'exhibitor_id');
    }

    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'products', 'id');
    }

    public function getProductNames()
    {
        $productIds = $this->products;
        if (is_array($productIds) && count($productIds) > 0) {
            $productNames = Product::whereIn('id', $productIds)->pluck('name')->toArray();
            return implode(', ', $productNames);
        }
        return null;
    }
}
