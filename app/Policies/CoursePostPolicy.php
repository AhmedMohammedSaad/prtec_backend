<?php

namespace App\Policies;

use App\Models\CoursePost;
use App\Models\User;
use App\Models\Course;

class CoursePostPolicy
{
    /**
     * Determine whether the user can create posts (Admin only).
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the post (Admin only).
     */
    public function update(User $user, CoursePost $coursePost): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the post (Admin only).
     */
    public function delete(User $user, CoursePost $coursePost): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the post (Student enrollment check).
     */
    public function view(User $user, CoursePost $coursePost): bool
    {
        // Admin or Author can always view
        if ($user->isAdmin() || $user->id === $coursePost->course->created_by) {
            return true;
        }

        // Student must be enrolled
        return $user->enrollments()->where('course_id', $coursePost->course_id)->exists();
    }
}
