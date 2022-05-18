<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('webauthn.database.table'), static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('attachment_type', 20)->nullable()->index();
            $table->string('credential_id')->index();
            $table->string('type');
            $table->text('transports');
            $table->string('attestation_type');
            $table->text('trust_path');
            $table->text('aaguid');
            $table->text('credential_public_key');
            $table->unsignedBigInteger('counter');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }
};
