<?php

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum("status", ProjectStatusEnum::getValues())->default(ProjectStatusEnum::PENDING->value);
            $table->enum("priority", ProjectPriorityEnum::getValues())->default(ProjectPriorityEnum::LOW->value);
            $table->enum("type", ProjectTypeEnum::getValues())->default(ProjectTypeEnum::INTERNAL->value);
            $table->enum("recurring", ProjectRecurringEnum::getValues())->default(ProjectRecurringEnum::NONE->value);
            $table->foreignIdFor(User::class, 'created_by')->constrained()->restrictOnDelete();;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
