<?php

use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // Create the employee role before testing registration
    Role::create(['name' => 'employee', 'guard_name' => 'web']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'job_title' => 'Software Developer',
        'department' => 'Engineering',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});