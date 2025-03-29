<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id','currency_id'];

    /**
     * Get all of the tags for the tags.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the tags for the statuses.
     */
    public function statuses(): MorphToMany
    {
        return $this->morphToMany(Status::class, 'statusable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the tags for the coodes.
     */
    public function code(): MorphToMany
    {
        return $this->morphOne(Code::class, 'codeable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the tags for the comments.
     */
    public function comments(): MorphToMany
    {
        return $this->morphToMany(Comment::class, 'commentable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the code for the files.
     */
    public function files(): MorphToMany
    {
        return $this->morphToMany(File::class, 'fileable')->withTimestamps()->wherePivotNull('deleted_at');
    }
}