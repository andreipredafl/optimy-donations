<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {

        if (auth()->user()->cannot('users.view')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }
}
