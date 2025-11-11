<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

readonly class AttachmentService
{
    public function storeAttachments(Model $model, array $files): void
    {
        foreach ($files as $file) {
            $this->storeAttachment($model, $file);
        }
    }

    public function storeAttachment(Model $model, UploadedFile $file): Attachment
    {
        $basePath = $this->getModelPath($model);
        $path = $file->store($basePath, 'public');

        return $model->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    public function deleteAttachment(Attachment $attachment): bool
    {
        Storage::disk('public')->delete($attachment->file_path);

        return $attachment->delete();
    }

    public function deleteAttachments(Model $model): void
    {
        $basePath = $this->getModelPath($model);

        if (Storage::disk('public')->exists($basePath)) {
            Storage::disk('public')->deleteDirectory($basePath);
        }

        $model->attachments()->delete();
    }

    private function getModelPath(Model $model): string
    {
        $modelName = class_basename($model);
        $modelId = $model->id;

        return "attachments/{$modelName}/{$modelId}";
    }
}
