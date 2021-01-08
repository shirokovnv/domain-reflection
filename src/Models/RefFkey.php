<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RefFkey
 * @package Shirokovnv\DomainReflection\Models
 */
class RefFkey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref_field_id', 'name', 'foreign_table', 'references'
    ];

    /**
     * @return BelongsTo
     */
    public function ref_field(): BelongsTo
    {
        return $this->belongsTo(RefField::class);
    }

}
