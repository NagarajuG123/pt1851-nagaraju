<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TermsOfUseTransformer extends JsonResource
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
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'short_description' =>(string) $this->description,
        ];
    }
}