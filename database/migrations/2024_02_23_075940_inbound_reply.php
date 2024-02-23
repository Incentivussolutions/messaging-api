<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inbound_replies', function (Blueprint $table) {
            $table->id();
            $table->integer('inbound_id')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('template_id')->nullable();
            $table->string('reply_text')->nullable();
            $table->string('message_ref_id')->nullable();
            $table->integer('created_user_id')->nullable();
            $table->integer('updated_user_id')->nullable();
            $table->integer('deleted_user_id')->nullable();
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
