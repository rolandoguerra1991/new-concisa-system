<?php

use App\Models\Category;
use App\Models\Classification;
use App\Models\Glaze;
use App\Models\SubCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(SubCategory::class);
            $table->foreignIdFor(Classification::class);
            $table->foreignIdFor(Glaze::class);
            $table->decimal('price_per_kg');
            $table->integer('code');
            $table->integer('quantity_boxes');
            $table->string('weight_per_box');
            $table->string('palette_dimensions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
