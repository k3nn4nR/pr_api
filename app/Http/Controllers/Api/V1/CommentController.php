<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreComment;
use App\Events\RegistrationEvent;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        //
    }

    /**
     * Get the specified resource in storage.
     */
    public function get_comment_comments(Comment $comment)
    {
        return $comment->comments;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update_comment_comments(StoreComment $request, Comment $comment)
    {
        DB::beginTransaction();
        try {
            $comment->comments()->create([
                'comment' => $request,
                'user_id' => auth()->user->id
            ]);     
            event(new RegistrationEvent(_('Tags Updated')));
            return response()->json(_('Tags Updated',200));
        } catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(),200);
        }
    }   
}
