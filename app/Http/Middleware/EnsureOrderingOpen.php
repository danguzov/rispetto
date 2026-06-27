<?php

namespace App\Http\Middleware;

use App\Services\Ordering\RestaurantSettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderingOpen
{
    public function __construct(
        private readonly RestaurantSettingsService $settings,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('ordering.enabled', true)) {
            return $this->reject($request, 'Online poručivanje trenutno nije dostupno.');
        }

        if (! $this->settings->acceptingOrders()) {
            return $this->reject($request, $this->settings->closedMessage());
        }

        return $next($request);
    }

    private function reject(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        return back()
            ->withInput()
            ->withErrors(['ordering' => $message]);
    }
}
