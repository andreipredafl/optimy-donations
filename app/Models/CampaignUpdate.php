<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CampaignUpdate model
 *
 * @property int $id
 * @property int $campaign_id
 * @property int $author_idc
 * @property string $title
 * @property string $content
 * @property string $update_type
 * @property bool $is_pinned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class CampaignUpdate extends Model
{
    /**
     * @use HasFactory<\Database\Factories\CampaignUpdateFactory>
     */
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'author_id',
        'title',
        'content',
        'update_type',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($update) {
            $update->created_at = now();
        });
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope a query to only include pinned updates.
     *
     * @param  Builder<CampaignUpdate>  $query
     * @return Builder<CampaignUpdate>
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Scope a query to only include updates for a specific campaign.
     *
     * @param  Builder<CampaignUpdate>  $query
     * @return Builder<CampaignUpdate>
     */
    public function scopeForCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope a query to only include updates of a specific type.
     *
     * @param  Builder<CampaignUpdate>  $query
     * @return Builder<CampaignUpdate>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('update_type', $type);
    }

    /**
     * Scope a query to order updates by most recent.
     *
     * @param  Builder<CampaignUpdate>  $query
     * @return Builder<CampaignUpdate>
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->latest('created_at');
    }
}
