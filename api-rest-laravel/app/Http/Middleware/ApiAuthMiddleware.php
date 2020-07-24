<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JWTAuth;


class ApiAuthMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $token = $request->header('Authorization');

        $jwtAuth = new JWTAuth();

        $checkToken = $jwtAuth->checktoken($token);

        if ($checkToken) {
            return $next($request);
        } else {
            $data = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'El usuario no está identificado.'
            );
            return response()->json($data, $data['code']);
        }
    }

}
