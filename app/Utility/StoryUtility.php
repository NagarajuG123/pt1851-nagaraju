<?php

namespace App\Utility;

use App\Models\UserCategory;
use App\Models\Publication;
use App\Utility\RegistrationUtility;
use App\Utility\MetaUtility;
use App\Utility\CommonUtility;
use Illuminate\Support\Str;

class StoryUtility
{
    public $storyUtility, $commonUtility, $registrationUtility;
    public function __construct()
    {
        $this->metaUtility = app(MetaUtility::class);
        $this->commonUtility = app(CommonUtility::class);
        $this->registrationUtility = app(RegistrationUtility::class);
    }

    public function slug($brand, $article) {
        if(!empty($brand)) {
            $slug =  $brand->brand_url . '/'. Str::slug($article->title) . '-' . $article->id;
        } else {
            $slug = Str::slug($article->title) . '-' . $article->id;
        }
        return $slug;
    } 

    public function meta($article){    
        $publication = $this->commonUtility->publication();
        $image = $this->media($article);
        $media =  $this->commonUtility->media($image['type'], $image['path'], $article->image);
        $articleSlug = Str::slug($article->title).'-'.$article->id;
        $brandSlug = !empty($article->brand->brand_url) ? '/'.$article->brand->brand_url : null;
        $url = env('FE_URL').$brandSlug.'/'.$articleSlug;
        $robots =  $article->story_index ? 'index,follow' : 'noindex,follow';
        $index = $article->story_index ? true : false;
        $seoDescription = $article->seo_description ??  $article->descriptor;
        $seoTitle = $article->seo_title ??  $article->title;
        return $this->metaUtility->details($seoTitle,$seoDescription,$article->keyword,$publication->author,$robots,$index,$article->title,$article->descriptor, $publication->ogSiteName,$url,$media);
    }

    public function media($article){
        $image = '1851_default.jpg';
        if(!empty($article->image) || !empty($article->videoImage)){
            $image = !empty($article->videoImage) ? $article->videoImage : $article->image;
        }
        $data = [
            'path' => 'story/featureImage/' . $image,
            'type' => ($article->featureImageAs == 'Image') ? 'image' : 'video',
        ];
         return $data;
    }

    public function details($article, $type = null){
        $image = $this->media($article);
        $data = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => Str::slug($article->title) . '-' . $article->id,
            'short_description' => $article->descriptor,
            'brand' => $this->registrationUtility->brandInfo($article->brand),
            'media' => $this->commonUtility->media($image['type'], $image['path'], $article->image),
            'meta' => $this->meta($article),
            'posted_on' => $article->publish_date,
            'last_modified' => $article->modified_date,
        ];
        if($type == 'featured'){
            $data['pinn'] = !empty($article->pinnedStory) ? $article->pinnedStory->pinned_articles_position : false;
        }
        return $data;
    }

    public function getStoryByCategory($category,$slug,$brand)
    {
      $query = UserCategory::select(['id','admin_cat_id','article_post_by','user_id','post_status','approve'])
                        ->where(['admin_cat_id' => $category->id])
                        ->publish()
                        ->approve()
                        ->limit(1);
          if( !empty($slug) && $slug !== 'franchisedevelopmentawards'){
            return $query->where(['user_id' => $brand->id]);
          } 
          else {
            return $query->where(['article_post_by' => UserCategory::POST_BY_AUTHOR]);
        }
    }

}