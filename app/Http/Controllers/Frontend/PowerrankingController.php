<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\StaticPage;
use App\Models\PowerRanking;
use App\Models\UserCategory;
use App\Utility\CommonUtility;
use App\Utility\MetaUtility;

class PowerrankingController extends Controller
{
    public $commonUtility,$metaUtility;
    public function __construct()
    {
        $this->commonUtility = app(CommonUtility::class);
        $this->metaUtility = app(MetaUtility::class);
    }
    public function index()
    {
      $model = StaticPage::where('id' , StaticPage::POWER_RANKING)->first();
      $publication = $this->commonUtility->publication();
      $yesterday     =   (new \DateTime())->modify('-1 day')->format('Y-m-d');
      $today      =   (new \DateTime())->format('Y-m-d');
      $ogTitle = 'Franchise Power Rankings '.$yesterday.' - '. $today;
      $ogUrl = env('FE_URL').'/powerrankings';
      $media = $this->commonUtility->media($model->media->name,$model->image_path);
      $title = 'Power Rankings | Franchise Brands | 1851 Franchise';
      $description = "Welcome to the Franchise Power Rankings, a glimpse into the brand stories that are trending on 1851 and resonating most with our readers.";
      $data = [
        'title' =>$model->title ?? null,
        'short_description' => $model->description ?? null,
        'media' => $media ?? null,
        'brands' => $this->getBrands(),
        'ga_code' => env('GA_TRACKING_CODE'),
        'meta' =>  $this->metaUtility->details($title,  $description, $publication->keywords, 
        $publication->author, $publication->robots, $index=null, $ogTitle, $ogDescription = null, 
        $publication->ogSiteName,$ogUrl, $media ),
      ];

      return [ 'data' => $data ];
    }

    public function getBrands(){
      $powerranking =  PowerRanking::select('brand_id','rank','points','last_rank')->orderBy('points', 'DESC')->orderBy('id','ASC')->limit(20)->get();
      if(!empty($powerranking)){
        foreach($powerranking as $data){
          $article = UserCategory::where('user_id', $data->brand_id)
                    ->approve()
                    ->publish()
                    ->active()
                    ->orderBy('publish_date' , 'DESC')->first();
          $logo = !empty($data->brand->photo) ? $data->brand->photo : $data->brand->brandLogo;
          $brands[] = [
            'id' => $data->brand_id,
            'logo' => env('AWS_S3_URL') . '/brand/logo/'. $logo,
            'rank' => $data->rank,
            'name' => $data->brand->company,
            'slug' => $data->brand->brand_url,
            'last_week_rank' => $data->last_rank,
            'latest_story' => [
              'id' => $article->id ?? null,
              'title' => $article->title ?? null,
              'slug' => Str::slug($article->title) . '-' . $article->id,
            ],
            ];
        }
      }
        return $brands ?? null;
    }
}