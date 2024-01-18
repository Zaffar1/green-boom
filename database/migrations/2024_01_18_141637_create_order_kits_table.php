<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_kits', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('short_description')->nullable();
            $table->string('description')->nullable();
            $table->json('kit_includes')->nullable();
            $table->string('image')->nullable();
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
        Schema::dropIfExists('order_kits');
    }
}
