<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->string('document_type');
            $table->string('for_whom');
            $table->json('application_data');
            $table->string('purpose');
            $table->string('delivery_method');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->date('claim_date');
            $table->time('claim_time');
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
}; 