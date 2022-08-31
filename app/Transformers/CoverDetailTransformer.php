<?php

namespace App\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Utility\CommonUtility;
use App\Utility\MetaUtility;
use App\Utility\CoverUtility;
use App\Utility\CategoryUtility;
use App\Utility\AuthorUtility;
use Illuminate\Support\Str;


class CoverDetailTransformer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request): array
    { 
        $type = 'image';
        $path = 'story/featureImage/'.$this->image;
        if(($this->featureImageAs === 'VideoURL') || ($this->featureImageAs === 'Video')){
            $type = 'video';
            $path = 'story/featureImage/'.$this->videoImage;
        }
        $publication = app(CommonUtility::class)->publication();
        $robots = $this->story_index ? 'index,follow' : 'noindex,follow';
        $index = $this->story_index ? true : false;
        $seoTitle = !empty($this->seo_title) ? $this->seo_title : $this->title;
        $seoDescription = !empty($this->seo_description) ? $this->seo_description : $this->descriptor;
        $media = app(CommonUtility::class)->media($type,$path,$this->image);
        return [
        'id' => (int) $this->id ?? null,
        'title' => (string) $this->title ?? null,
        'slug' => Str::slug($this->title).'-'.$this->id,
        'short_description' =>(string) $this->descriptor ?? null,
        'content' =>(string) $this->description ?? null,
        'posted_on' =>(string) $this->publish_date ?? null,
        'last_modified' =>(string) $this->modified_date  ?? null,
        'content' =>(string) $this->description ?? null,
        'type' =>(string) (!empty($this->display_status)) ? strtolower($this->display_status) : null,
        'sponsorship' =>(string) $this->is_sponsored ? true : false,
        'category' => app(CategoryUtility::class)->details($this) ?? null,
        'author' => app(AuthorUtility::class)->getDetails($this) ?? null,
        'media' => $media ?? null,
        'meta' =>  app(MetaUtility::class)->details($seoTitle,  $seoDescription, $this->keyword, 
                    $publication->author, $robots, $index, $this->title, $this->descriptor,$publication->ogSiteName,
                    app(CoverUtility::class)->getArticleUrl($this->title,$this->id),$media),
        'pinn' =>!empty($this->pinn_article_id) ? true :false,
        ];
        
    }
}