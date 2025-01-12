<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glaze extends Model
{
    protected $fillable = ['name', 'percentage'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'percentage' => 'integer',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
