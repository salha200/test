<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Illuminate\Support\Facades\Hash;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $userService;

    public function setUp(): void
    {
        parent::setUp();
        $this->withSession(['key' => 'value']);

        // تهيئة UserService باستخدام Mock
        $this->userService = Mockery::mock(UserService::class);
        $this->app->instance(UserService::class, $this->userService);

        // إنشاء دور Admin لاختبار الصلاحيات
        Role::create(['name' => 'Admin']);
    }

    /** @test */
    public function it_can_list_all_users()
    {
        $this->userService
            ->shouldReceive('getAllUsers')
            ->once()
            ->andReturn(collect([
                ['id' => 1, 'name' => 'User One', 'email' => 'user1@example.com'],
                ['id' => 2, 'name' => 'User Two', 'email' => 'user2@example.com']
            ]));

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'users' => [
                         ['id' => 1, 'name' => 'User One', 'email' => 'user1@example.com'],
                         ['id' => 2, 'name' => 'User Two', 'email' => 'user2@example.com']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_show_a_specific_user()
    {
        $user = User::factory()->create();

        $this->userService
            ->shouldReceive('getUserById')
            ->with($user->id)
            ->once()
            ->andReturn($user);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'user' => [
                         'id' => $user->id,
                         'name' => $user->name,
                         'email' => $user->email,
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_404_when_user_not_found()
    {
        $this->userService
            ->shouldReceive('getUserById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 'error', 'message' => 'User not found']);
    }

    /** @test */

    public function test_it_can_update_a_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Prepare updated data
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        // Send update request
        $response = $this->withoutMiddleware()->putJson("/api/users/{$user->id}", $updatedData);

        // Check response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User updated successfully',
                'user' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ]
            ]);
    }



    /** @test */
    public function it_can_delete_a_user()
{
    $user = User::factory()->create();

    $this->userService
        ->shouldReceive('deleteUser')
        ->with($user->id)
        ->once()
        ->andReturn(true);

    $response = $this->withoutMiddleware()->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(200)
             ->assertJson(['status' => 'success', 'message' => 'User deleted successfully']);
}



}
