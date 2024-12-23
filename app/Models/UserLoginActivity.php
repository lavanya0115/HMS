<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoginActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'userable_id',
        'userable_type',
        'last_login_at',
        'last_logout_at',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userable_id');
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class, 'userable_id');
    }

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class, 'userable_id');
    }

    public function exhibitorContact()
    {
        return $this->belongsTo(ExhibitorContact::class, 'userable_id');
    }
}
