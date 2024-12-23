<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExhibitorProduct extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'exhibitor_id',
        'product_id',
        '_meta'
    ];

    protected $logAttributes = [
        'exhibitor_id',
        'product_id',
        '_meta'
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'exhibitor_product_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes)
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = getAuthData()->name ?? '';
                if ($eventName === 'created') {
                    $action = 'add';
                } elseif ($eventName === 'updated') {
                    $action = 'update';
                } elseif ($eventName === 'deleted') {
                    $action = 'removed';
                }
                // dd($eventName);
                $description = "Product $action by " . $userName . ' on ' . now()->toDateTimeString();

                if ($eventName === 'updated') {
                    $description .= '. Changes: ';
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
                    $description = rtrim($description, ', ');
                }

                return $description;
            });
    }

    protected $casts = [
        '_meta' => 'array'
    ];

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
