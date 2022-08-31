<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Models\NewsletterSubscription;
use App\Http\Controllers\Controller;
use Newsletter;
use \DrewM\MailChimp\MailChimp;

class NewsletterController extends Controller
{
  public function __construct()
    {
      
    }
    public function signup(Request $request)
    {
      $model = new NewsletterSubscription();
      if(!empty($request->all())) {
        $email = $request->input('email');
        //add validation
        NewsletterSubscription::create($request->all());
        $MailChimp = new MailChimp(env('MAILCHIMP_APIKEY'));
        $list_id = env('MAILCHIMP_LIST_ID');

        $result = $MailChimp->post("lists/$list_id/members", [
          'email_address' => $email,
          'status' => 'subscribed',
        ]);
        $message = 'Thanks for contacting us. We will email you very shortly.';

        if ($result['status'] == 400) {
          $message =  $this->getMessage($result, $email);
        } 
        return response()->json(['message' => $message]);
      } else {
        return response()->json(['message' => 'Request body is empty']);
      }
    }
    public function getMessage($data, $email) {
      $message = $email . ' is already subscribed';
      if($data['title'] == 'Member Exists') {
          $message = $email . ' is already a list member.';
      } else if($data['title'] == 'Invalid Resource') {
          $message = $data['detail'];
      }
      return $message;
  }
}