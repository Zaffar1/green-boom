<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->nullable();
            $table->string('usage')->nullable();
            $table->string('title')->nullable();
            $table->string('description1')->nullable();
            $table->string('description2')->nullable();
            $table->string('description3')->nullable();
            $table->string('description4')->nullable();
            $table->string('description5')->nullable();
            $table->string('file')->nullable();
            $table->string('product_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
