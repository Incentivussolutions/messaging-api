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
        Schema::create('whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->nullable();
            $table->integer('provider')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('application_id')->nullable();
            $table->string('api_key', 50)->nullable();
            $table->string('api_secret', 100)->nullable();
            $table->string('key_file')->nullable();
            $table->tinyInteger('status')->nullable();
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
