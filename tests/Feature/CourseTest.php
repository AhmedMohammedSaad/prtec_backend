<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/courses', [
            'title' => 'Laravel 12 Masterclass',
            'description' => 'Complete guide',
            'price' => 99.99,
            'content_outline' => 'Intro, Setup, MVC',
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => true]);
        
        $this->assertDatabaseHas('courses', ['title' => 'Laravel 12 Masterclass']);
    }

    public function test_student_cannot_create_course()
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->postJson('/api/courses', [
            'title' => 'Hacker Course',
            'description' => 'Should fail',
            'price' => 0,
            'content_outline' => '...',
        ]);

        $response->assertStatus(403);
    }
}
