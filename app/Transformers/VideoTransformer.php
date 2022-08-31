<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id ?? null,
            'title' => (string) $this->title ?? null,
            'description' => (string) $this->description ?? null,
            'published_on' => $this->publish_date?? null,
            'media' => [
                'url' => 'https://www.youtube.com/embed/'.$this->video_id,
                'iframe' => '<iframe itemprop ="video" class="img-responsive info-videos" src="//www.youtube.com/embed/'.$this->video_id.'?rel=0&?wmode=transparent&?controls=0&showinfo=0" frameborder="0"  allowfullscreen style="max-width:100%; height:400" height="400" width="55%"></iframe>',
                'placeholder' => env('YOUTUBE_PLACEHOLDER_URL').$this->video_id. env('YOUTUBE_PLACEHOLDER_DEFAULT_IMAGE'),
                'path' => $this->image_path,
            ]
        ];
    }
}