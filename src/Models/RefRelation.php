<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


/**
 * Class RefRelation
 * @package Shirokovnv\DomainReflection\Models
 */
class RefRelation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref_model_id', 'name', 'type', 'keys'
    ];

    public function ref_model(): BelongsTo
    {
        return $this->belongsTo(RefModel::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'keys' => 'array',
    ];
}
