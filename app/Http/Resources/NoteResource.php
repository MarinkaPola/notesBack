<?php

namespace App\Http\Resources;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class NoteResource
 * @package App\Http\Resources
 * @mixin Note
 */

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'visibility' => $this->visibility,
            'uuid' => $this->uuid,
            'author' => UserResource::make($this->whenLoaded('user')),
            'attachment'=>AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
