<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\Controller;
use App\Models\UserCategory;
use App\Models\Publication;
use App\Models\Registration;
use App\Models\Category;
use App\Models\PinnedArticle;
use App\Models\DynamicPageContents;
use App\Models\DynamicPageContentsArticle;
use App\Utility\AuthorUtility;
use App\Utility\BrandUtility;
use App\Utility\PageContentUtility;
use App\Utility\MetaUtility;
use App\Utility\RegistrationUtility;
use App\Utility\StoryUtility;
use App\Utility\CategoryUtility;
use App\Utility\CommonUtility;
use DB;

class StoryController extends Controller
{
    public $authorUtility,$brandUtility,$metaUtility, $registrationUtility, $storyUtility, $commonUtility,$pageContentUtility;
    public function __construct()
    {
        $this->authorUtility = app(AuthorUtility::class);
        $this->brandUtility = app(BrandUtility::class);
        $this->metaUtility = app(MetaUtility::class);
        $this->categoryUtility = app(CategoryUtility::class);
        $this->registrationUtility = app(RegistrationUtility::class);
        $this->storyUtility = app(StoryUtility::class);
        $this->commonUtility = app(CommonUtility::class);
        $this->pageContentUtility = app(PageContentUtility::class);
    }  

    public function detail(Request $request) {
        $id = $request->query('id');

        $article = UserCategory::select('id','title','descriptor','description','display_status','post_status','is_sponsored','publish_date','modified_date','videoImage','image','featureImageAs','user_author','user_id','admin_cat_id','user_author','author_id','seo_title','seo_description','story_index','keyword')
            ->where('id','=',$id)->status()->active()->approve()->first();
          
        $data = null;
        if (!empty($article)){
            $brandId = UserCategory::BRAND_1851;
            if($article->user_author > 0) {
                $brandId = $article->user_id;
                $brand = $this->registrationUtility->getDetails($brandId);
            }  
            $image = $this->storyUtility->media($article);
            $data =  [
                'id' => $id,
                 'title' => $article->title,
                 'slug' => Str::slug($article->title) . '-' . $id,
                 'short_description' =>  $article->descriptor,
                 'content' => $article->description,
                 'type' => strtolower($article->display_status) ?? null,
                 'status' => $article->post_status ?? null,
                 'sponsorship' => $article->is_sponsored ? true : false,
                 'category' => $this->categoryUtility->details($article),
                 'author' => $this->authorUtility->getDetails($article),
                 'brand' => $this->brandUtility->getDetails($brandId,$article),
                 'media' => $this->commonUtility->media($image['type'], $image['path'], $article->image),
                 'posted_on' => $article->publish_date,
                 'last_modified' => $article->modified_date,
                 'meta' => $this->storyUtility->meta($article),
            ];  
            if(!empty($brand) && $brand->status == Registration::STATUS_DISAPPROVE) {
                $data = null ;
            }
        } 
            return response()->json([ 'data' => $data ]);

    }
    public function list(Request $request) {
        $type = $request->attributes->get('type');
        $limit = $request->query('limit') ?? 6;
        $categorySlug = $request->query('categorySlug') ?? 0;
        $slug = $request->query('slug');
        $brand = $brandId = $categoryId = null;
        $category =  $this->categoryUtility->getDetails($categorySlug);
        if(!empty($category)) {
            $categoryId = $category->id;
        }
        if(!empty($slug)) {
            $brand = $this->brandUtility->fetchBySlug($slug);
            if(!empty($brand)) {
                $brandId = $brand->id;
            }
            if(empty($brand) && $type != UserCategory::ENDPOINT_AUTHOR && $type != UserCategory::ENDPOINT_AWARDS) {
                $data = [
                    'status' => 404,
                    'message' => 'Brand is not found'
                ];
            }
        }
        $select = ['user_category.id', 'story_index', 'title', 'publish_date', 'keyword', 'modified_date','description', 'descriptor', 'admin_cat_id', 'article_post_by', 'featureImageAs', 'image', 'user_category.user_id', 'display_status', 'user_author', 'author_id','videoImage'];
        $articles = UserCategory::select($select)->publish()->approve()->active();

        if($type == 'featured'){
            $articles = $articles->featured(UserCategory::ENDPOINT_FEATURED, $slug, $categorySlug, $brandId, $categoryId);
          
        } else if($type == 'sponsored') {
            $articles =  $articles->where('admin_cat_id', '!=', Category::CATEGORY_NA);
            if(empty($slug)){
                $articles = $articles->where('user_author','!=',0);
            }        
        } else if($type == 'editorial') {
            $articles = $articles->featured(UserCategory::ENDPOINT_EDITORIAL, $slug, $categorySlug, $brandId, $categoryId);
        }else if($type == UserCategory::ENDPOINT_LATEST_STORIES) {
            $articles = $articles->where('user_id',$brandId);
        }else if($type == UserCategory::ENDPOINT_TRENDING || $type == UserCategory::ENDPOINT_TRENDING_BRAND_BUZZ) {
            $startDate = (new \DateTime())->modify('-7 days');
            $startDate = $startDate->format('Y-m-d 00:00:00');
            $date = new \DateTime();
            $toDate = $date->modify('-1 days');
            $toDate = $toDate->format('Y-m-d 00:00:00');

            $postDate = (new \DateTime())->modify('-60days');
            
            if(empty($categorySlug)){
                $trending = $articles->join('trending_analysis','trending_analysis_article_id','=', 'user_category.id')
                        ->whereBetween('trending_analysis_updated_datetime', [$startDate, $toDate])
                        ->where('trending_analysis_article_points', '<>', 0);
                if(empty($slug)){
                    if($type == UserCategory::ENDPOINT_TRENDING_BRAND_BUZZ) {
                        $articles = $trending->postByUser();
                    }else{
                        $articles = $trending->where('user_category.publish_date', '>=', $postDate->format('Y-m-d 00:00:00'))
                        ->where('trending_analysis_brand_id', 0);  
                    }
                } else {
                    $articles = $trending->where('trending_analysis_brand_id', $brand->id);
                }
                $articles = $articles->groupBy(['trending_analysis_article_id'])->orderByRaw('SUM(trending_analysis_article_points) DESC');
            }
        } else if($type == UserCategory::ENDPOINT_AUTHOR) {
            $type = $request->query('type');
            $author = $this->authorUtility->fetchBySlug($slug);
            if(empty($author)) {
                return response()->json([
                    'message' => "Author Not Found"
                ]);
            }
            if($type == 'editorial') {
                $articles = $articles->where('user_id', $author->id);
            } else if($type == 'branded-content') {
                $articles = $articles->where('user_author', $author->id);
            }

        } else if($type == UserCategory::ENDPOINT_AWARDS) {
            $pageContent = $this->pageContentUtility->hasDynamic($slug);
            if(!empty($pageContent)) {   
                $articles = $articles->dynamic($pageContent);
            }
            else{
                return response()->json(["data" => null]);
            }
        }
        $articles = $articles->orderBy('user_category.publish_date' ,'DESC')->paginate($limit);

        if(!empty($articles)) {
            foreach($articles as $article) {
                $data[] = $this->storyUtility->details($article, $type);
            }
        }
        $response = [ 
            'hasMore' => $articles->hasMorePages(),
            'data' => $data ?? null
        ];
        if($type == UserCategory::ENDPOINT_AWARDS && !empty($pageContent)) {
            $response['title'] = $pageContent->dynamic_page_contents_title ?? null;
            $response['description'] = $pageContent->dynamic_page_contents_description?? null;
            $response['url'] = '/'.$pageContent->dynamic_page_contents_url?? null;
            $response['media'] = $this->pageContentUtility->media($slug,$pageContent) ?? null;
        }
        return response()->json($response);
    }
}