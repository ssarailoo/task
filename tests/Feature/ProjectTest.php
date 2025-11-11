<?php

namespace Tests\Feature;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectRecurringEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\ProjectTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

}
