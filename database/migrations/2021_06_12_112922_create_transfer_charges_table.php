<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferChargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_charges', function (Blueprint $table) {
            $table->id();
            $table->decimal('fixed_charge', 28,8);
            $table->decimal('percent_charge', 5,2);
            $table->decimal('min_limit', 28,8)->nullable();
            $table->decimal('max_limit', 28,8)->nullable();
            $table->integer('cap');
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
        Schema::dropIfExists('transfer_charges');
    }
}
