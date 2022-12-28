<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Dictionary
 * @property int $id
 * @property array $name
 * @property string $slug
 */
class Category extends BaseModel
{
    use SoftDeletes;

    /**
     * @var array|string[]
     */
    public array $translatable = ['name'];

    /**
     * @var string
     */
    public $table = 'categories';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'slug', 'active','order'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'active' => 'boolean',
        'name' => 'json'
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'active', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * @return BelongsToMany
     */
    public function spectacles()
    {
        return $this->belongsToMany(Spectacle::class, 'spectacle_category', 'category_id', 'spectacle_id');
    }

}
