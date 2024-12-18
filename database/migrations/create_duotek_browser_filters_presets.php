<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMrzlanx532BrowserFiltersPresets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('Mrzlanx532_browser_filters_presets');
        Schema::create('Mrzlanx532_browser_filters_presets', function (Blueprint $table) {
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
        Schema::dropIfExists('Mrzlanx532_browser_filters_presets');
    }
}
