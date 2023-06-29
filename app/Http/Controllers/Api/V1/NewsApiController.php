<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserPreference;
use App\Http\Controllers\Api\V1\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use jcobhams\NewsApi\NewsApi;

class NewsApiController extends BaseController
{
    protected $newsapi;
    public function __construct()
    {
        $this->newsapi = new NewsApi(env('NEWS_API_KEY'));
    }
    /**
     * Display a listing of top-headlines/sources.
     *
     * @return \Illuminate\Http\Response
     */
    public function articles(Request $request)
    {
        $date = $request->input('date');
        $keyword = $request->input('keyword') ?? "apple";
        $source = $request->input('$source') ?? null;
        $language = "en";
        $country = "us";
        $from = ($date) ? $date : date("Y-m-d", strtotime('-1 day'));
        $to = ($date) ? $date :date("Y-m-d", strtotime('-1 day'));
        $sortBy = "popularity";
        $pageSize = 100;
        $page = 1;

        $all_articles = $this->newsapi->getEverything(
            $keyword ,
            $source,
            null,
            null,
            $from,
            $to,
            $language,
            $sortBy,
            $pageSize,
            $page);

        return $this->sendResponse($all_articles->articles, 'Article retrieved');
    }

    public function newsfeed(Request $request)
    {
        $date_ = $request->input('date');
        $keyword = $request->input('keyword') ?? "apple";

        $userSource = UserPreference::where('user_id', Auth::user()->id)
            ->where('type', 'source')
            ->pluck('name')->toArray();

        $source = implode(",", $userSource);

        $language = "en";
        $country = "us";
        $from = date("Y-m-d", strtotime('-1 day'));
        $to = date("Y-m-d", strtotime('-1 day'));
        $sortBy = "popularity";
        $pageSize = 100;
        $page = 1;

        $all_articles = $this->newsapi->getEverything(
            $keyword ,
            $source,
            null,
            null,
            $from,
            $to,
            $language,
            $sortBy,
            $pageSize,
            $page);

        return $this->sendResponse($all_articles->articles, 'Article retrieved');
    }

    public function getSources()
    {
        $userCategory = UserPreference::where('user_id', Auth::user()->id)
            ->where('type', 'category')
            ->pluck('name')->toArray();

        $category = implode(",", $userCategory);

        $language = "en";

        $country = "us";

        $sources = $this->newsapi->getSources($category, $language, $country);

        if($sources->status = "ok") {
            return $this->sendResponse($sources->sources, 'Sources retrieved');
        } else {
            return $this->sendResponse([], 'Can\'t retrieved sourced.');
        }
    }
}
