<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RefScopeArg
 * @package Shirokovnv\DomainReflection\Models
 */
class RefScopeArg extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref_scope_id', 'name', 'isOptional', 'position', 'typeHint'
    ];

    /**
     * @return BelongsTo
     */
    public function ref_scope(): BelongsTo {
        return $this->belongsTo(RefScope::class);
    }
}
