<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerfectSaleMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perfect_sale_media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file')->nullable();
            $table->string('file_type')->nullable();
            $table->string('perfect_sale_id')->nullable();
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
        Schema::dropIfExists('perfect_sale_media');
    }
}
