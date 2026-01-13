<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of courses.
     */
    public function index()
    {
        $courses = Course::with('author:id,name')
            ->where('status', 'published') // Only published courses for students
            ->latest()
            ->get();
        return $this->success($courses);
    }

    /**
     * Store a newly created course in storage (Admin only).
     */
    public function store(StoreCourseRequest $request)
    {
        $this->authorize('create', Course::class);

        // Generate minimal slug from title
        $slug = Str::slug($request->title);
        // Ensure uniqueness logic could be added here if needed, keeping simple for now

        $course = Course::create([
            ...$request->validated(),
            'slug' => $slug,
            'created_by' => $request->user()->id,
        ]);

        return $this->success($course, 'Course created successfully', 201);
    }

    /**
     * Display the specified course.
     */
    public function show($id)
    {
        $course = Course::with(['author:id,name', 'posts' => function($query) {
             // For public view, only show basic post info or free ones? 
             // Logic: Show all titles/order, but content/video hidden if not free/enrolled?
             // For now returning all posts structure, content access handled by PostController or separate API
             $query->select('id', 'course_id', 'title', 'order', 'is_free');
        }])->findOrFail($id);
        
        return $this->success($course);
    }

    /**
     * Update the specified course (Admin only).
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0|max:999999.99',
            'level' => 'string|max:50',
            'status' => 'in:draft,published',
            'thumbnail' => 'nullable|url',
        ]);

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $course->update($validated);

        return $this->success($course, 'Course updated successfully');
    }

    /**
     * Remove the specified course (Admin only).
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        
        $this->authorize('delete', $course);

        $course->delete();

        return $this->success([], 'Course deleted successfully');
    }
}
