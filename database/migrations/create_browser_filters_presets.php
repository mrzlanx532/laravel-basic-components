<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('browser_filters_presets');
        Schema::create('browser_filters_presets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('ident')->comment('Идентификатор браузера');
            $table->json('filters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('browser_filters_presets');
    }
};
