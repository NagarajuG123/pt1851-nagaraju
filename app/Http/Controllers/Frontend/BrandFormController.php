<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Utility\BrandUtility;
use App\Utility\CommonUtility;
use App\Services\InquiryService;

class BrandFormController extends Controller
{
    public $brandUtility,$commonUtility,$inquireService;

    public function __construct()
    {
        $this->brandUtility = app(BrandUtility::class);
        $this->commonUtility = app(CommonUtility::class);
        $this->inquireService = app(InquiryService::class);
    }
  public function contact(Request $request)
  {
    $slug = $request->query('slug');
    $data = $this->brandUtility->brandExist($slug);
    if($data['status'] == 200) {
        $brandId = $data['data']->id;
        return  $this->inquireService->create($request,$brandId);
    } else{
      return $data['message'];
    }
  }
}
