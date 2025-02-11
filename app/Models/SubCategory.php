<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = ['name', 'category_id', 'fao', 'name_en', 'name_pt', 'name_it'];

    public function casts(): array
    {
        return [
            'name' => 'string',
            'category_id' => 'integer',
            'fao' => 'string',
            'name_en' => 'string',
            'name_pt' => 'string',
            'name_it' => 'string',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function classifications()
    {
        return $this->belongsToMany(Classification::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function subCategorySorts()
    {
        return $this->hasMany(SubCategorySort::class);
    }
}
