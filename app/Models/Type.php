<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Type extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'brand_id',
    ];

    public function getRouteKeyName()
    {
        return 'type';
    }

    /**
     * Get the brand that owns the type.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get all of the tags for the type.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the statuses for the type.
     */
    public function statuses(): MorphToMany
    {
        return $this->morphToMany(Status::class, 'statusable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the codes for the type.
     */
    public function codes(): MorphToMany
    {
        return $this->morphToMany(Code::class, 'codeable')->withTimestamps()->wherePivotNull('deleted_at');
    }
}
