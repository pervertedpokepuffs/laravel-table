<?php

namespace Sysniq\LaravelTable\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Is capable of rendering datatable ajax.
 */
trait HasDatatableAjax
{
    /**
     * Convert a query into a datatable object.
     *
     * @param Request $request
     * @param $base_query
     * @param callback $filter_callback
     * @param callback $post_filter_callback
     * @return void
     */
    private function getDatatablesData(Request $request, $base_query, $filter_callback = null, $post_filter_callback = null): array
    {

        $columns = collect($request->input('columns'));

        $filtered_query = $filter_callback == null ? clone $base_query : $filter_callback(clone $base_query);

        $searchable = $columns->filter(fn ($row) => $row['searchable'] == 'true');
        $search_string = $request->input('search')['value'] ?? null;

        if (count($searchable) > 0) $filtered_query->where(function ($query) use ($searchable) {
            $searchable->each(function ($row) use ($query) {
                $decoded = json_decode($row['search']['value'] ?? 'notdecodable');
                if ($decoded) {
                    $decoded->data = $row['data'];
                    switch ($decoded->type) {
                        case 'date':
                            $query->where(function ($innerQuery) use ($decoded) {
                                if ($decoded->min != null)
                                    $innerQuery->where($decoded->data, '>=', Carbon::parse($decoded->min));
                                if ($decoded->max != null)
                                    $innerQuery->where($decoded->data, '<=', Carbon::parse($decoded->max));
                            });
                            break;

                        default:
                            # code...
                            break;
                    }
                }
            });
        });

        if (count($searchable) > 0 && $search_string != null) $filtered_query->where(function ($query) use ($searchable, $search_string) {
            $searchable->each(function ($row) use ($query, $search_string) {
                $decoded = json_decode($row['search']['value'] ?? 'notdecodable');
                if ($decoded == null)
                    $query->orWhereRaw("upper({$row['data']}) like ?", [strtoupper("%{$search_string}%")]);
            });
        });

        collect($request->input('order'))->each(fn ($row) => $columns[$row['column']]['orderable'] == 'false' ? $filtered_query : $filtered_query->orderBy($columns[$row['column']]['data'], $row['dir']));

        $data = (clone $filtered_query)->skip($request->input('start'))->take($request->input('length'))->get();

        if ($post_filter_callback != null)
            $data = $post_filter_callback($data);

        $response = [
            'draw' => $request->input('draw'),
            'recordsTotal' => $base_query->count(),
            'recordsFiltered' => $filtered_query->count(),
            'data' => $data,
        ];

        return $response;
    }
}
