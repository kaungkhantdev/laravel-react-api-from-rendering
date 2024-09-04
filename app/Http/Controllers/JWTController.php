<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\JwtService;

class JwtController extends Controller
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function loginWithAccessKey(Request $request)
    {
        // Validate the request
        $request->validate([
            'access_key' => 'required|string',
        ]);

        // Predefined access key (you can also fetch this from the database or config file)
        $validAccessKey = '1238978';

        // Check if the provided access key matches the valid one
        if ($request->input('access_key') === $validAccessKey) {
            // Access key is valid, generate a token
            $claims = [
                'access_key' => $validAccessKey, // Store the access key in the token claims
            ];

            $token = $this->jwtService->createToken($claims);

            return response()->json(['token' => $token]);
        }

        // If the access key is invalid, return an error
        return response()->json(['error' => 'Invalid access key'], 401);
    }

    public function getProtectedResource(Request $request)
    {
        // Get the token from the Authorization header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        // Validate the token
        $claims = $this->jwtService->validateToken($token);

        if ($claims) {
            // Token is valid, return the protected resource
            return response()->json(['message' => 'You are authorized', 'data' => $request->body()]);
        }

        // Token is invalid, return an error response
        return response()->json(['error' => 'Invalid token'], 401);
    }
}

