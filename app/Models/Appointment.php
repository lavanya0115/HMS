<?php

namespace App\Models;

use App\Models\EventVisitor;
use App\Models\EventExhibitor;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'event_id',
        'visitor_id',
        'exhibitor_id',
        'scheduled_at',
        'status',
        'notes',
        '_meta',
        'created_by',
        'created_type',
        'updated_by',
        'updated_type',
        'cancelled_by',
        'cancelled_type',
        'cancelled_at',
        'source',
        'deleted_by',
        'deleted_type',
        'completable_id',
        'completable_type',
        'completed_at'
    ];

    protected $logAttributes = [
        'event_id',
        'visitor_id',
        'exhibitor_id',
        'scheduled_at',
        'status',
        'notes',
        '_meta',
        'updated_by',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'appointment_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }

    protected $casts = [
        '_meta' => 'json',
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function eventVisitorInfo()
    {
        return $this->belongsTo(EventVisitor::class, 'visitor_id', 'visitor_id');
    }

    public function eventExhibitorInfo()
    {
        return $this->belongsTo(EventExhibitor::class,'exhibitor_id','exhibitor_id');
    }
    public function completable()
    {
        return $this->morphTo();
    }

    public function cancelledBy()
    {
        return $this->morphTo();
    }

    public function deletedBy()
    {
        return $this->morphTo();
    }
}
