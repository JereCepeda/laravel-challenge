<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function index()
    {
        Log::info('Accediendo a la vista de login API');
        return view('auth.login');
    }
    

}
