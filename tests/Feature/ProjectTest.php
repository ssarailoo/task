<?php

namespace Tests\Feature;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function it_can_create_a_project_with_minimal_data()
    {
        Passport::actingAs($this->user);

        $projectData = [
            'title' => 'Test Project',
            'description' => 'Test project description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'user_ids' => [$this->user->id],
        ];


        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_CREATED);


        $responseData = $response->json('data');
        $this->assertEquals($projectData['title'], $responseData['title']);
        $this->assertEquals($projectData['description'], $responseData['description']);
        $this->assertEquals(ProjectStatusEnum::PENDING->value, $responseData['status']);
        $this->assertEquals(ProjectPriorityEnum::LOW->value, $responseData['priority']);
        $this->assertEquals(ProjectTypeEnum::INTERNAL->value, $responseData['type']);
        $this->assertEquals(ProjectRecurringEnum::NONE->value, $responseData['recurring']);
        $this->assertContains($this->user->id, array_column($responseData['users'], 'id'));


        $this->assertDatabaseHas('projects', [
            'title' => $projectData['title'],
            'status' => ProjectStatusEnum::PENDING->value,
        ]);

        $this->assertDatabaseHas('project_user', [
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_create_a_project_with_all_data(): void
    {
        Passport::actingAs($this->user);

        $projectData = [
            'title' => 'Complete Project',
            'description' => 'Complete project description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => ProjectStatusEnum::IN_PROGRESS->value,
            'priority' => ProjectPriorityEnum::HIGH->value,
            'type' => ProjectTypeEnum::EXTERNAL->value,
            'recurring' => ProjectRecurringEnum::WEEKLY->value,
            'budget' => 50000.00,
            'user_ids' => [$this->user->id],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_CREATED);

        $responseData = $response->json('data');
        $this->assertEquals($projectData['title'], $responseData['title']);
        $this->assertEquals($projectData['description'], $responseData['description']);
        $this->assertEquals($projectData['status'], $responseData['status']);
        $this->assertEquals($projectData['priority'], $responseData['priority']);
        $this->assertEquals($projectData['type'], $responseData['type']);
        $this->assertEquals($projectData['recurring'], $responseData['recurring']);
        $this->assertEquals($projectData['budget'], $responseData['budget']);
        $this->assertContains($this->user->id, array_column($responseData['users'], 'id'));

        $this->assertDatabaseHas('projects', [
            'title' => $projectData['title'],
            'status' => $projectData['status'],
            'priority' => $projectData['priority'],
            'type' => $projectData['type'],
            'recurring' => $projectData['recurring'],
            'budget' => $projectData['budget'],
        ]);

        $this->assertDatabaseHas('project_user', [
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function it_can_create_a_project_with_attachments()
    {
        Storage::fake('public');
        Passport::actingAs($this->user);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $projectData = [
            'title' => 'Project with Attachment',
            'description' => 'Project with attachment description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'user_ids' => [$this->user->id],
            'attachments' => [$file],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);
        $response->assertStatus(Response::HTTP_CREATED);

        $projectId = $response->json('data.id');

        $this->assertDatabaseHas('attachments', [
            'file_name' => $file->getClientOriginalName(),
            'attachable_type' => get_class(app(\App\Models\Project::class)),
            'attachable_id' => $projectId,
        ]);

        $expectedPath = "attachments/Project/{$projectId}/{$file->hashName()}";
        Storage::disk('public')->assertExists($expectedPath);
    }

    #[Test]
    public function it_requires_title_to_create_project()
    {
        Passport::actingAs($this->user);

        $projectData = [
            'description' => 'Test description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'user_ids' => [$this->user->id],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['title']);

    }
    #[Test]
    public function it_requires_at_least_one_user()
    {
        Passport::actingAs($this->user);

        $projectData = [
            'title' => 'Test Project',
            'description' => 'Test description',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'user_ids' => [],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['user_ids']);
    }


    #[Test]
    public function it_validates_start_date_is_not_in_past()
    {
        Passport::actingAs($this->user);

        $projectData = [
            'title' => 'Test Project',
            'description' => 'Test description',
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'user_ids' => [$this->user->id],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['start_date']);
    }


    #[Test]
    public function it_validates_end_date_is_after_start_date()
    {
        Passport::actingAs($this->user);

        $projectData = [
            'title' => 'Test Project',
            'description' => 'Test description',
            'start_date' => now()->addDays(30)->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'user_ids' => [$this->user->id],
        ];

        $response = $this->postJson('/api/v1/projects', $projectData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['end_date']);
    }


}
