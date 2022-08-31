<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserCategory extends Model
{
    const STATUS_PUBLISH = 'publish';
    const STATUS_SCHEDULED = 'published_on';
    const STATUS_DRAFT = 'draft';
    const APPROVE = 'yes';
    const POST_BY_AUTHOR = 'author';
    const POST_BY_USER = 'user';
    const BRAND_1851 = 1851;
    const TYPE_EDITORIAL = 'Editorial';

    const ENDPOINT_TRENDING = 'trending';
    const ENDPOINT_AUTHOR = 'author';
    const ENDPOINT_FEATURED = 'featured';
    const ENDPOINT_EDITORIAL = 'editorial';
    const ENDPOINT_AWARDS = 'awards';
    const ENDPOINT_TRENDING_BRAND_BUZZ = 'trending-buzz';
    const ENDPOINT_LATEST_STORIES ='latest-stories';

    public $table = 'user_category';

    //scopes
    public function scopePostByAuthor($query)
    {
        return $query->where(['article_post_by' => self::POST_BY_AUTHOR]);
    }
    public function scopePublish($query)
    {
        return $query->where('post_status', self::STATUS_PUBLISH);
    }
    public function scopeApprove($query)
    {
        return $query->where(['user_category.approve' => self::APPROVE]);
    }
    public function scopeActive($query)
    {
        return $query->where(['user_category.is_trash' => false]);
    }
    public function scopePostByUser($query)
    {
        return $query->where(['user_category.article_post_by' => self::POST_BY_USER]);
    }
    public function scopeStatus($query)
    {
        return $query->where(['user_category.post_status' => [self::STATUS_PUBLISH, self::STATUS_SCHEDULED, self::STATUS_DRAFT]]);
    }
    public function scopeEditorial($query)
    {
        return $query->where(['user_category.display_status' => self::TYPE_EDITORIAL]);
    }

    public function scopeFeatured($query, $type,$slug, $categorySlug, $brandId, $categoryId)
    {
        if(empty($slug)) {
            $query = $query->postByAuthor();
        }
        if($type == self::ENDPOINT_FEATURED) {
            $query = $query->editorial();
        }
        if(empty($categorySlug)) {
            $query = $query->leftJoin('pinned_articles', 'user_category.id', '=', 'pinned_articles.pinned_articles_article_id')
            ->where(function ($query) {
                $query->orWhere('category', 0)
                ->orWhere('category',null);
            });
            if(empty($slug)){
                $query =  $query->where(function($query)  {
                    $query->orWhere('pinned_by', 'author')
                    ->orWhere('pinned_by', null);
                    });       
            } else {
                $query = $query->leftJoin('brand_main_story','brand_main_story.story_id', '=', 'user_category.id')->where(function($query) use ($brandId) {
                        $query->where('user_category.user_id', $brandId)
                            ->orWhere('brand_main_story.brand_id', $brandId)
                            ->orWhere('pinned_articles_brand_id', $brandId);
                        });
            }
            $query = $query->orderByRaw('ISNULL(pinned_articles_position), pinned_articles_position ASC');
        } else {
            $pinnedCatStory = PinnedArticle::where('category', $categoryId);
            $query = $query->where('user_category.admin_cat_id', $categoryId);
            if(!empty($slug)){
                $query = $query->where('user_category.user_id', $brandId);
                $pinnedCatStory = $pinnedCatStory->where('pinned_articles.pinned_articles_brand_id', $brandId);
            } else {
                $pinnedCatStory = $pinnedCatStory->where('pinned_articles.pinned_articles_brand_id', null);
            }
            $pinnedCatStory = $pinnedCatStory->first();
            if(!empty($pinnedCatStory)) {
                $query = $query->orWhere('user_category.id', $pinnedCatStory->pinned_articles_article_id)
                ->orderByRaw('FIELD(user_category.id,'.$pinnedCatStory->pinned_articles_article_id.') desc');
            } 
        }
        return $query;
    }
    public function scopeDynamic($query,$pageContent)
    {
        return $query->join('dynamic_page_contents_articles','dynamic_page_contents_articles.dynamic_page_contents_articles_article_id','=','user_category.id')
        ->where('dynamic_page_contents_articles.dynamic_page_contents_id','=',$pageContent->dynamic_page_contents_id)
        ->orderBy('dynamic_page_contents_articles.dynamic_page_contents_articles_created_date','DESC')
        ->orderBy( 'dynamic_page_contents_articles.dynamic_page_contents_articles_id','ASC');
    }
    //Relations
    public function category() {
        return $this->belongsTo('App\Models\Category','admin_cat_id');
    }
    public function author() {
        return $this->belongsTo('App\Models\Registration','user_id')->where('user_type','author');
    }
    public function brand() {
        return $this->belongsTo('App\Models\Registration','user_id')->where('user_type','user');
    }
    public function pinnedStory() {
        return $this->belongsTo('App\Models\PinnedArticle','id','pinned_articles_article_id');
    }    
}
