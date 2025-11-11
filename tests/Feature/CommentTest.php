<?php

namespace Tests\Feature;

use App\Enums\ProjectStatusEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;
    private Task $task;

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

        $this->task = Task::create([
            'title' => 'Test Task',
            'project_id' => $this->project->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);
    }

    #[Test]
    public function it_can_create_a_comment()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => $this->task->id,
            'content' => 'This is a test comment',
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'task_id',
                    'user_id',
                    'content',
                    'rating',
                ]
            ]);

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'This is a test comment',
        ]);
    }

    #[Test]
    public function it_can_create_a_comment_with_rating()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => $this->task->id,
            'content' => 'Great work!',
            'rating' => 5,
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'content' => 'Great work!',
            'rating' => 5,
        ]);
    }

    #[Test]
    public function it_can_create_a_reply_to_comment()
    {
        Passport::actingAs($this->user);

        $parentComment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Parent comment',
        ]);

        $replyData = [
            'task_id' => $this->task->id,
            'content' => 'This is a reply',
        ];

        $response = $this->postJson("/api/v1/comments/{$parentComment->id}/replies", $replyData);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('comments', [
            'parent_id' => $parentComment->id,
            'content' => 'This is a reply',
            'rating' => null,
        ]);
    }

    #[Test]
    public function it_prevents_rating_on_replies()
    {
        Passport::actingAs($this->user);

        $parentComment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Parent comment',
        ]);

        $replyData = [
            'task_id' => $this->task->id,
            'content' => 'This is a reply',
            'rating' => 5,
        ];

        $response = $this->postJson("/api/v1/comments/{$parentComment->id}/replies", $replyData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['rating']);
    }

    #[Test]
    public function it_can_update_a_comment()
    {
        Passport::actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    #[Test]
    public function it_can_update_comment_rating()
    {
        Passport::actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Comment with rating',
            'rating' => 3,
        ]);

        $updateData = [
            'content' => 'Comment with rating',
            'rating' => 5,
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'rating' => 5,
        ]);
    }

    #[Test]
    public function it_prevents_non_owner_from_updating_comment()
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Original content',
        ]);

        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->patchJson("/api/v1/comments/{$comment->id}", $updateData);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function it_can_delete_a_comment()
    {
        Passport::actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Comment to delete',
        ]);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

    #[Test]
    public function it_deletes_replies_when_parent_is_deleted()
    {
        Passport::actingAs($this->user);

        $parentComment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Parent comment',
        ]);

        $reply = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'parent_id' => $parentComment->id,
            'content' => 'Reply',
        ]);

        $response = $this->deleteJson("/api/v1/comments/{$parentComment->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('comments', [
            'id' => $parentComment->id,
        ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $reply->id,
        ]);
    }

    #[Test]
    public function it_prevents_non_owner_from_deleting_comment()
    {
        $otherUser = User::factory()->create();
        Passport::actingAs($otherUser);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'content' => 'Comment',
        ]);

        $response = $this->deleteJson("/api/v1/comments/{$comment->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    #[Test]
    public function it_validates_rating_range()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => $this->task->id,
            'content' => 'Comment',
            'rating' => 6,
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['rating']);
    }

    #[Test]
    public function it_validates_content_is_required()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => $this->task->id,
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function it_validates_task_exists()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => 99999,
            'content' => 'Comment',
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['task_id']);
    }

    #[Test]
    public function it_validates_content_max_length()
    {
        Passport::actingAs($this->user);

        $commentData = [
            'task_id' => $this->task->id,
            'content' => str_repeat('a', 5001),
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    #[Test]
    public function unauthenticated_user_cannot_create_comment()
    {
        $commentData = [
            'task_id' => $this->task->id,
            'content' => 'Comment',
        ];

        $response = $this->postJson('/api/v1/comments', $commentData);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
