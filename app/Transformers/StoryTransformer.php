<?php

namespace App\Transformers;


use Illuminate\Http\Resources\Json\JsonResource;

class StoryTransformer extends JsonResource
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
            
        ];
    }
}