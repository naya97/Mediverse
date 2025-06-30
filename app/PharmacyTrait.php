<?php

namespace App;

use App\Models\Pharmacy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait PharmacyTrait
{
    public function getAllPharmacies(Request $request): JsonResponse
    {
        $isPaginate = $request->boolean('isPaginate', true);
        $pageSize = $request->input('size', 5);
        $page = $request->input('page', 1);

        $query = Pharmacy::select('id', 'name', 'start_time', 'finish_time', 'phone', 'latitude', 'longitude', 'location');

        if ($isPaginate) {
            $pharmacies = $query->paginate($pageSize, ['*'], 'page', $page);
            $pharmacies->withQueryString();
            $response = [
                'message' => 'Pharmacies retrieved successfully',
                'data' => $pharmacies->items(),
                'meta' => [
                    'current_page' => $pharmacies->currentPage(),
                    'last_page' => $pharmacies->lastPage(),
                    'total' => $pharmacies->total(),
                    'per_page' => $pharmacies->perPage(),
                ],
            ];
        } else {
            $pharmacies = $query->get();
            $response = [
                'message' => 'Pharmacies retrieved successfully',
                'data' => $pharmacies,
            ];
        }

        return response()->json($response, 200);
    }

    /////
    public function searchPharmacyByName(Request $request): JsonResponse
    {
        $isPaginate = $request->boolean('isPaginate', true);
        $pageSize = $request->input('size', 5);
        $page = $request->input('page', 1);
        $searchTerm = $request->input('name');

        if (!$searchTerm) {
            return response()->json(['message' => 'Search term is required'], 400);
        }

        $query = Pharmacy::search($searchTerm);

        if ($isPaginate) {
            $results = $query->paginate($pageSize, ['*'], 'page', $page);
            $results->withQueryString();

            if ($results->isEmpty()) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            $responseData = collect($results->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'start_time' => $item->start_time,
                    'finish_time' => $item->finish_time,
                    'phone' => $item->phone,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'location' => $item->location,
                ];
            });

            $response = [
                'message' => 'Pharmacies retrieved successfully',
                'data' => $responseData,
                'meta' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                ],
            ];
        } else {
            $results = $query->get();

            if ($results->isEmpty()) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            $responseData = $results->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'start_time' => $item->start_time,
                    'finish_time' => $item->finish_time,
                    'phone' => $item->phone,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'location' => $item->location,
                ];
            });

            $response = [
                'message' => 'Pharmacies retrieved successfully',
                'data' => $responseData,
            ];
        }

        return response()->json($response, 200);
    }


    /////
    public function getPharmacy(Request $request)
    {
        $pharmacy = Pharmacy::select('id', 'name', 'start_time', 'finish_time', 'phone', 'latitude', 'longitude', 'location')
            ->where('id', $request->id)
            ->first();

        if (!$pharmacy) {
            return response()->json(['message' => 'Pharmacy not found'], 404);
        }

        return response()->json($pharmacy, 200);
    }
}
