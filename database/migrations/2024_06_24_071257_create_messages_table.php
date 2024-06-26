<?php

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Contact;
use App\Models\Device;
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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Device::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignIdFor(Contact::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->enum('type', MessageType::values());
            $table->string('content');
            $table->json('response')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', MessageStatus::values());
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
