<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $fillable = ['name', 'increase_amount', 'increase_percentage'];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'increase_amount'=> 'decimal:2',
            'increase_percentage'=> 'integer',
        ];
    }
}
