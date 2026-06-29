<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

abstract class Controller
{
    
}


class UserController extends Controller
{
    public function show(User $user)
    {
        return Inertia::render('User/Show', [
            'user' => $user
        ]);
    }
}