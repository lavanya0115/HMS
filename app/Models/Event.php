<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'organizer',
        'contact',
        'description',
        '_meta',
        'event_description',
        'event_period',
        'event_code',
        'invoice_title',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // log the specified attributes
    protected $logAttributes = [
        'title',
        'start_date',
        'end_date',
        'organizer',
        'contact',
        'description',
        '_meta',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'event_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }

    protected $casts = [
        '_meta' => 'json',
    ];

    protected $dates = ['deleted_at', 'start_date', 'end_date'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function visitors()
    {
        return $this->hasMany(EventVisitor::class);
    }

    public function exhibitors()
    {
        return $this->hasMany(EventExhibitor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
