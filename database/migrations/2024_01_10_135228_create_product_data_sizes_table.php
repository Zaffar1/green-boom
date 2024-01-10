<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDataSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_data_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('sku_num')->nullable();
            $table->string('size')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('absorbency_bag')->nullable();
            $table->string('absorbency_drum')->nullable();
            $table->string('qty_case')->nullable();
            $table->string('added_remediation_material')->nullable();
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
        Schema::dropIfExists('product_data_sizes');
    }
}
