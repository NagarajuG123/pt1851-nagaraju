<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Terms;

class TermsController extends Controller
{
    public function index(){
        $term = Terms::select('term')->first();
       
        return response()->json(str_replace(array(" ,", ",", ", "), "\n", $term->term));
    }
    public function update(Request $request,$id){
        $terms = Terms::findOrFail($id);
        $terms->update($request->all());
        return response()->json([
            "success" => true,
            "message" => "Terms has been saved successfully"
          ]);
    }
}
