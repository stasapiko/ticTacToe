<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JwtTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VerifyJwtToken
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $validated = $validator->safe()->only(['token']);

        if (JwtTokenService::validateToken($validated['token'])) {
            return $next($request);
        }

        return response()->json(['error' => 'jwt token is incorrect'], 401);
    }
}
