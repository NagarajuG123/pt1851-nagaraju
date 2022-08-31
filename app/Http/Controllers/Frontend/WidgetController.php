<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Widgets;
use App\Models\WidgetStories;
use App\Models\UserCategory;

class WidgetController extends Controller
{
    public function index(Request $request){
        $widgetId = $request->query('id');
        $widget = Widgets::where('id','=',$widgetId)->first();
        $data = null;
        if($widget) {
            $data = [
                'title' => $widget->title ?? null,
                'layout' => $widget->layout_id ?? null,
                'seeMore' => $widget->see_more ? true : false,
                'pageUrl' => $widget->page_url ?? null,
                'stories' => $this->getStories($widget) ?? null,
            ];
            return $data;
        } else {
            return response()->json([
                'message' => 'Widget is not found'
            ]);
        }
        
    }

    public function getStories($widget){
        $widgetStories = 
        WidgetStories::select(['user_category.id','widget_stories.story_id','widget_stories.widget_id',
        'user_category.title','user_category.descriptor','user_category.publish_date','user_category.modified_date','user_category.videoImage','user_category.image' ])
        ->join('user_category', 'user_category.id', '=', 'widget_stories.story_id')
            ->where('widget_id', '=', $widget->id)
            ->orderBy('publish_date', 'DESC')
            ->take($widget->layout->article_count)->get();
        $storyData = null;
        foreach($widgetStories as $widgetStory){
            $image =   $widgetStory->videoImage ?? $widgetStory->image;
            $storyData[] = [
                'id' => $widgetStory->id,
                'title' => $widgetStory->title,
                'slug' =>  Str::slug($widgetStory->title) . '-' . $widgetStory->id,
                'short_description' => $widgetStory->descriptor,
                'brand' => [
                    'id' => $widget->brand->id ?? '1851',
                    'name' => $widget->brand->company ?? '1851',
                    'slug' => $widget->brand->brand_url ?? '1851',
                ],
                'posted_on' => $widgetStory->publish_date,
                'last_modified' => $widgetStory->modified_date,
                'media' => [
                    'url' => env('IMAGE_PROXY_URL') . '/story/featureImage/' . $image,
                    'path' => 'story/featureImage/' . $image,
                ]
            ];
        }
        return $storyData ?? null;
    }
}
