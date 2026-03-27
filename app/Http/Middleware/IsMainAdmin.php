<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsMainAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin && $admin->isMainAdmin()) {
            return $next($request);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'K této části administrace mají přístup pouze hlavní administrátoři.');
    }
}
