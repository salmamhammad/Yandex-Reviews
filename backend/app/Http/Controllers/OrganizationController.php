<?php
namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\YandexReviewsParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    public function store(Request $request, YandexReviewsParser $parser)
    {
        $validator = Validator::make($request->all(), [
            'url' => ['required', 'url', 'regex:/^https:\/\/(yandex\.(ru|com)|maps\.yandex)\/.*/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $url = $request->input('url');

        // Parse data from Yandex
        try {
            $data = $parser->parse($url);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to parse Yandex page: ' . $e->getMessage()], 500);
        }

        // Save or update organization
        $org = Organization::updateOrCreate(
            ['business_id' => $data['business_id']],
            [
                'name'           => $data['name'],
                'url'            => $data['url'],
                'average_rating' => $data['average_rating'],
                'total_ratings'  => $data['total_ratings'],
                'total_reviews'  => $data['total_reviews'],
                'last_synced_at' => now(),
            ]
        );

        // Delete old reviews and insert new ones
        $org->reviews()->delete();
        foreach ($data['reviews'] as $review) {
            try {
                $review['date'] = Carbon::parse($review['date'])
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                $review['date'] = null;
                Log::warning(
                    'Invalid review date: ' . $review['date']
                );
            }
            $org->reviews()->create($review);
        }

        return response()->json([
            'message'      => 'Organization data synced successfully',
            'organization' => $org->fresh(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $org     = Organization::with('reviews')->findOrFail($id);
        $perPage = 50;
        $reviews = $org->reviews()->paginate($perPage);

        return response()->json([
            'organization' => $org,
            'reviews'      => $reviews,
        ]);
    }

    public function index(Request $request)
    {
        $orgs = Organization::latest()->get();
        return response()->json($orgs);
    }
}
