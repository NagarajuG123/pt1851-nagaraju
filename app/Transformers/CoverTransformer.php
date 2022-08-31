<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Utility\CoverUtility;
use Illuminate\Support\Str;


class CoverTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request): array
    { 
        if(!empty($this->pinn_article_id)){
            $data = [
                'id' => $this->pinnedArticle->id ?? null,
                'title' => $this->pinnedArticle->title ?? null,
                'description' => $this->pinnedArticle->descriptor ?? null,
                'slug' => Str::slug($this->pinnedArticle->title).'-'.$this->pinnedArticle->id,
            ]; 
        };
        return [
        'id' => (int) $this->id ?? null,
        'date' =>(string) $this->monthYear ?? null,
        'url' => app(CoverUtility::class)->getCoverUrl($this->monthYear) ?? null,
        'media' => [
            'type' => 'image',
            'url' => env('AWS_S3_URL') ."/covers/".$this->feature_image ?? null, 
            'path' =>  'covers/'.$this->feature_image
        ],
        'story' =>  $data ?? null
        ];
        
    }
}