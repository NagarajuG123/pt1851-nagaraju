<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManageInquiry extends Model
{

    public $table = 'manage_inquiry';
    public $timestamps = false;   
    
    protected $fillable = ['firstName', 'lastName', 'email', 'user_id','phone','city','state','city_of_interest','state_of_interest','comments','custField','custFieldTwo','custFieldFive','netWorth','zip','country_of_interest','hearAboutUs','kids','hobby','likeBrand','employee','expYear','financial','liquidity','addFunding','whyNot','contactedDate','facebook','twitter','linkedIn','comment_on','needFromThem','lastTimeToCall','rating','fromPdf','pdfDownloadDate','submitDate','inquirydate','recieved_date','company','custFieldThree','custFieldFour'];
  
}
