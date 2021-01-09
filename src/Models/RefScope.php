<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class RefScope
 * @package Shirokovnv\DomainReflection\Models
 */
class RefScope extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref_model_id', 'name'
    ];

    public function ref_scope_args(): HasMany {
        return $this->hasMany(RefScopeArg::class);
    }
}
