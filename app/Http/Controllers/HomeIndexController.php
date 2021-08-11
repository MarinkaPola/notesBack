<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResourceCollection;
use App\Models\Note;
use App\Scopes\NoteQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeIndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $notes = NoteQuery::search(
            NoteQuery::published(Note::query()), $search
        )->with(['user'])->latest()->paginate(10);
        return $this->success(NoteResourceCollection::make($notes));
    }

}
