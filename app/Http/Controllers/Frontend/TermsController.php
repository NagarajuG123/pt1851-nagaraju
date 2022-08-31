<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Terms;

class TermsController extends Controller
{
    public function index(){
      $terms = Terms::select('term')->first();
      $data = null;
      if(!empty($terms)){
        $data = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $terms->term));
      }
      return response()->json([ 'data' => $data ]);
    }
}
