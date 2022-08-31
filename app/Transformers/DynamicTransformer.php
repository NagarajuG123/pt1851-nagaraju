<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DynamicTransformer extends JsonResource
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
            'title' => (string) $this->dynamic_page_contents_title ?? null,
            'url' =>(string) '/'.$this->dynamic_page_contents_url ?? null,
            'media' => [
                'type' => 'image',
                'url' => env('AWS_S3_URL') ."/pageContent/".$this->splash_image ?? null, 
                'path' =>  'pageContent/'.$this->splash_image
            ]
        ];
    }
}