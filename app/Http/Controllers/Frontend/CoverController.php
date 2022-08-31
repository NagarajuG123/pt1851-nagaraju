<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\UserCategory;
use App\Utility\CoverUtility;
use App\Utility\MetaUtility;
use App\Utility\CommonUtility;
use App\Transformers\CoverTransformer;
use App\Transformers\CoverDetailTransformer;

class CoverController extends Controller
{
  public $coverUtility,$metaUtility,$commonUtility;
  public function __construct(){
      $this->coverUtility = app(CoverUtility::class);
      $this->metaUtility = app(MetaUtility::class);
      $this->commonUtility = app(CommonUtility::class);
  }
  public function index(Request $request){
    $type = $request->attributes->get('type');
    $limit = $request->query('limit') ?? 6;
    $slug = $request->query('slug');
    $date = (new \DateTime())->format('Y-m-d');
    if(!empty($slug)){
      list($month, $year, $aday, $day) = explode('/',$slug);
      $actualDate = $year.'-'.$month.'-'.$aday;
      $startDate = $year.'-'.$month.'-'.'16';
      $endDate = date('Y-m-t', strtotime($startDate));
      if ($day == 3) {
          $startDate = $year.'-'.$month.'-'.'1';
          $endDate = date('Y-m-t', strtotime($startDate));
      } else if ($day == 1) {
              $startDate = $year.'-'.$month.'-'.'1';
              $endDate = $year.'-'.$month.'-'.'15';
      }
    }
    if( $type ==  Issue::ENDPOINT_IMAGES ){
        $ids = $this->coverUtility->getCover($date)->take(4)->pluck('id');
        $cover = $this->coverUtility->getCover($date)->whereNotIn('id',$ids)->paginate($limit);
        $hasMore = $cover->hasMorePages();
        $data = CoverTransformer::collection($cover);
    }else if( $type == Issue::ENDPOINT_DETAILS ){
      $issue = $this->coverUtility->getIssue($actualDate);
      $id = $this->coverUtility->getDetails($startDate,$endDate,$issue)->take(1)->pluck('user_category.id');
      $articles = $this->coverUtility->getDetails($startDate,$endDate,$issue)->whereNotIn('user_category.id',$id)->paginate($limit);
      $hasMore = $articles->hasMorePages();
      $data = CoverDetailTransformer::collection($articles);
    }
    else if( $type == Issue::ENDPOINT_BANNER ){
      if(empty($slug)){
        $cover = $this->coverUtility->getCover($date)->take(4)->get();
        $data = CoverTransformer::collection($cover);
      }else{
        $issue = $this->coverUtility->getIssue($actualDate);
        $articles = $this->coverUtility->getDetails($startDate,$endDate,$issue)->take(1)->get();
        $data = CoverDetailTransformer::collection($articles);
      }
    }
    $response = [ 
      'hasMore' =>  $hasMore ?? null,
      "data" =>  $data ?? null
    ];
    if( $type == Issue::ENDPOINT_BANNER && !empty($slug)){
      $response['cover'] = [
        'type' => 'image',
        'url' =>  !empty($issue->feature_image) ? env('AWS_S3_URL').'/covers/'.$issue->feature_image : null,
        'path' => !empty($issue->feature_image) ? 'covers/'.$issue->feature_image : null, 
      ];
    }
    return response()->json($response);
  }
}
