<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\BlogStoreRequest;
use App\Http\Requests\api\v1\BlogUpdateRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{

    /**
     * Display a listing of the blog resource.
     */
    public function index()
    {
        try {
            $user = request()->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $perPage = (int) request()->get('per_page', 10);
            $search = request()->get('search', null);
            $filter = request()->get('filter', null);

            $query = Blog::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            if ($filter === 'most_liked') {
                $query->orderByDesc('likes_count');
            } else {
                $query->orderByDesc('created_at');
            }

            $blogs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Blogs fetched successfully',
                'data' => $blogs
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error fetching blogs: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogStoreRequest $request)
    {
        try {
            $data = $request->validated();

            $blog = Blog::create([
                'title' => $data['title'],
                'content' => $data['content'],
            ]);

            if ($data['image']) {
                $blog->addMediaFromRequest('image')->toMediaCollection('images');
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog created successfully',
                'data' => $blog
            ], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('Error Creating blog: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogUpdateRequest $request, String $id)
    {
        try {

            $blog = Blog::find($id);

            if (! $blog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();

            $blog->update($data);

            if ($request->hasFile('image')) {
                $blog->clearMediaCollection('images');

                $blog->addMediaFromRequest('image')
                    ->toMediaCollection('images');
            }

            return response()->json([
                'success' => true,
                'message' => 'Blog Updated successfully',
                'data' => $blog
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error Updated blog: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $blog = Blog::find($id);

            if (! $blog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $blog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Blog Deleted successfully',
                'data' => $blog
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('Error Deleted blog: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle like/unlike for the given blog by the authenticated user.
     */
    public function toggleLike(Request $request, Blog $blog)
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            if ($blog->isLikedByUser($user->id)) {
                $blog->likes()->where('user_id', $user->id)->delete();
                $liked = false;
                $message = 'Blog unliked successfully';
            } else {
                $blog->likes()->create(['user_id' => $user->id]);
                $liked = true;
                $message = 'Blog liked successfully';
            }

            $likesCount = $blog->likes()->count();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'liked' => $liked,
                    'likes_count' => $likesCount,
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {

            Log::error('Error toggling like: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
