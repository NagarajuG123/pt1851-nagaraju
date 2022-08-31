<?php

namespace App\Utility;

use App\Models\Issue;
use App\Models\UserCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CoverUtility
{
    public function getCover($date){
        return Issue::select(['id', 'monthYear', 'articles_count', 'feature_image','pinn_article_id'])
                ->where( 'monthYear','<=', $date)
                ->where('articles_count','>=', '3')
                ->orderBy('monthYear','DESC');
    }
    public function getNoOfRows($month, $year){
        return Issue::whereMonth('monthYear','=',$month)
                    ->whereYEAR( 'monthYear','=',$year)
                    ->count();
    }
    public function getCoverUrl($monthYear){
        $month = (new \DateTime($monthYear))->format('m');
        $year = (new \DateTime($monthYear))->format('Y');
        $date = (new \DateTime($monthYear))->format('d');
        $rowCount = $this->getNoOfRows($month, $year);
        if ((int) $rowCount == 1) {
            $daypass = 3;
        } else {
            if ($date < 16) {
                $daypass = 1;
            } else {
                $daypass = 2;
            }
        }
        return '/monthlydetails/'.$month.'/'.$year.'/'.$date.'/'.$daypass;
    }
    public function getIssue($actualDate){
        return Issue::select(['id', 'monthYear',  'feature_image','pinn_article_id'])
                ->where('monthYear','=',$actualDate)
                ->orderBy('id','DESC')
                ->first();
    }
    public function getDetails($startDate, $endDate,$issue){
        return  UserCategory::select(['user_category.id','user_category.title','user_category.descriptor','user_category.description','user_category.display_status',
                    'user_category.post_status','user_category.is_sponsored','user_category.publish_date','user_category.modified_date','user_category.videoImage',
                    'user_category.image','user_category.featureImageAs','user_category.user_author','user_category.user_id','user_category.admin_cat_id','user_category.user_author',
                    'user_category.author_id','user_category.seo_title','user_category.seo_description','user_category.story_index','user_category.keyword',
                    'issue.pinn_article_id'])
                    ->leftJoin('issue','pinn_article_id','=','user_category.id')
                    ->publish()->approve()->active()->postByAuthor()
                    ->whereBetween('user_category.publish_date', [$startDate, $endDate])
                    ->orderByRaw('ISNULL(pinn_article_id), user_category.publish_date DESC,user_category.id DESC');
    }
    public function getArticleUrl($title,$id){
        $articleSlug = Str::slug($title) . '-' .  Str::slug($id);
        return $url = env('FE_URL').'/'.$articleSlug;
    }
}   