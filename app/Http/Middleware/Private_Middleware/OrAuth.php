<?php

namespace App\Http\Middleware\Private_Middleware;

use Closure;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Throw_;

class OrAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next,$rule)
    {
        try {
            $user = auth("userapi")->user();
            if(in_array($user->rule,explode('|', $rule)))
                return $next($request);
            else
                throw new \Exception("Unauthenticated.");
        }catch (\Exception $exception){
            return response()->json(["message"=>$exception->getMessage()]);
        }
    }
}
