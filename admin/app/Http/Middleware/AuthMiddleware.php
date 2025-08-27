<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Menu;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allow_default_routes=[
            'dashboard.read',
            'profile-user.read'
        ];
        
        if (!auth()->check()) {
            Toastr::error('You are not logged in!');
            return redirect()->route('login');
        }

        $sidbarMenuData = Menu::with([
            'children' => function ($query) {
                $query->with('children');
            }
        ])->where('parent_id', null)->get()->toArray();


        if (auth()->user()->role_id != 1) {

            if (auth()->user()->role_id == null) {

                if (!in_array(Route::currentRouteName(), $allow_default_routes)) {
                    Toastr::error('Permission denied!');
                    return redirect()->back();
                }

            } else {

                if (!in_array(Route::currentRouteName(), $allow_default_routes)) {
                    
                    $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                        ->where('route', Route::currentRouteName())->exists();

                    if (!$hasPermission) {

                        Toastr::error('Permission denied!');
                        return redirect()->back();
                    }
                }
            }

            // Remove menu if user doesn't have permission
            if (!empty($sidbarMenuData)) {
                foreach ($sidbarMenuData as $key => &$menu) {
                    // If menu has children
                    if (!empty($menu['children'])) {
                        foreach ($menu['children'] as $key1 => &$child) {
                            // If child has sub-children
                            if (!empty($child['children'])) {
                                foreach ($child['children'] as $key2 => $subChild) {
                                    $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                                        ->where('slug', $subChild['slug'])
                                        ->where('action', 'read')
                                        ->exists();

                                    if (!$hasPermission) {
                                        unset($child['children'][$key2]);
                                    }
                                }

                                // Remove child if all sub-children are gone
                                if (empty($child['children'])) {
                                    unset($menu['children'][$key1]);
                                }
                            } else {
                                $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                                    ->where('slug', $child['slug'])
                                    ->where('action', 'read')
                                    ->exists();

                                if (!$hasPermission) {
                                    unset($menu['children'][$key1]);
                                }
                            }
                        }

                        // Remove menu if all children are gone
                        if (empty($menu['children'])) {
                            unset($sidbarMenuData[$key]);
                        }
                    } else {
                        $hasPermission = Permission::where('role_id', auth()->user()->role_id)
                            ->where('slug', $menu['slug'])
                            ->where('action', 'read')
                            ->exists();

                        if (!$hasPermission) {
                            unset($sidbarMenuData[$key]);
                        }
                    }
                }
                unset($menu, $child); // break references
            }

        }


        view()->share('sidbarMenuData', $sidbarMenuData);
        return $next($request);
    }
}
