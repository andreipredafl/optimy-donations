<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $amount_cents
 * @property bool $is_anonymous
 * @property string $payment_method
 * @property string $transaction_id
 * @property string|null $gateway_transaction_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Donation extends Model
{
    /**
     * @use HasFactory<\Database\Factories\DonationFactory>
     */
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_REFUNDED,
    ];

    protected $fillable = [
        'campaign_id',
        'user_id',
        'amount_cents',
        'payment_method',
        'transaction_id',
        'gateway_transaction_id',
        'status',
        'is_anonymous',
        'message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'is_anonymous' => 'boolean',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors
     */
    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    /**
     * Mutators
     *
     * @param  float  $value
     */
    public function setAmountAttribute($value): void
    {
        $this->amount_cents = (int) round($value * 100);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopeRefunded(Builder $query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * @param  Builder<Donation>  $query
     * @param  int  $userId
     * @return Builder<Donation>
     */
    public function scopeByUser($query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopeByStatus($query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopePublic($query): Builder
    {
        return $query->where('is_anonymous', false);
    }

    /**
     * @param  Builder<Donation>  $query
     * @return Builder<Donation>
     */
    public function scopeAnonymous(Builder $query): Builder
    {
        return $query->where('is_anonymous', true);
    }
}
