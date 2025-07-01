<?php

namespace App\Models;

use Monolog\Level;
use App\Models\Category;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract; // Import Authenticatable interface

class User extends Authenticatable implements AuthenticatableContract
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    // use LogsActivity;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'is_active',
        'type',
        'emp_no',
    ];

    // log the specified attributes
    // protected $logAttributes = [
    //     'name',
    //     'email',
    //     'password',
    //     'mobile_number',
    //     'is_active',
    //     'type',
    //     'emp_no',
    // ];

    // protected static $logOnlyDirty = true;

    // protected $logName = 'user_log';

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->useLogName($this->logName)
    //         ->logOnly($this->logAttributes);
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function department()
    {
        return $this->belongsTo(Category::class, 'department_id')->where('type', 'department');
    }

}
