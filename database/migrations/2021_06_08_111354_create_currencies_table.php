<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code')->unique();
            $table->string('currency_symbol')->unique();
            $table->string('currency_fullname');
            $table->unsignedInteger('currency_type')->comment('1 => fiat, 2 => crypto');
            $table->unsignedInteger('is_default')->comment('1 => default, 0 => not default');
            $table->unsignedInteger('status')->comment('1 => active, 0 => inactive');
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
        Schema::dropIfExists('currencies');
    }
}
