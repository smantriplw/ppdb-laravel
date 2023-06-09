<?php
namespace App\Http\Controllers\Api\Archives;

use App\Http\Controllers\ApiController;
use App\Models\Archive;
use Illuminate\Http\Request;

class ShowArchivesController extends ApiController
{
    public function show(Request $request)
    {
        $isVerified = $request->exists('verified');
        $isAll = $request->exists('all');

        $perPage = request('offset', 25);
        $page    = request('page', 1);

        $paginator = null;
        if (!$isAll)
            $paginator = Archive::with([
                'verifyData',
                'verificator',
            ])->where('verificator_id', $isVerified ? '!=' : '=', null)->paginate(
                $perPage,
                '*',
                'archives',
                $page
            );
        else {
            $paginator = Archive::with(['verifyData', 'verificator'])->paginate(
                $perPage,
                '*',
                'archives',
                $page
            );
        }

        $currPage = $paginator->currentPage();
        $items = $paginator->items();

        for ($i=0; $i < count($items); $i++) { 
            $items[$i]['index'] = $i+1;
        }

        return response()->json([
            'data' => [
                'archives' => $items,
                'prevPage' => $currPage === 1 ? 1 : $currPage-1,
                'nextPage' => $currPage === $paginator->lastPage() ? $currPage : $currPage+1,
                'totalPage' => $paginator->lastPage(),
            ]
        ]);
    }

    public function singleShow(string $id)
    {
        $archive = Archive::find($id);

        return response()->json([
            'data' => $archive,
        ]);
    }
}