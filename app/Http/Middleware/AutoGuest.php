<?php

namespace App\Http\Middleware;

use App\Services\UserStore;
use Closure;
use Illuminate\Http\Request;

/**
 * Si Stake: sapo hapet faqja (sesion i ri), krijohet automatikisht
 * një llogari guest me bonus mirëseardhjeje — pa formularë.
 */
class AutoGuest
{
    private const BONUS = 100;

    public function handle(Request $request, Closure $next)
    {
        // mos ndërhy te login/register/logout
        if ($request->is('login', 'register', 'logout')) {
            return $next($request);
        }

        if (!session('user_id') && !session('seen')) {
            $n = mt_rand(1000, 9999);
            $avatars = ['🦅', '🐺', '🦁', '🐯', '🦊', '🐼', '🐸', '🦈', '👽', '🤖'];
            $user = UserStore::create(
                'Lojtari' . $n,
                'guest_' . uniqid() . '@guest.local',
                bin2hex(random_bytes(8)),
                self::BONUS,
                ['avatar' => $avatars[array_rand($avatars)], 'is_guest' => true]
            );
            session([
                'user_id' => $user['id'],
                'user_name' => $user['name'],
                'user_avatar' => $user['avatar'],
                'balance' => $user['balance'],
                'is_guest' => true,
            ]);
            session()->now('success', '🎁 Llogaria jote u krijua automatikisht + ' . self::BONUS . '€ bonus mirëseardhjeje! Luaj pa limit 🎰');
        }
        session(['seen' => true]);

        return $next($request);
    }
}
