<?php

namespace App\Services;

use App\DataTransferObjects\ReportDTO;
use App\Enums\ReportCategoryEnum;
use App\Enums\ReportPeriodEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

readonly class ReportService
{
    public function generateTaskCompletionReport(Task $task): Report
    {
        $project = $task->project;

        $data = [
            'task_id' => $task->id,
            'task_title' => $task->title,
            'completed_at' => now()->toDateTimeString(),
            'estimated_time' => $task->estimated_time,
            'actual_time' => $task->actual_time,
            'time_variance' => $task->actual_time && $task->estimated_time
                ? $task->actual_time - $task->estimated_time
                : null,
            'assigned_users' => $task->assignedUsers->pluck('name')->toArray(),
        ];

        $reportDTO = new ReportDTO(
            project_id: $project->id,
            user_id: null,
            title: "Task Completion: {$task->title}",
            category: ReportCategoryEnum::TASK_COMPLETION,
            period: ReportPeriodEnum::CUSTOM,
            data: $data,
            date_from: now()->toDateString(),
            date_to: now()->toDateString(),
        );

        return $this->createReport($reportDTO);
    }

    public function generateTeamPerformanceReport(Project $project, string $dateFrom, string $dateTo): Report
    {
        $tasks = $project->tasks()
            ->with('assignedUsers')
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->where('status', TaskStatusEnum::COMPLETED->value)
            ->get();

        $userPerformance = [];
        foreach ($tasks as $task) {
            foreach ($task->assignedUsers as $user) {
                if (!isset($userPerformance[$user->id])) {
                    $userPerformance[$user->id] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'tasks_completed' => 0,
                        'total_actual_time' => 0,
                        'total_estimated_time' => 0,
                    ];
                }

                $userPerformance[$user->id]['tasks_completed']++;
                $userPerformance[$user->id]['total_actual_time'] += $task->actual_time ?? 0;
                $userPerformance[$user->id]['total_estimated_time'] += $task->estimated_time ?? 0;
            }
        }


        foreach ($userPerformance as &$performance) {
            $performance['efficiency'] = $performance['total_estimated_time'] > 0
                ? round(($performance['total_estimated_time'] / $performance['total_actual_time']) * 100, 2)
                : null;
        }

        $data = [
            'total_tasks_completed' => $tasks->count(),
            'team_members' => count($userPerformance),
            'user_performance' => array_values($userPerformance),
        ];

        $reportDTO = new ReportDTO(
            project_id: $project->id,
            user_id: null,
            title: "Team Performance Report: {$project->title}",
            category: ReportCategoryEnum::TEAM_PERFORMANCE,
            period: ReportPeriodEnum::CUSTOM,
            data: $data,
            date_from: $dateFrom,
            date_to: $dateTo,
        );

        return $this->createReport($reportDTO);
    }

    public function generateTimePredictionReport(Project $project, string $dateFrom, string $dateTo): Report
    {
        $completedTasks = $project->tasks()
            ->whereBetween('updated_at', [$dateFrom, $dateTo])
            ->where('status', TaskStatusEnum::COMPLETED->value)
            ->whereNotNull('estimated_time')
            ->whereNotNull('actual_time')
            ->get();

        $totalVariance = 0;
        $accuratePredictions = 0;
        $overestimated = 0;
        $underestimated = 0;

        foreach ($completedTasks as $task) {
            $variance = $task->actual_time - $task->estimated_time;
            $totalVariance += abs($variance);

            $variancePercentage = ($variance / $task->estimated_time) * 100;

            if (abs($variancePercentage) <= 10) {
                $accuratePredictions++;
            } elseif ($variance > 0) {
                $underestimated++;
            } else {
                $overestimated++;
            }
        }

        $data = [
            'total_tasks_analyzed' => $completedTasks->count(),
            'accurate_predictions' => $accuratePredictions,
            'overestimated_tasks' => $overestimated,
            'underestimated_tasks' => $underestimated,
            'average_variance_minutes' => $completedTasks->count() > 0
                ? round($totalVariance / $completedTasks->count(), 2)
                : 0,
            'accuracy_rate' => $completedTasks->count() > 0
                ? round(($accuratePredictions / $completedTasks->count()) * 100, 2)
                : 0,
        ];

        $reportDTO = new ReportDTO(
            project_id: $project->id,
            user_id: null,
            title: "Time Prediction Analysis: {$project->title}",
            category: ReportCategoryEnum::TIME_PREDICTION,
            period: ReportPeriodEnum::CUSTOM,
            data: $data,
            date_from: $dateFrom,
            date_to: $dateTo,
        );

        return $this->createReport($reportDTO);
    }

    public function generateProjectStatusReport(Project $project): Report
    {
        $totalTasks = $project->tasks()->count();
        $completedTasks = $project->tasks()->where('status', TaskStatusEnum::COMPLETED->value)->count();
        $inProgressTasks = $project->tasks()->where('status', TaskStatusEnum::IN_PROGRESS->value)->count();
        $blockedTasks = $project->tasks()->where('status', TaskStatusEnum::BLOCKED->value)->count();

        $statusDistribution = $project->tasks()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count])
            ->toArray();

        $data = [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'project_status' => $project->status,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'blocked_tasks' => $blockedTasks,
            'completion_percentage' => $totalTasks > 0
                ? round(($completedTasks / $totalTasks) * 100, 2)
                : 0,
            'status_distribution' => $statusDistribution,
            'budget' => $project->budget,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
        ];

        $reportDTO = new ReportDTO(
            project_id: $project->id,
            user_id: null,
            title: "Project Status Report: {$project->title}",
            category: ReportCategoryEnum::PROJECT_STATUS,
            period: ReportPeriodEnum::CUSTOM,
            data: $data,
            date_from: now()->toDateString(),
            date_to: now()->toDateString(),
        );

        return $this->createReport($reportDTO);
    }

    public function createReport(ReportDTO $data): Report
    {
        return $this->query()->create($data->toArray());
    }

    private function query(): Builder
    {
        return Report::query();
    }
}
