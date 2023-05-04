<?php

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
            $table->integer('user_id');
            $table->integer('brand_id')->nullable();
            $table->integer('category_id');
            $table->integer('supplier_id')->nullable();
            $table->string('name');
            $table->string('code');
            $table->string('variant');
            $table->text('description')->nullable();
            $table->boolean('is_show');
            $table->string('photo')->nullable();
            $table->integer('price');
            $table->integer('current_stock');
            $table->softDeletes();
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
