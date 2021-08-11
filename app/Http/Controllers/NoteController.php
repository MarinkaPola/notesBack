<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DetachAttachmentRequest;
use App\Http\Requests\Attachment\DownloadAttachmentRequest;
use App\Http\Requests\Note\ViewNoteRequest;
use App\Http\Resources\NoteResource;
use App\Http\Resources\NoteResourceCollection;
use App\Models\Attachment;
use App\Models\Note;
use App\Models\User;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Scopes\NoteQuery;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;



class NoteController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        /** @var User $user */
        $user=auth()->user();
        $search = request()->get('search');
        $notes = NoteQuery::search(
            Note::query(), $search
        )->where('user_id', $user->id)->with(['user'])->latest()->paginate(10);

        return $this->success(NoteResourceCollection::make($notes));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreNoteRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Note $note */
        $note = $user->notes()->create(array_merge($request->only(
            'title', 'text', 'visibility'
        ), [
            'uuid' => Str::uuid()
            ]));

        if ($request->has('file')) {
            $note->attachments()->create([
                'file_name' => Attachment::saveFile($request->file('file')),
                'file_original_name' => $request->file('file')->getClientOriginalName(),
                'ext' =>  $request->file('file')->getClientOriginalExtension()
            ]);
        }

        return $this->created(NoteResource::make($note));

    }

    /**
     * Display the specified resource.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Note $note)
    {
        $this->authorize('show', $note);

        return $this->success(NoteResource::make($note)->load(['attachments']));
    }



    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $this->authorize('update', $note);

        $note->update($request->only('title', 'text', 'visibility'));

        if ($request->has('file')) {
            $note->attachments()->create([
                'file_name' => Attachment::saveFile($request->file('file')),
                'file_original_name' => $request->file('file')->getClientOriginalName(),
                'ext' =>  $request->file('file')->getClientOriginalExtension(),
            ]);
        }

        if ($request->has('delete_attachments')) {
            $id_attachments = $request->get('delete_attachments');
            foreach ($id_attachments as $id) {
                /** @var Attachment $attachment */
                $attachment = $note->attachments()->where('id', $id)->first();

                if($attachment) {
                    $attachment->deleteFile();
                }
            }
        }

        return $this->success(NoteResource::make($note));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Note $note)
    {
        $this->authorize('destroy', $note);

        $note->delete();

        return $this->success('Record deleted.', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     *
     * @param DetachAttachmentRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function detach_file(DetachAttachmentRequest $request, Note $note)
    {
        $this->authorize('detach_file', $note);

        /** @var Attachment $attachment */
        $attachment = $note->attachments()->where('id', $request->get('id'))->first();

        if ($attachment) {
            $attachment->deleteFile();
        }

        return $this->success('You have deleted the requested file');
    }

    /**
     *
     * @param ViewNoteRequest $request
     * @param Note $note
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function share(ViewNoteRequest $request, Note $note)
    {
        $this->authorize('share', $note);

        $email = $request->get('email');

        $note->share(User::whereEmail($email)->first());

         return $this->success('You have opened access for email ' . $email);
    }


    /**
     *
     * @param DownloadAttachmentRequest $request
     * @param Note $note
     * @return string
     * @throws AuthorizationException
     */
    public function download(DownloadAttachmentRequest $request, Note $note): string
    {
        $this->authorize('download', $note);

        /** @var Attachment $attachment */
        $attachment = $note->attachments()->where('id', $request->get('id'))->first();

            return  response()->download($attachment->filePath(), $attachment->file_original_name);
        }

    /**
     * Display a listing of the resource.
     *
     * @return
     */
    public function notes_shared_you()
    {
        /** @var User $user */
        $user = auth()->user();
        $notes = $user->user_notes()->latest()->paginate();

        return $this->success(NoteResourceCollection::make($notes));
    }
}
