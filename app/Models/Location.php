<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['location'];

    public function getRouteKeyName()
    {
        return 'location';
    }

    /**
     * Get all of the tags for the task.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the statuses for the task.
     */
    public function statuses(): MorphToMany
    {
        return $this->morphToMany(Status::class, 'statusable')->withTimestamps()->wherePivotNull('deleted_at');
    }

    /**
     * Get all of the codes for the task.
     */
    public function codes(): MorphToMany
    {
        return $this->morphToMany(Code::class, 'codeable')->withTimestamps()->wherePivotNull('deleted_at');
    }

        /**
     * Get all of the tasks for the task.
     */
    public function tasks(): MorphToMany
    {
        return $this->morphToMany(Task::class, 'taskable')->withTimestamps()->wherePivotNull('deleted_at');
    }

            /**
     * Get all of the types for the task.
     */
    public function types(): MorphToMany
    {
        return $this->morphToMany(Type::class, 'typeable')->withTimestamps()->wherePivotNull('deleted_at');
    }
}