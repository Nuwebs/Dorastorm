<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $author = $this->user;
        $path = is_null($this->banner)? null : config('filesystems.disks.public.url').'/'.$this->banner;
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'visible' => $this->visible ?? false,
            'private' => $this->private ?? false,
            'slug' => $this->slug,
            'banner' => $path,
            'author' => [
                'id' => $author->id,
                'name' => $author->name
            ],
            'tags' => $this->tagNames,
            'created' => $this->created_at,
            'modified' => $this->updated_at,
        ];
    }
}
