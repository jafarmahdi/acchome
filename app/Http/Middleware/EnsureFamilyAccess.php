<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFamilyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->family_id) {
            abort(403, 'You do not belong to any family group.');
        }

        // Check route model bindings for family_id ownership
        foreach ($request->route()->parameters() as $parameter) {
            if (is_object($parameter) && property_exists($parameter, 'family_id')) {
                if ((int) $parameter->family_id !== (int) $user->family_id) {
                    abort(403, 'You do not have access to this resource.');
                }
            }
        }

        // Inject family_id into the request for convenient access
        $request->merge(['current_family_id' => $user->family_id]);

        return $next($request);
    }
}
