<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glaze extends Model
{
    protected $fillable = ['name', 'percentage', 'name_en', 'name_pt', 'name_it'];

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
            'name_en' => 'string',
            'name_pt' => 'string',
            'name_it' => 'string',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
