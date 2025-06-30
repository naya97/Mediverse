<?php

namespace App;

use Illuminate\Http\Request;

trait PaginationTrait
{
    public function paginateResponse(Request $request, $query, string $resourceLabel = 'Items')
    {
        $isPaginate = $request->boolean('isPaginate', true);
        $pageSize = $request->input('size', 10);
        $page = $request->input('page', 1);

        if ($isPaginate) {
            $paginated = $query->paginate($pageSize, ['*'], 'page', $page);
            $paginated->withQueryString();
            
            return [
                'message' => "$resourceLabel retrieved successfully",
                'data' => $paginated->items(),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'total' => $paginated->total(),
                    'per_page' => $paginated->perPage(),
                ],
            ];
        }

        $items = $query->get();
        return [
            'message' => "$resourceLabel retrieved successfully",
            'data' => $items,
        ];
    }
}
