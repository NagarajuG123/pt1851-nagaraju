<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Controllers\Controller;

use App\Models\TermsOfUse;
use App\Transformers\TermsOfUseTransformer;


use App\Utility\CommonUtility;

class TermsOfUseController extends Controller
{
  public   $commonUtility;
  public function __construct()
  {
      $this->commonUtility = app(CommonUtility::class);
  }
   public function index()
   {
     $terms = TermsOfUse::select(['title','description','slug'])->get();
     return response()->json([
       "data" =>  [
          'media' => [
              'type' => "image",
              'url' => env('AWS_S3_URL') ."/static/termsofuse.png"
            ],
            'content' =>  TermsOfUseTransformer::collection($terms)
        ]
     ]);
   }
}
