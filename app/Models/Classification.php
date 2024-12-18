<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
        ];
    }

    public function subCategories()
    {
        return $this->belongsToMany(SubCategory::class);
    }
}
