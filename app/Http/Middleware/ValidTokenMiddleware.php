<?php

namespace App\Http\Middleware;

use App\Models\bill\Bill;
use App\Models\Company\ConfigMeta;
use App\Models\invoice\Invoice;
use App\Models\order\Order;
use App\Models\purchaseorder\Purchaseorder;
use App\Models\quote\Quote;
use App\Models\djc\Djc;
use App\Models\rjc\Rjc;
use Closure;
use Illuminate\Support\Facades\App;

class ValidTokenMiddleware
{
    /*
     _ Handle an incoming request.
     _
     _ @param  \Illuminate\Http\Request  $request
     _ @param  \Closure  $next
     _ @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (App::environment('production')) error_reporting(0);        
        if (!isset($request->type)) abort(403, 'Access denied');
        
        switch ($request->type) {
            case 1:
                $invoice = Invoice::withoutGlobalScopes()->find($request->id);
                break;
            case 3:
                $invoice = Bill::withoutGlobalScopes()->find($request->id);
                break;
            case 4:
                $invoice = Quote::withoutGlobalScopes()->find($request->id);
                break;
            case 5:
                $invoice = Order::withoutGlobalScopes()->find($request->id);
                break;
            case 9:
                $invoice = Purchaseorder::withoutGlobalScopes()->find($request->id);
                break;
            case 10:
                $invoice = Djc::withoutGlobalScopes()->find($request->id);
                break;
            case 11:
                $invoice = Rjc::withoutGlobalScopes()->find($request->id);
                break;
        }

        if (isset($invoice->ins)) {
            session(['theme' => ConfigMeta::withoutGlobalScopes()
                ->where(['ins' => $invoice->ins, 'feature_id' => 15])
                ->first('value1')->value1
            ]);
        }
        
        return $next($request);
    }
}
