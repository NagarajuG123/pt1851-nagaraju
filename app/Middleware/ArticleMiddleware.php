<?php

namespace App\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class ArticleMiddleware
{
    public function handle($request, Closure $next, $resource = null)
    {
        $type = empty($request->route()[2]['type']) === false ? $request->route()[2]['type'] : $resource;
        if ($type) {
	        $request->attributes->add(["type" => $type,]);
        	return $next($request);
        }
    }
}