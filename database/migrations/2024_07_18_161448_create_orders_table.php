<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Assuming you have a users table
            $table->decimal('total_price', 8, 2); // Total price of the order
            $table->string('status');
            
            // Adding delivery-related fields directly to the orders table
            $table->string('delivery_name')->nullable();
            $table->string('delivery_email')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('delivery_phone_number')->nullable();
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}