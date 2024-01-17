<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToProductDataSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_data_sizes', function (Blueprint $table) {
            $table->string('absorbency_pillow')->nullable();
            $table->string('absorbency_boom')->nullable();
            $table->string('absorbency_sock')->nullable();
            $table->string('absorbency_mat')->nullable();
            $table->string('absorbency_kit')->nullable();
            $table->string('capacity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_data_sizes', function (Blueprint $table) {
            //
        });
    }
}
