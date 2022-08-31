<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Publication;
use App\Models\UserMetaTags;
use App\Models\Seotag;
use App\Utility\MetaUtility;
use App\Utility\CategoryUtility;
use App\Utility\CommonUtility;
use App\Utility\BrandUtility;

class MetaController extends Controller
{
    public $metaUtility,$categoryUtility,$commonUtility,$brandUtility;
    public function __construct()
    {
        $this->metaUtility = app(MetaUtility::class);
        $this->categoryUtility = app(CategoryUtility::class);
        $this->commonUtility = app(CommonUtility::class);
        $this->brandUtility =  app(BrandUtility::class);
    }

    public function index(Request $request){
        $slug = $request->query('slug');
        $page = $request->query('page');
        $isBrand = $request->query('is_brand');
        $meta = $this->commonUtility->publication();
        if(!empty($slug)){
            if($isBrand){
                $brand = $this->commonUtility->brand($slug);
                $userMetaTags = UserMetaTags::where('userId', $brand->id)->where('page_type', 'Brand_Main')->first();
                $seoTags = Seotag::where('user_id',$brand->id)->first();
                if(!empty($userMetaTags)){
                    $metaDescription = $userMetaTags->description;
                    $metaTitle = $userMetaTags->title;
                }
                $article = $this->brandUtility->brandFeatureImage($brand->id);
                if ($article) {
                    $image = $article['videoImage'] ? $article['videoImage'] : $article['feature_image'];
                    $media = [
                        'type' => $article['type'],
                        'url' => env('IMAGE_PROXY_URL') . '/brand/featureImage/' . $image,
                        'path' => 'brand/featureImage/' . $image
                    ];
                }
                $title = $metaTitle ?? $seoTags->title ?? $meta->title;
                $description = $metaDescription ?? $seoTags->description ?? $meta->description;
                $keywords = $userMetaTags->keywords ?? $seoTags->descriptor ?? $meta->keywords;
                $ogTitle = $metaTitle ?? $seoTags->title ?? null;
                $ogDescription = $metaDescription ?? $seoTags->description ?? null;
                $data = $this->metaUtility->details($title,  $description, $keywords, 
                                $meta->author, $meta->robots, $index=null, $ogTitle, $ogDescription, 
                                $meta->ogSiteName, $ogUrl=null, $media=null);
            } else{
                $category = $this->categoryUtility->getDetails($slug);
                $title = $category->category_meta_title ?? $meta->title ?? null;
                $description = !empty($category->category_meta_description) ? $category->category_meta_description : null;
                $data =  $this->metaUtility->details($title,$description,
                        $category->category_keyword,$meta->author, $meta->robots, $index=null, 
                        $category->category_meta_title, $ogDescription = null,$meta->ogSiteName );
            }
        } else{
            $description = $meta->description;
            if(!empty($page)) {
                $description = $this->getDescription($page);
            }
            $ogImage =  !empty($meta->ogImage) ? env('AWS_S3_URL') . '/static/' . $meta->ogImage : null;
            $twitterImage = !empty($meta->twitterImage) ? env('AWS_S3_URL') . '/static/' . $meta->twitterImage : null;
           $data = $this->metaUtility->details($meta->title,$description,$meta->keywords,$meta->author,$meta->robots,$index = null,$meta->title,$description, $meta->ogSiteName,$ogUrl=null,$media=null,$ogImage,$twitterImage);
        }
        return response()->json([
            'data' => $data
        ]);
    }

    public function getDescription($page) {
        if($page == "termsofuse") {
            $description = "Please read the following terms and conditions carefully. By using the pages in this site, you agree to these terms and conditions. If you do not agree, you should not use this site.";
        } else if($page == "contact") {
            $description = "Have a great story that you'd like to see featured on 1851 Franchise? Send us a message and we'll have one of our editors look into it.";
        } else if($page == "about") {
            $description = "1851Franchise.com was created in 2012 as a platform to share news about the franchise industry. Over time, the monthly readership has grown to more than 300,000 franchisors, franchisees and prospects.";
        }
        return $description ?? null;
    }
}
