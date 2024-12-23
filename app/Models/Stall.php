<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stall extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'stall_number',
        'hall_number',
        'special_feature',
        'size',
        'stall_type',
        'side_open',
        'fascia_count',
        '_meta',
        'event_id',
        'potential_id',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $logAttributes = [
        'stall_number',
        'hall_number',
        'special_feature',
        'size',
        'stall_type',
        'side_open',
        'fascia_count',
        '_meta',
        'event_id',
        'potential_id',
        'status',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'stall_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
