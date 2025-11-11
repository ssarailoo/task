<?php

namespace App\Console\Commands;

use App\Enums\ProjectStatusEnum;
use App\Enums\ReportPeriodEnum;
use App\Models\Project;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyReports extends Command
{
    protected $signature = 'reports:generate-daily';
    protected $description = 'Generate daily reports for all active projects';

    public function __construct(
        private readonly ReportService $reportService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting daily report generation...');

        $projects = Project::whereIn('status', [ProjectStatusEnum::PENDING->value, ProjectStatusEnum::IN_PROGRESS->value])->get();

        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->toDateString();

        $successCount = 0;
        $failCount = 0;

        foreach ($projects as $project) {
            try {

                $this->reportService->generateTeamPerformanceReport(
                    $project,
                    $dateFrom,
                    $dateTo
                );

                $this->reportService->generateProjectStatusReport($project);

                $successCount++;
                $this->info("Reports generated for project: {$project->title}");
            } catch (\Exception $e) {
                $failCount++;
                $this->error("Failed to generate reports for project: {$project->title}");
                Log::error('Daily report generation failed', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Daily report generation completed: {$successCount} succeeded, {$failCount} failed");

        return self::SUCCESS;
    }
}
