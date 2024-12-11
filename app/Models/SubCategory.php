<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = ['name', 'category_id'];

    public function casts(): array
    {
        return [
            'name' => 'string',
            'category_id' => 'integer',
        ];
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function glazes() {
        return $this->hasMany(Glaze::class);
    }
}
