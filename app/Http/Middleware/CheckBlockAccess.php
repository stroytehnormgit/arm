<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('admin')) {
            return $next($request);
        }

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            $block = $request->input('block');

            if ($block && !$this->userHasAccessToBlock($user, $block)) {
                return redirect()->back()->withErrors([
                    'block' => 'У вас нет доступа к этому блоку. Вы можете работать только с блоком ' . $user->block_rank
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Проверяет, имеет ли пользователь доступ к указанному блоку
     */
    private function userHasAccessToBlock($user, string $block): bool
    {
        if (!$user || !$user->block_rank) {
            return false;
        }

        $userBlockRank = (string) $user->block_rank;

        return str_starts_with($block, $userBlockRank . '.') || $block === $userBlockRank;
    }
}

