<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Services\ReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateTaskCompletionReport implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function handle(TaskCompleted $event): void
    {
        try {
            $this->reportService->generateTaskCompletionReport($event->task);
        } catch (\Exception $e) {
            Log::error('Failed to generate task completion report', [
                'task_id' => $event->task->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function failed(TaskCompleted $event, \Throwable $exception): void
    {
        Log::error('Task completion report generation failed', [
            'task_id' => $event->task->id,
            'error' => $exception->getMessage()
        ]);
    }
}
