<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyId
{
    public function handle(Request $request, Closure $next, $companyId)
    {
        $user = $request->user();

        if (! $user || (int) $user->company_id !== (int) $companyId) {
            // Of: abort(403);
            return redirect()->route('support.dashboard');
        }

        return $next($request);
    }
}
