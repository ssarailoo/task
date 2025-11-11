<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProjectStatusEnum;
use App\Enums\ReportCategoryEnum;
use App\Enums\TaskStatusEnum;
use App\Events\TaskCompleted;
use App\Listeners\GenerateTaskCompletionReport;
use App\Models\Project;
use App\Models\Report;
use App\Models\Task;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $reportService;
    private User $user;
    private Project $project;
    #[Test]
    protected function setUp(): void
    {
        parent::setUp();

        $this->reportService = app(ReportService::class);

        $this->user = User::factory()->create();

        $this->project = Project::create([
            'title' => 'Test Project',
            'description' => 'Test Description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => ProjectStatusEnum::IN_PROGRESS->value,
            'created_by' => $this->user->id,
        ]);

        $this->project->users()->attach($this->user->id);
    }

    #[Test]
    public function it_generates_task_completion_report()
    {
        $task = Task::create([
            'title' => 'Completed Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 120,
            'actual_time' => 100,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $report = $this->reportService->generateTaskCompletionReport($task);

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals($this->project->id, $report->project_id);
        $this->assertEquals(ReportCategoryEnum::TASK_COMPLETION->value, $report->category);
        $this->assertEquals($task->id, $report->data['task_id']);
        $this->assertEquals($task->title, $report->data['task_title']);
        $this->assertEquals(-20, $report->data['time_variance']); // 100 - 120
        $this->assertContains($this->user->name, $report->data['assigned_users']);
    }

    #[Test]
    public function it_generates_team_performance_report()
    {

        $task1 = Task::create([
            'title' => 'Task 1',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 120,
            'actual_time' => 100,
            'updated_at' => now(),
        ]);
        $task1->assignedUsers()->attach($this->user->id);

        $task2 = Task::create([
            'title' => 'Task 2',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 60,
            'actual_time' => 80,
            'updated_at' => now(),
        ]);
        $task2->assignedUsers()->attach($this->user->id);

        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->addDay()->toDateString();

        $report = $this->reportService->generateTeamPerformanceReport(
            $this->project,
            $dateFrom,
            $dateTo
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals(ReportCategoryEnum::TEAM_PERFORMANCE->value, $report->category);
        $this->assertEquals(2, $report->data['total_tasks_completed']);
        $this->assertEquals(1, $report->data['team_members']);

        $userPerformance = $report->data['user_performance'][0];
        $this->assertEquals($this->user->id, $userPerformance['user_id']);
        $this->assertEquals(2, $userPerformance['tasks_completed']);
        $this->assertEquals(180, $userPerformance['total_actual_time']);
        $this->assertEquals(180, $userPerformance['total_estimated_time']);
    }

    #[Test]
    public function it_generates_time_prediction_report()
    {

        Task::create([
            'title' => 'Accurate Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 105,
            'updated_at' => now(),
        ]);


        Task::create([
            'title' => 'Underestimated Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 150,
            'updated_at' => now(),
        ]);


        Task::create([
            'title' => 'Overestimated Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 50,
            'updated_at' => now(),
        ]);

        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->addDay()->toDateString();

        $report = $this->reportService->generateTimePredictionReport(
            $this->project,
            $dateFrom,
            $dateTo
        );

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals(ReportCategoryEnum::TIME_PREDICTION->value, $report->category);
        $this->assertEquals(3, $report->data['total_tasks_analyzed']);
        $this->assertEquals(1, $report->data['accurate_predictions']);
        $this->assertEquals(1, $report->data['underestimated_tasks']);
        $this->assertEquals(1, $report->data['overestimated_tasks']);
        $this->assertEquals(33.33, round($report->data['accuracy_rate'], 2));
    }


    #[Test]
    public function it_generates_project_status_report()
    {

        Task::create([
            'title' => 'Todo Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        Task::create([
            'title' => 'In Progress Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);

        Task::create([
            'title' => 'Completed Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
        ]);

        Task::create([
            'title' => 'Blocked Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::BLOCKED->value,
        ]);

        $report = $this->reportService->generateProjectStatusReport($this->project);

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals(ReportCategoryEnum::PROJECT_STATUS->value, $report->category);
        $this->assertEquals(4, $report->data['total_tasks']);
        $this->assertEquals(1, $report->data['completed_tasks']);
        $this->assertEquals(1, $report->data['in_progress_tasks']);
        $this->assertEquals(1, $report->data['blocked_tasks']);
        $this->assertEquals(25, $report->data['completion_percentage']); // 1/4 * 100
        $this->assertArrayHasKey('status_distribution', $report->data);
    }



    #[Test]
    public function it_handles_empty_project_gracefully()
    {
        $report = $this->reportService->generateProjectStatusReport($this->project);

        $this->assertEquals(0, $report->data['total_tasks']);
        $this->assertEquals(0, $report->data['completion_percentage']);
    }


    public function task_completion_event_triggers_report_generation()
    {
        Event::fake([TaskCompleted::class]);

        $task = Task::create([
            'title' => 'Task to Complete',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'estimated_time' => 120,
            'actual_time' => 100,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        event(new TaskCompleted($task));

        Event::assertDispatched(TaskCompleted::class);
    }


    #[Test]
    public function listener_generates_report_on_task_completed_event()
    {
        $task = Task::create([
            'title' => 'Task for Listener Test',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 120,
            'actual_time' => 100,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $event = new TaskCompleted($task);
        $listener = new GenerateTaskCompletionReport($this->reportService);

        $listener->handle($event);

        $this->assertDatabaseHas('reports', [
            'project_id' => $this->project->id,
            'category' => ReportCategoryEnum::TASK_COMPLETION->value,
        ]);

        $report = Report::where('project_id', $this->project->id)
            ->where('category', ReportCategoryEnum::TASK_COMPLETION->value)
            ->first();

        $this->assertEquals($task->id, $report->data['task_id']);
    }


    #[Test]
    public function it_calculates_time_variance_correctly()
    {

        $task1 = Task::create([
            'title' => 'Over Time Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 150,
        ]);

        $report1 = $this->reportService->generateTaskCompletionReport($task1);
        $this->assertEquals(50, $report1->data['time_variance']);


        $task2 = Task::create([
            'title' => 'Under Time Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 80,
        ]);

        $report2 = $this->reportService->generateTaskCompletionReport($task2);
        $this->assertEquals(-20, $report2->data['time_variance']);
    }


    #[Test]
    public function it_handles_null_time_values_in_report()
    {
        $task = Task::create([
            'title' => 'Task without times',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => null,
            'actual_time' => null,
        ]);

        $report = $this->reportService->generateTaskCompletionReport($task);

        $this->assertNull($report->data['time_variance']);
    }


    #[Test]
    public function team_performance_report_calculates_efficiency()
    {
        $user2 = User::factory()->create();
        $this->project->users()->attach($user2->id);


        $task1 = Task::create([
            'title' => 'Efficient Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 120,
            'actual_time' => 100,
            'updated_at' => now(),
        ]);
        $task1->assignedUsers()->attach($this->user->id);


        $task2 = Task::create([
            'title' => 'Less Efficient Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
            'estimated_time' => 100,
            'actual_time' => 150,
            'updated_at' => now(),
        ]);
        $task2->assignedUsers()->attach($user2->id);

        $dateFrom = now()->subDay()->toDateString();
        $dateTo = now()->addDay()->toDateString();

        $report = $this->reportService->generateTeamPerformanceReport(
            $this->project,
            $dateFrom,
            $dateTo
        );

        $this->assertEquals(2, $report->data['team_members']);

        foreach ($report->data['user_performance'] as $performance) {
            if ($performance['user_id'] === $this->user->id) {
                $this->assertEquals(120, $performance['efficiency']);
            } else if ($performance['user_id'] === $user2->id) {
                $this->assertEquals(66.67, $performance['efficiency']);
            }
        }
    }
}
