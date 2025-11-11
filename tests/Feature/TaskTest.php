<?php

namespace Tests\Feature;

use App\Enums\ProjectStatusEnum;
use App\Enums\TaskStatusEnum;
use App\Events\TaskCompleted;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function it_can_create_a_task_with_minimal_data()
    {
        Passport::actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'project_id',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);
    }

    #[Test]
    public function it_can_create_a_task_with_all_data()
    {
        Passport::actingAs($this->user);

        $taskData = [
            'title' => 'Complete Task',
            'description' => 'Task description',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'due_date' => now()->addDays(7)->toDateString(),
            'estimated_time' => 120,
            'actual_time' => 100,
            'assigned_user_ids' => [$this->user->id],
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Complete Task',
            'description' => 'Task description',
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'estimated_time' => 120,
            'actual_time' => 100,
        ]);

        $this->assertDatabaseHas('task_user', [
            'user_id' => $this->user->id,
            'task_id' => $response->json('data.id'),
        ]);
    }

    #[Test]
    public function it_can_update_a_task()
    {
        Passport::actingAs($this->user);

        $task = Task::create([
            'title' => 'Original Title',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'estimated_time' => 180,
        ];

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'estimated_time' => 180,
        ]);
    }

    #[Test]
    public function it_fires_event_when_task_is_completed()
    {
        Event::fake([TaskCompleted::class]);

        Passport::actingAs($this->user);

        $task = Task::create([
            'title' => 'Task to Complete',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'estimated_time' => 120,
            'actual_time' => 100,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $updateData = [
            'status' => TaskStatusEnum::COMPLETED->value,
        ];

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK);

        Event::assertDispatched(TaskCompleted::class, function ($event) use ($task) {
            return $event->task->id === $task->id;
        });
    }

    #[Test]
    public function it_does_not_fire_event_when_task_status_changes_to_non_completed()
    {
        Event::fake([TaskCompleted::class]);

        Passport::actingAs($this->user);

        $task = Task::create([
            'title' => 'Task in Progress',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $updateData = [
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ];

        $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        Event::assertNotDispatched(TaskCompleted::class);
    }

    #[Test]
    public function it_can_create_task_with_dependencies()
    {
        Passport::actingAs($this->user);

        $dependency1 = Task::create([
            'title' => 'Dependency 1',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $dependency2 = Task::create([
            'title' => 'Dependency 2',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $taskData = [
            'title' => 'Task with Dependencies',
            'project_id' => $this->project->id,
            'dependencies' => [$dependency1->id, $dependency2->id],
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $response->json('data.id'),
            'depends_on_task_id' => $dependency1->id,
        ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $response->json('data.id'),
            'depends_on_task_id' => $dependency2->id,
        ]);
    }

    #[Test]
    public function it_prevents_completing_task_with_incomplete_dependencies()
    {
        Passport::actingAs($this->user);

        $dependency = Task::create([
            'title' => 'Incomplete Dependency',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);

        $task = Task::create([
            'title' => 'Dependent Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);
        $task->dependencies()->attach($dependency->id);

        $updateData = [
            'status' => TaskStatusEnum::COMPLETED->value,
        ];

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function it_allows_completing_task_with_all_dependencies_completed()
    {
        Event::fake();
        Passport::actingAs($this->user);

        $dependency = Task::create([
            'title' => 'Completed Dependency',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::COMPLETED->value,
        ]);

        $task = Task::create([
            'title' => 'Dependent Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);
        $task->dependencies()->attach($dependency->id);

        $updateData = [
            'status' => TaskStatusEnum::COMPLETED->value,
        ];

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatusEnum::COMPLETED->value,
        ]);
    }

    #[Test]
    public function it_requires_user_to_be_assigned_to_update_task()
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);

        $task = Task::create([
            'title' => 'Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $updateData = ['title' => 'New Title'];

        $response = $this->patchJson("/api/v1/tasks/{$task->id}", $updateData);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function it_validates_assigned_users_belong_to_project()
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($this->user);

        $taskData = [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
            'assigned_user_ids' => [$otherUser->id],
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['assigned_user_ids.0']);
    }

    #[Test]
    public function it_can_delete_a_task()
    {
        Passport::actingAs($this->user);

        $task = Task::create([
            'title' => 'Task to Delete',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    #[Test]
    public function it_can_show_task_with_relationships()
    {
        Passport::actingAs($this->user);

        $task = Task::create([
            'title' => 'Task with Relations',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);

        $task->assignedUsers()->attach($this->user->id);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'project' => ['id', 'title'],
                    'assigned_users',
                    'dependencies',
                    'dependents',
                    'comments',
                ]
            ]);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_task()
    {
        $taskData = [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
        ];

        $response = $this->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
