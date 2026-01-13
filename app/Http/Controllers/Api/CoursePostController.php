<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCoursePostRequest;
use App\Models\Course;
use App\Models\CoursePost;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CoursePostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new post/lesson for a course.
     */
    public function store(StoreCoursePostRequest $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorize('create', CoursePost::class); // Check admin

        $post = $course->posts()->create($request->validated());

        return $this->success($post, 'Lesson created successfully', 201);
    }

    /**
     * Get all posts for a course (Public with filtered fields).
     */
    public function index($courseId)
    {
        $posts = CoursePost::where('course_id', $courseId)
            ->orderBy('order')
            ->select('id', 'course_id', 'title', 'order', 'is_free') // Hide content by default
            ->get();

        return $this->success($posts);
    }

    /**
     * Get single post content (Protected).
     */
    public function show($id)
    {
        $post = CoursePost::with('course')->findOrFail($id);
        
        $this->authorize('view', $post); // Enforce enrollment or admin

        return $this->success($post);
    }

    /**
     * Update a post.
     */
    public function update(StoreCoursePostRequest $request, $id)
    {
        $post = CoursePost::findOrFail($id);
        $this->authorize('update', $post);

        $post->update($request->validated());

        return $this->success($post, 'Lesson updated successfully');
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {
        $post = CoursePost::findOrFail($id);
        $this->authorize('delete', $post);

        $post->delete();

        return $this->success([], 'Lesson deleted successfully');
    }

    /**
     * Batch update for reordering.
     */
    public function batchUpdate(Request $request)
    {
        $this->authorize('create', CoursePost::class); // Needs admin

        $request->validate([
            'posts' => 'required|array',
            'posts.*.id' => 'required|exists:course_posts,id',
            'posts.*.order' => 'required|integer',
        ]);

        foreach ($request->posts as $item) {
            CoursePost::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return $this->success([], 'Lessons reordered successfully');
    }
}
