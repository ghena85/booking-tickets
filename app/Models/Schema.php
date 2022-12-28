<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use \Illuminate\Database\Eloquent\Relations\HasMany;

class Schema extends BaseModel
{

    /**
     * @var string
     */
    protected $table = 'schemas';

    /**
     * @var array|string[]
     */
    protected array $translatable = ['name'];

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'active'];

    /**
     * @return HasMany
     */
    public function rows()
    {
        return $this->hasMany(Row::class);
    }

    /**
     * @return BelongsToMany
     */
    public function colors()
    {
        return $this
            ->belongsToMany(Color::class, 'color_schema', 'schema_id', 'color_id')
            ->withPivot('id', 'price')
            ->as('data');
    }

}
