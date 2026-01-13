<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EnrollmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Enroll user in a course.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $this->authorize('create', Enrollment::class);

        $enrollment = Enrollment::create([
            'user_id' => $request->user()->id,
            'course_id' => $request->course_id,
            'status' => 'active',
        ]);

        return $this->success($enrollment, 'Enrolled successfully', 201);
    }

    /**
     * Get authenticated student's enrollments.
     */
    public function myCourses(Request $request)
    {
        $enrollments = Enrollment::with('course')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->success($enrollments);
    }

    /**
     * View all enrollments (Admin only).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Enrollment::class);

        $enrollments = Enrollment::with(['student:id,name,email', 'course:id,title'])
            ->latest()
            ->get();

        return $this->success($enrollments);
    }
}
