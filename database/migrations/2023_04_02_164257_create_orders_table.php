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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('summary_price');
            $table->unsignedBigInteger('cart_id');
            $table->string('name');
            $table->string('email');
            $table->string('telephone');
            $table->string('tax_number')->nullable();
            $table->text('invoice_address');
            $table->text('shipping_address');
            $table->unsignedBigInteger('user_id');
            $table->string('transaction_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
