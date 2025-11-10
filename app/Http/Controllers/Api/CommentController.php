<?php

namespace App\Http\Controllers\Api;

use App\DataTransferObjects\CommentDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService
    ) {}

    public function store(StoreCommentRequest $request): JsonResponse
    {
        $commentData = CommentDTO::fromRequest($request->validated());
        $comment = $this->commentService->createComment($commentData);

        return response()->json(
            ['data' => new CommentResource($comment)],
            Response::HTTP_CREATED
        );
    }

    public function reply(StoreCommentRequest $request, Comment $comment): JsonResponse
    {
        $replyData = CommentDTO::fromRequest($request->validated());
        $reply = $this->commentService->createReply($comment, $replyData);

        return response()->json(
            ['data' => new CommentResource($reply)],
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $comment = $this->commentService->updateComment($comment, $request->validated());

        return response()->json(
            ['data' => new CommentResource($comment)],
            Response::HTTP_OK
        );
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $this->commentService->deleteComment($comment);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
