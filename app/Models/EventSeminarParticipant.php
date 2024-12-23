<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSeminarParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'visitor_id',
        'seminar_id',
        'amount',
        'payment_status',
        'payment_type',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function seminar()
    {
        return $this->belongsTo(Seminar::class);
    }
}
