<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // Import Authenticatable class
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Import Authenticatable interface
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class Visitor extends Authenticatable implements AuthenticatableContract
{
    use SoftDeletes;
    use HasFactory;
    use HasApiTokens;
    use LogsActivity;
    use HasRoles;

    protected $fillable = [
        'username',
        'password',
        'salutation',
        'name',
        'mobile_number',
        'email',
        'category_id',
        'organization',
        'designation',
        'known_source',
        'reason_for_visit',
        'newsletter',
        'proof_type',
        'proof_id',
        'registration_type',
        '_meta',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $logAttributes = [
        'username',
        'password',
        'salutation',
        'name',
        'mobile_number',
        'email',
        'category_id',
        'organization',
        'designation',
        'known_source',
        'reason_for_visit',
        'newsletter',
        'proof_type',
        'proof_id',
        'registration_type',
        '_meta',
        'created_by',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'visitor_log';

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
        '_meta' => 'json',
    ];
    protected $dates = ['deleted_at'];

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function eventVisitors()
    {
        return $this->hasMany(EventVisitor::class);
    }
    public function eventDelegates()
    {
        return $this->hasMany(EventSeminarParticipant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function visitor_logins()
    {
        return $this->morphMany(UserLoginActivity::class, 'userable');
    }
    public function paymentTransactions()
    {
        return $this->morphOne(PaymentTransaction::class, 'payable');
    }

    public function getProfileCompletionPercentage()
    {
        $percentageVisitorField = [
            'username' => 9,
            'salutation' => 5,
            'name' => 9,
            'mobile_number' => 8,
            'email' => 9,
            'category_id' => 8,
            'organization' => 7,
            'designation' => 7,
            '_meta' => 10,
        ];

        $visitorAddress = [
            'pincode' => 7,
            'city' => 5,
            'state' => 5,
            'country' => 5,
            'address' => 6,
        ];

        $address = [
            'pincode' => 10,
            'country' => 8,
            'address' => 10,
        ];

        $filledFields = 0;

        foreach ($percentageVisitorField as $field => $percentage) {
            if (!empty($this->$field)) {
                $filledFields += $percentage;
            }
        }

        foreach ($visitorAddress as $field => $percentage) {
            if ($this->address !== null && strtolower($this->address->country) === 'india' && !empty($this->address->$field)) {
                $filledFields += $percentage;
            }
        }

        foreach ($address as $field => $percentage) {
            if ($this->address !== null && strtolower($this->address->country) !== 'india' && !empty($this->address->$field)) {
                $filledFields += $percentage;
            }
        }

        return $filledFields;
    }

}
