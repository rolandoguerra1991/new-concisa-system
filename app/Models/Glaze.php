<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Glaze extends Model
{
    protected $fillable = ['name', 'sub_category_id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'string',
            'sub_category_id'=> 'integer',
        ];
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
