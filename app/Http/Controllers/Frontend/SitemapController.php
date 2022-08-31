<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Sitemap\SitemapIndex;

class SitemapController extends Controller
{

    public function index(){
        $urlContent = '<?xml version="1.0" encoding="UTF-8"?>';
        $urlContent .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $urls = [
            env('FE_URL').'/sitemap-core.xml',
            env('FE_URL').'/sitemap-brands.xml',
            env('FE_URL').'/sitemap-core-content.xml',
            env('FE_URL').'/sitemap-brand-content.xml'
        ];
        $date = (new \DateTime())->setTimezone(new \DateTimeZone('America/Chicago'))->format('Y-m-d\TH:i:sP'); 
        foreach($urls as $url) {
            $urlContent .= '<sitemap>
                        <loc>'.$url.'</loc><lastmod>'
                        . $date . '</lastmod>
                        </sitemap>';
        }
        $urlContent .= '</sitemapindex>';
        
        return response($urlContent, 200)
                  ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
