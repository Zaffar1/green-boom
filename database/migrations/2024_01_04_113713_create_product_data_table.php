<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_data', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('sku_num')->nullable();
            $table->string('size')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('Absorbency')->nullable();
            $table->string('qty')->nullable();
            $table->string('case')->nullable();
            $table->string('added_remediation_material')->nullable();
            $table->string('product_dimensions_size')->nullable();
            $table->string('product_dimensions_cm')->nullable();
            $table->string('packaging_dimensions_size')->nullable();
            $table->string('packaging_dimensions_cm')->nullable();
            $table->string('weight')->nullable();
            $table->string('product')->nullable();
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
        Schema::dropIfExists('product_data');
    }
}
