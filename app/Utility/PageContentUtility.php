<?php

namespace App\Utility;

use DB;

use App\Models\DynamicPageContents;

class PageContentUtility
{
    public function hasDynamic($slug){
        $pageContent = DynamicPageContents::select(['dynamic_page_contents_id','dynamic_page_contents_page_name',
        'dynamic_page_contents_title','dynamic_page_contents_url','dynamic_page_contents_description',
        'dynamic_page_contents_hero_image','splash_image','dynamic_page_contents_featured']);
        if(!empty($slug)) {
           return $pageContent = $pageContent->where('dynamic_page_contents_url','=',$slug)->first();
        }
        else{
            return $pageContent = $pageContent->where('dynamic_page_contents_featured','=',1)->first();
        }
    }
    public function media($pageContent){
        return $media = [
            'type' => 'image',
            'url' =>  !empty($pageContent->dynamic_page_contents_hero_image) ? env('AWS_S3_URL').'/pageContent/'.$pageContent->dynamic_page_contents_hero_image : env('AWS_S3_URL').'/pageContent/banner.png',
            'path' =>'pageContent/banner.png'
        ];
    }
}