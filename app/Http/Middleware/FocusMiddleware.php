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
        /**
         * Set core configuraion key value to company instance 
         */
        $company = Company::find(auth()->user()->ins);
        config(['core' => $company]);

        $company_zone = $company ? $company->zone : '';
        config(['app.timezone' => $company_zone]);
        date_default_timezone_set($company_zone);

        if ($company) {
            $meta = ConfigMeta::withoutGlobalScopes()
                ->where(['feature_id' => 2, 'ins' => $company->id])
                ->first();

            config(['currency' => $meta ? $meta->currency : '']);
        } 
               
        return $next($request);
    }
}
