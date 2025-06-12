<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $goal_amount_cents
 * @property int $current_amount_cents
 * @property int $donations_count
 * @property int $donors_count
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 */
class Campaign extends Model
{
    /**
     * @use HasFactory<\Database\Factories\CampaignFactory>
     */
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'goal_amount_cents',
        'current_amount_cents',
        'creator_id',
        'category_id',
        'status',
        'start_date',
        'end_date',
        'featured_image_url',
        'donations_count',
        'donors_count',
    ];

    protected function casts(): array
    {
        return [
            'goal_amount_cents' => 'integer',
            'current_amount_cents' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'donations_count' => 'integer',
            'donors_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($campaign) {
            if (empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->title);
            }
        });

        static::updating(function ($campaign) {
            if ($campaign->isDirty('title') && empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->title);
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Donation, $this>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * @return HasMany<CampaignUpdate, $this>
     */
    public function campaignUpdates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    public function getGoalAmountAttribute(): float
    {
        return $this->goal_amount_cents / 100;
    }

    public function setGoalAmountAttribute(float $value): void
    {
        $this->goal_amount_cents = (int) round($value * 100);
    }

    public function getCurrentAmountAttribute(): float
    {
        return $this->current_amount_cents / 100;
    }

    public function setCurrentAmountAttribute(float $value): void
    {
        $this->current_amount_cents = (int) round($value * 100);
    }
}
