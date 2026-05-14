<?php

namespace App\Http\Middleware;

use Diglactic\Breadcrumbs\Breadcrumbs;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $isHeimdall = env('VITE_USER_CONNECTION', 'mariadb') === 'heimdall';
        $isProduction = config('app.env') === 'production' || config('app.env') === 'prod';
        $isDev = !$isProduction;
        // $notifications = Auth::user()->notifications;
        $arr = array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'appVersion' => config('app.version'),
            'appEnv' => config('app.env'),
            'useSidebar' => config('app.use_sidebar'),
            // 'notifications' => $notifications,
            'isProduction' => $isProduction,
            'isHeimdall' => $isHeimdall,
            'breadcrumbs' => Breadcrumbs::exists($request->route()->getName()) ? Breadcrumbs::generate($request->route()->getName()) : [],
        ]);

        if ($isHeimdall) {
            $arr = array_merge($arr, [
                'heimdall.token' => Session::get('heimdallToken'),
                'heimdall.perfis' => Session::get('perfis', []),
                'heimdall.menus' => Session::get('menus', []),
                'heimdall.permissoes' => Session::get('permissoes', []),
            ]);
        }

        if ($isDev) {
            $arr = array_merge($arr, [
                'laravelVersion' => Application::VERSION,
                'phpVersion' => PHP_VERSION,
            ]);
        }

        return $arr;
    }
}
