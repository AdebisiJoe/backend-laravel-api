<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserPreference;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Resources\UserPreferenceResource;
use jcobhams\NewsApi\NewsApi;

class UserPreferenceController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter');

        $preferences = UserPreference::where('user_id', Auth::user()->id)
            ->when($filter, function ($q) use ($filter){
                return $q->where('type', $filter);
            })
            ->get();

        return $this->sendResponse(UserPreferenceResource::collection($preferences), 'User Preference retrieved.');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $input['user_id'] = Auth::user()->id;

        $validator = Validator::make($input, [
            'type' => 'required',
            'name' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $preference = UserPreference::updateOrCreate($input);

        return $this->sendResponse(new UserPreferenceResource($preference), 'User Preference created.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkStore(Request $request)
    {
        $preferences = $request->input('preferences');

        foreach ($preferences as $preference)
        {
            $validator = Validator::make($preference, [
                'type' => 'required',
                'name' => 'required'
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $data = [
                'user_id'  => Auth::user()->id,
                'type'  => $preference['type'],
                'name'  => $preference['name']
            ];

            $preference = UserPreference::updateOrCreate($data);
        }

        $preferences = UserPreference::where('user_id', Auth::user()->id)->get();

        return $this->sendResponse(UserPreferenceResource::collection($preferences), 'User Preference created.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userPreference = UserPreference::where(['id' => $id, 'user_id' => Auth::user()->id])
                ->first();
        if($userPreference) {
            $userPreference->delete();

            return $this->sendResponse([], 'User Preference deleted.');
        }

        return $this->sendError('User Preference Not Found', []);

    }



    /**
     * Display a listing of top-headlines/sources.
     *
     * @return \Illuminate\Http\Response
     */
    public function newsfeed(Request $request)
    {
        $userCategory = UserPreference::where('user_id', Auth::user()->id)->pluck('name')->toArray();
        $category = implode(",", $userCategory);
        $language = "en";
        $country = "us";
//        return $userCategory;
        $from = date("Y-m-d");
        $to = date("Y-m-d");
        $sortBy = "popularity";
        $pageSize = 100;
        $page = 1;
//        return $from;

        $newsapi = new NewsApi(env('NEWS_API_KEY'));

        $all_articles = $newsapi->getEverything(
           "apple" ,
            null,
            null,
            null,
            $from,
            $to,
            $language,
            $sortBy,
            $pageSize,
            $page);
        return $all_articles;

        $all_articles = $newsapi->getTopHeadLines(
            NULL,
            NULL,
            $country,
            NULL,
            $pageSize,
            $page
        );
        return $all_articles;
        $sources = $newsapi->getSources($category, $language, $country);

        return $sources;

        if($sources->sources && count($sources->sources) > 0) {
            return $this->sendResponse($sources->sources, 'Here are your news feed');
        } else {
            return $this->sendResponse([], 'No News Feed',);
        }
    }
}
