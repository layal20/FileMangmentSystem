<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'path',
        'version_id',
        'version_number',
        'user_id',
        'group_id',
        'owner_id',
        'booked_by',
        'request_status'
    ];
    /**
     * Get the user that owns the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ownedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    /**
     * Get the user that books the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by', 'id');
    }

    /**
     * Get the group that owns the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::observe(FileObserver::class);
    // }
}
