<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDataDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_data_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('product_dimensions(LHW)1')->nullable();
            $table->string('product_dimensions(LHW)2')->nullable();
            $table->string('packaging_dimensions(LHW)1')->nullable();
            $table->string('packaging_dimensions(LHW)2')->nullable();
            $table->string('weight_product')->nullable();
            $table->string('total_weight_product')->nullable();
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
        Schema::dropIfExists('product_data_dimensions');
    }
}
