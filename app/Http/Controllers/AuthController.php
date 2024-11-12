<?php
namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(AuthRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $response = $this->authService->login($credentials);
        return response()->json($response, isset($response['status']) && $response['status'] === 'error' ? 401 : 200);
    }

    public function register(RegisterRequest $request)
    {
        $response = $this->authService->register($request->validated());
        return response()->json($response);
    }

    public function logout()
    {
        $response = $this->authService->logout();
        return response()->json($response);
    }

    public function refresh()
    {
        $response = $this->authService->refresh();
        return response()->json($response);
    }
}
