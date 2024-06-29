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
        Schema::table('status_logs', function (Blueprint $table) {
            $table->string('status', 20)->nullable()->after('message_ref_id');
            $table->string('status_code', 20)->nullable()->after('status')->change();
            $table->json('status_response')->nullable()->after('rejected_reason');
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
