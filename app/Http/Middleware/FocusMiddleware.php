<?php

namespace App\Http\Middleware;

use App\Models\Company\Company;
use App\Models\Company\ConfigMeta;
use Closure;

class FocusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset(auth()->valid)) {
            $company = Company::find(auth()->user()->ins);
            if ($company) {
                config(['core' => $company]);
                $meta = ConfigMeta::withoutGlobalScopes()
                    ->where(['feature_id' => 2, 'ins' => $company->id])
                    ->first();
                if ($meta) config(['currency' => $meta->currency]);
            }
            config(['app.timezone' => $company->zone]);
            date_default_timezone_set($company->zone);
        }

        return $next($request);
    }
}
