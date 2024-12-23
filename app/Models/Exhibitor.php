<?php

namespace App\Models;

use App\Models\ExhibitorProduct;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity; // Import Authenticatable class
use Spatie\Permission\Traits\HasRoles;
// Import Authenticatable interface

class Exhibitor extends Authenticatable implements AuthenticatableContract
{
    use HasFactory;
    use softDeletes;
    use HasApiTokens;
    use HasRoles;
    use LogsActivity;

    protected $fillable = [
        'username',
        'name',
        'category_id',
        'proof_type',
        'proof_id',
        'email',
        'website',
        'password',
        'mobile_number',
        'logo',
        'description',
        'known_source',
        'newsletter',
        'registration_type',
        'landline_number',
        'created_by',
        'updated_by',
        'deleted_by',
        '_meta',
        'lead_id',
        'sales_person_id'
    ];

    protected $logAttributes = [
        'username',
        'name',
        'category_id',
        'proof_type',
        'proof_id',
        'email',
        'website',
        'password',
        'mobile_number',
        'logo',
        'description',
        'known_source',
        'newsletter',
        'registration_type',
        'created_by',
        'updated_by',
        'deleted_by',
        '_meta',
        'lead_id',
    ];

    protected static $logOnlyDirty = true;

    protected $logName = 'exhibitor_log';

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function exhibitorContact()
    {
        return $this->hasOne(ExhibitorContact::class);
    }

    public function contact_persons()
    {
        return $this->hasMany(ExhibitorContact::class);
    }

    public function eventExhibitors()
    {
        return $this->hasMany(EventExhibitor::class, 'exhibitor_id');
    }

    public function exhibitorProducts()
    {
        return $this->hasMany(ExhibitorProduct::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function userLogin()
    {
        return $this->hasMany(UserLoginActivity::class, 'userable_id')->orderBy('last_login_at', 'desc')->take(1);
    }

    public function products()
    {
        return $this->hasMany(ExhibitorProduct::class);
    }
    public function getProfileCompletionPercentage()
    {
        $exhibitorFields = [
            'logo' => 7,
            'username' => 7,
            'name' => 5,
            'category_id' => 5,
            'products' => 8,
            'email' => 6,
            'mobile_number' => 6,
            'website_url' => 4,
            'description' => 5,
        ];

        $contactPersonFields = [
            'salutation' => 5,
            'name' => 6,
            'designation' => 5,
            'contact_number' => 6,
        ];

        $exhibitorAddress = [
            'pincode' => 5,
            'city' => 5,
            'state' => 5,
            'country' => 5,
            'address' => 5,
        ];

        $address = [
            'pincode' => 9,
            'country' => 8,
            'address' => 8,
        ];

        $filledFields = 0;

        foreach ($exhibitorFields as $field => $percentage) {
            if ($field === 'products' && !empty($this->exhibitorProducts)) {
                $filledFields += $percentage;
            } else if ($field === 'website_url' && !empty($this->_meta[$field])) {
                $filledFields += $percentage;
            } else if (!empty($this->$field)) {
                $filledFields += $percentage;
            }
        }

        foreach ($contactPersonFields as $field => $percentage) {
            if (!empty($this->exhibitorContact->$field)) {
                $filledFields += $percentage;
            }
        }
        foreach ($exhibitorAddress as $field => $percentage) {
            if (strtolower($this->address?->country) === 'india' && !empty($this->address->$field)) {
                $filledFields += $percentage;
            }
        }

        foreach ($address as $field => $percentage) {
            if (strtolower($this?->address?->country) !== 'india' && !empty($this->address->$field)) {
                $filledFields += $percentage;
            }
        }

        return $filledFields;
    }

    public function branchPrimaryAddress()
    {
        return $this->hasOne(Branch::class)->where('is_head', true);
    }
    public function branchAddress()
    {
        return $this->hasMany(Branch::class)->where('is_head', false);
    }
}
