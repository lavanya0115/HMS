<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Announcement extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'visible_type',
        'is_active',
        'is_pinned',
        'image',
        'event_id',
    ];
    protected $logAttributes = [
        'title',
        'description',
        'visible_type',
        'is_active',
        'is_pinned',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'announcement_log';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->logName)
            ->logOnly($this->logAttributes);
    }
}
