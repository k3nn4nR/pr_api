<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag'];

    public function getRouteKeyName()
    {
        return 'tag';
    }

    /**
     * Get all of the tags for the tag.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the statuses for the tag.
     */
    public function statuses(): MorphToMany
    {
        return $this->morphToMany(Status::class, 'statusable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the codes for the tag.
     */
    public function codes(): MorphToMany
    {
        return $this->morphToMany(Code::class, 'codeable')->withTimestamps()->wherePivotNull('deleted_at');
    }
}
