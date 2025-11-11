<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyReports extends Command
{
    protected $signature = 'reports:generate-monthly';
    protected $description = 'Generate monthly reports for all active projects';

    public function __construct(
        private readonly ReportService $reportService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting monthly report generation...');

        $projects = Project::whereIn('status', ['pending', 'in_progress', 'completed'])->get();

        $dateFrom = now()->subMonth()->toDateString();
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
                $this->reportService->generateTimePredictionReport(
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
                Log::error('Monthly report generation failed', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Monthly report generation completed: {$successCount} succeeded, {$failCount} failed");

        return self::SUCCESS;
    }
}
