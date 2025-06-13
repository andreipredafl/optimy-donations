<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User model
 *
 * @property int $id
 * @property string $employee_ref
 * @property string $name
 * @property string $email
 * @property string|null $department
 * @property string|null $job_title
 * @property string|null $avatar_url
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_ref',
        'name',
        'email',
        'password',
        'department',
        'job_title',
        'avatar_url',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function createdCampaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'creator_id');
    }

    /**
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Generate a unique employee_ref like 'EMP001', 'EMP002', etc.
     */
    public static function generateEmployeeRef(): string
    {
        /** @var User|null $lastUser */
        $lastUser = self::query()->orderByDesc('id')->first();
        $lastRef = $lastUser?->employee_ref;

        if ($lastRef && preg_match('/EMP(\\d+)/', $lastRef, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        } else {
            $nextNumber = 1;
        }

        return 'EMP'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
