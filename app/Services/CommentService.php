<?php

namespace App\Services;

use App\DataTransferObjects\CommentDTO;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

readonly class CommentService
{
    public function createComment(CommentDTO $data): Comment
    {
        return $this->query()->create($data->toArray());
    }

    public function createReply(Comment $parentComment, CommentDTO $data): Comment
    {
        $replyData = array_merge($data->toArray(), [
            'parent_id' => $parentComment->id,
            'task_id' => $parentComment->task_id,
            'rating' => null,
        ]);

        return $this->query()->create($replyData);
    }

    public function updateComment(Comment $comment, CommentDTO $data): Comment
    {
        $updateData = array_filter($data->toArray(), fn($v) => $v !== null);
        $comment->update($updateData);
        return $comment->fresh();
    }

    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }

    private function query(): Builder
    {
        return Comment::query();
    }
}
