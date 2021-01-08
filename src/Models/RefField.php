<?php


namespace Shirokovnv\DomainReflection\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class RefField
 * @package Shirokovnv\DomainReflection\Models
 */
class RefField extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref_model_id', 'name', 'label', 'type',
        'fillable', 'guarded', 'hidden', 'required'
    ];

    /**
     * Associated model
     * @return BelongsTo
     */
    public function ref_model(): BelongsTo {
        return $this->belongsTo(RefModel::class);
    }

    /**
     * Associated foreign keys (if exists)
     * @return HasOne
     */
    public function ref_fkey(): HasOne {
        return $this->hasOne(RefFkey::class);
    }
}
