<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_num');
            $table->string('invoice_to');
            $table->string('email');
            $table->string('address');
            $table->decimal('charge',28,8);
            $table->decimal('total_amount',28,8);
            $table->decimal('get_amount',28,8);
            $table->tinyInteger('payment_status')->comment('1 => paid, 0 => not paid');
            $table->tinyInteger('status')->comment('1 => published, 0 => not published , 2 => cancel');
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
        Schema::dropIfExists('invoices');
    }
}
