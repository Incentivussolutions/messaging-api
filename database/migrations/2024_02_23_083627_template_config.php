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
        Schema::create('template_configs', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->nullable();
            $table->tinyInteger('template_type')->nullable();
            $table->string('template_id')->nullable();
            $table->string('template_key')->nullable();
            $table->integer('whatspp_config_id')->nullable();
            $table->integer('language_id')->nullable();
            $table->integer('custom_content_type')->nullable();
            $table->integer('custom_field_type')->nullable();
            $table->text('template_content')->nullable();
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
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
