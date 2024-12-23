<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Potential extends Model
{
    use HasFactory;
    use softDeletes;
    use LogsActivity;

    protected $fillable = [
        'event_id',
        'lead_id',
        'sales_person_id',
        'agent_id',
        'status',
        'stall_status',
        'activity_type',
        'contact_mode',
        'stall_id',
        'stall_type',
        'amount',
        '_meta',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        '_meta' => 'array',
    ];

    protected $logAttributes = [
        'event_id',
        'lead_id',
        'sales_person_id',
        'agent_id',
        '_meta',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'potential_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
    public function assignedPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stall()
    {
        return $this->belongsTo(Stall::class, 'stall_id');
    }
    public function agent()
    {
        return $this->belongsTo(Lead::class, 'agent_id');
    }
    // public function branchPrimaryAddress()
    // {
    //     return $this->hasOne(Branch::class, 'lead_id');
    // }
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
    public function followups()
    {
        return $this->hasMany(Followup::class);
    }
}
