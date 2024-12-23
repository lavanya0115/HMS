<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventVisitor extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'event_id',
        'visitor_id',
        'is_visited',
        'product_looking',
        'delegate_id',
        'seminars_to_attend',
        'is_delegates',
        '_meta',
        'visited_at',
        'registration_type',
        'known_source',
        'is_lead_converted',
        'who_converted'
    ];

    protected $logAttributes = [
        'event_id',
        'visitor_id',
        'is_visited',
        'product_looking',
        'delegate_id',
        'seminars_to_attend',
        'is_delegates',
        '_meta',
        'visited_at',
        'registration_type',
        'known_source',
        'is_lead_converted',
        'who_converted',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'event_visitor_log';

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
        'product_looking' => 'json',
        '_meta' => 'json',
        'seminars_to_attend' => 'json',
    ];

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'visitor_id', 'visitor_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_looking', 'id');
    }

    public function getProductNames()
    {
        $productIds = $this->product_looking;

        if (is_array($productIds) && count($productIds) > 0) {
            $productNames = Product::whereIn('id', $productIds)->pluck('name')->toArray();
            return implode(', ', $productNames);
        }

        return null;
    }
    public function getSeminarNames()
    {
        $seminarsIds = $this->seminars_to_attend;

        if (is_array($seminarsIds) && count($seminarsIds) > 0) {
            $seminarNames = Seminar::whereIn('id', $seminarsIds)->pluck('title')->toArray();
            // dd($seminarNames);
            return implode(', ', $seminarNames);
        }
        return null;
    }
}
