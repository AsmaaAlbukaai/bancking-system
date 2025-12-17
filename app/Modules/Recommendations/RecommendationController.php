<?php

namespace App\Modules\Recommendations;

use App\Http\Controllers\Controller;

class RecommendationController extends Controller
{
    public function getMyRecommendations()
    {
        $user = auth()->user();

        $service = new RecommendationService();
        $data = $service->getRecommendations($user);

        return response()->json($data);
    }
}
