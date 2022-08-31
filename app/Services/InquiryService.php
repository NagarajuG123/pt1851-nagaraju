<?php 
namespace App\Services;

use App\Models\ManageInquiry;

use Illuminate\Support\Facades\Validator;

class InquiryService
{
	public function create($request,$brandId) {
        $validator = Validator::make($request->json()->all(),[
            'first_name' => 'required|min:3|max:50',
            'last_name' => 'required|min:3',
            'email' => 'required',
            'phone' => 'required|regex:/[0-9]{10}/',
            'zip' => 'required|regex:/^\d{5}$/',
            'comments' => 'required'
          ]);
          if ($validator->passes($request)) {
            $inquire = $request->all();
            $inquire['firstName'] = $request->first_name;
            $inquire['lastName'] = $request->last_name;
            $inquire['user_id'] = $brandId;
            $inquire['city'] = $request->city ?? '';
            $inquire['city_of_interest'] = $request->city_of_interest ?? '';
            $inquire['state_of_interest'] = $request->state_of_interest ?? '';
            $inquire['state'] = $request->state ?? '';
            $inquire['country_of_interest'] = $request->country_of_interest ?? '';
            $inquire['hearAboutUs'] = $request->hearAboutUs ?? '';
            $inquire['kids'] = $request->kids ?? '';
            $inquire['hobby'] = $request->hobby ?? '';
            $inquire['likeBrand'] = $request->likeBrand ?? '';
            $inquire['employee'] = $request->employee ?? '';
            $inquire['expYear'] = $request->expYear ?? '';
            $inquire['financial'] = $request->financial ?? '';
            $inquire['liquidity'] = $request->liquidity ?? '';
            $inquire['addFunding'] = $request->addFunding ?? '';
            $inquire['whyNot'] = $request->whyNot ?? '';
            $inquire['contactedDate'] = (new \DateTime())->format('Y-m-d H:i:s');
            $inquire['facebook'] = $request->facebook ?? '';
            $inquire['twitter'] = $request->twitter ?? '';
            $inquire['linkedIn'] = $request->linkedIn ?? '';
            $inquire['comment_on'] = $request->commentOn?? '';
            $inquire['needFromThem'] = $request->needFromThem?? '';
            $inquire['lastTimeToCall'] = $request->lastTimeToCall?? date('y-m-d');
            $inquire['rating'] = $request->rating?? '';
            $inquire['pdfDownloadDate'] = $request->pdfDownloadDate?? null;
            $inquire['submitDate'] = (new \DateTime())->format('Y-m-d H:i:s');
            $inquire['inquirydate'] = (new \DateTime())->format('Y-m-d H:i:s');
            $inquire['recieved_date'] = $request->recievedDate?? date('y-m-d');;
            $inquire['company'] = $request->company?? '';
            $inquire['custFieldTwo'] =$request->custFieldTwo ?? '';
            $inquire['custFieldThree'] =$request->custFieldThree ?? '';
            $inquire['custFieldFour'] =$request->custFieldFour ?? '';
            $inquire['custFieldFive'] =$request->custFieldFive ?? '';
            $inquire['custField'] =$request->custField ?? '';
            $inquire['netWorth'] =$request->netWorth ?? '';
            $userDetails =   ManageInquiry::create($inquire);
            return response()->json([
              "data" => [
                  "id" => $userDetails->id,
                  'message' => 'Successfully Submitted',
              ]
          ]);
          } else{
            return response()->json([
              "status" => 400,
              "error" => $validator->errors()
            ]);
          }
	}
}
