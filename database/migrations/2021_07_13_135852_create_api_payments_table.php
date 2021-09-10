<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_payments', function (Blueprint $table) {
            $table->id();
            $table->string('trx',40);
            $table->integer('merchant_id');
            $table->string('merchant_public_key',255);
            $table->integer('payer_id')->nullable();
            $table->string('identifier',255);
            $table->string('currency',40);
            $table->decimal('amount',28,8);
            $table->string('details',255);
            $table->string('ipn_url',255);
            $table->string('cancel_url',255);
            $table->string('success_url',255);
            $table->string('site_logo',255);
            $table->string('checkout_theme',40);
            $table->string('customer_name',40);
            $table->string('customer_email',40);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('api_payments');
    }
}
