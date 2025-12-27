<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Core\DemoService;

class RestrictDemoMode
{
    protected $demoService;

    public function __construct(DemoService $demoService)
    {
        $this->demoService = $demoService;
    }

    public function handle(Request $request, Closure $next, string $feature)
    {
        if (config('demo.enabled') 
                && $this->demoService->isRestrictedRoute($request, $feature) 
                && $this->demoService->isFeatureEnabled($feature)) {

            $restrictedKeys = $this->demoService->getRestrictedKeys($feature);

            if (empty($restrictedKeys)) return back()->withNotify([['error', $this->demoService->getGlobalMessage()]]);

            $originalData       = $request->all();
            $filteredData       = $this->demoService->filterRestrictedKeys($originalData, $restrictedKeys);
            $hasRestrictedKeys  = $this->demoService->hasRestrictedKeys($originalData, $restrictedKeys);

            $request->merge($filteredData);
            $response = $next($request);

            if ($hasRestrictedKeys 
                && $response->getStatusCode() === 200) 
                return $this->demoService->appendGlobalMessage($response, $request);

            return $response;
        }

        return $next($request);
    }
}