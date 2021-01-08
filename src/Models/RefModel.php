<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class RefModel
 * @package Shirokovnv\DomainReflection\Models
 */
class RefModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_name', 'class_name', 'table_name'
    ];

    /**
     * @return HasMany
     */
    public function ref_fields(): HasMany
    {
        return $this->hasMany(RefField::class);
    }

    /**
     * @return HasMany
     */
    public function ref_relations(): HasMany
    {
        return $this->hasMany(RefRelation::class);
    }

    /**
     * @return HasManyThrough
     */
    public function ref_fkeys(): HasManyThrough
    {
        return $this->hasManyThrough(RefFkey::class, RefField::class);
    }
}
