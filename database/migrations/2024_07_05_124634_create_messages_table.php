<?php

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
            $table->foreignId('media_id')
                ->nullable()
                ->constrained(app(config('curator.model'))->getTable())
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->text('content');
            $table->json('response')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('initiated_at')->nullable();
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
