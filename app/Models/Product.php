<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'classification_id',
        'glaze_id',
        'price_per_kg',
        'code',
        'quantity_boxes',
        'weight_per_box',
        'palette_dimensions',
        'category_id',
        'sub_category_id',
        'net_price',
        'net_weight',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'classification_id' => 'integer',
            'glaze_id' => 'integer',
            'code' => 'string',
            'quantity_boxes' => 'integer',
            'price_per_kg' => 'decimal:2',
            'weight_per_box' => 'decimal:2',
            'palette_dimensions' => 'string',
            'category_id' => 'integer',
            'sub_category_id' => 'integer',
            'net_price' => 'decimal:2',
            'net_weight' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function glaze()
    {
        return $this->belongsTo(Glaze::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
