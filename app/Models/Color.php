<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Color extends Model
{

    /**
     * @var string
     */
    protected $table = 'colors';

    /**
     * @var string[]
     */
    protected $fillable = ['name', 'color'];

    /**
     * @return BelongsToMany
     */
    public function schemas()
    {
        return $this->belongsToMany(Schema::class, 'color_schema', 'color_id', 'schema_id');
    }

}
