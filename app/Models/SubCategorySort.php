<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategorySort extends Model
{
    protected $fillable = [
        'sub_category_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sub_category_id' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
