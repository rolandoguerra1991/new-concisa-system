<?php

use App\Models\Classification;
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
        Schema::create('classification_sub_category', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Classification::class);
            $table->foreignIdFor(SubCategory::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classification_sub_category');
    }
};
