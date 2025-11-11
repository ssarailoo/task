<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CommentPolicy
{



    public function update(User $user, Comment $comment): Response
    {
        if ($comment->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You can only edit your own comments.')
            ->withStatus(HttpResponse::HTTP_FORBIDDEN);

    }

    /**
     * Determine if the user can delete the comment.
     */
    public function delete(User $user, Comment $comment): Response
    {
        if ($comment->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You can only delete your own comments.')
            ->withStatus(HttpResponse::HTTP_FORBIDDEN);
    }
}
