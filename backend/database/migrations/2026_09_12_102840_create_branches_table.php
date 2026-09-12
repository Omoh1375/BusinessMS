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
    Schema::create('branches', function (Blueprint $table) {
        $table->id();

        $table->foreignId('business_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('name');
        $table->string('code', 50);

        $table->string('phone')->nullable();
        $table->string('email')->nullable();

        $table->text('address')->nullable();
        $table->string('city')->nullable();
        $table->string('state')->nullable();
        $table->string('country')->default('Nigeria');

        $table->boolean('is_main')->default(false);
        $table->boolean('is_active')->default(true);

        $table->timestamps();
        $table->softDeletes();

        $table->unique(['business_id', 'code']);
        $table->index(['business_id', 'is_active']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
