<?php

namespace App\Http\Controllers;

use App\Services\UserStore;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public const AVATARS = ['🦅','🐺','🦁','🐯','🦊','🐼','🐸','🐵','🦄','🐝','🦈','🐳','🐲','👽','🤖','👑'];

    private function me()
    {
        $id = session('user_id');
        if (!$id) return null;
        return UserStore::findById($id);
    }

    public function show()
    {
        $u = $this->me();
        if (!$u) return response()->json(['error' => 'S’je i kyçur.'], 401);
        $txs = $u['transactions'] ?? [];
        return response()->json([
            'name' => $u['name'],
            'email' => $u['email'],
            'avatar' => $u['avatar'] ?? '🦅',
            'balance' => $u['balance'],
            'is_guest' => (bool)($u['is_guest'] ?? false),
            'created' => $u['created_at'] ?? null,
            'tx_count' => count($txs),
            'avatars' => self::AVATARS,
        ]);
    }

    public function update(Request $r)
    {
        $u = $this->me();
        if (!$u) return response()->json(['error' => 'S’je i kyçur.'], 401);
        $data = $r->validate([
            'name' => 'required|string|min:3|max:30',
            'avatar' => 'required|string',
        ], [
            'name.required' => 'Shkruaj emrin.',
            'name.min' => 'Emri së paku 3 shkronja.',
            'name.max' => 'Emri maksimumi 30 shkronja.',
        ]);
        if (!in_array($data['avatar'], self::AVATARS)) {
            return response()->json(['error' => 'Avatari i pavlefshëm.'], 422);
        }
        $u = UserStore::update($u['id'], ['name' => $data['name'], 'avatar' => $data['avatar']]);
        session(['user_name' => $u['name'], 'user_avatar' => $u['avatar']]);
        return response()->json(['name' => $u['name'], 'avatar' => $u['avatar'], 'message' => 'Profili u përditësua! ✅']);
    }

    // guests e kthejnë llogarinë në të plotë (emër + email + fjalëkalim)
    public function claim(Request $r)
    {
        $u = $this->me();
        if (!$u) return response()->json(['error' => 'S’je i kyçur.'], 401);
        if (!($u['is_guest'] ?? false)) return response()->json(['error' => 'Llogaria jote është tashmë e plotë.'], 422);
        $data = $r->validate([
            'name' => 'required|string|min:3|max:30',
            'email' => 'required|email|max:100',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.min' => 'Emri së paku 3 shkronja.',
            'email.email' => 'Email i pavlefshëm.',
            'password.min' => 'Fjalëkalimi së paku 6 karaktere.',
            'password.confirmed' => 'Fjalëkalimet nuk përputhen.',
        ]);
        if (UserStore::findByEmail($data['email'])) {
            return response()->json(['error' => 'Ky email është i regjistruar. Përdor login.'], 422);
        }
        $u = UserStore::update($u['id'], [
            'name' => $data['name'], 'email' => $data['email'],
            'password' => $data['password'], 'is_guest' => false,
        ]);
        session(['user_name' => $u['name']]);
        session()->forget('is_guest');
        return response()->json(['name' => $u['name'], 'message' => 'Llogaria u ruajt! Tani mundesh me u kyçë kudo. 🎉']);
    }

    public function password(Request $r)
    {
        $u = $this->me();
        if (!$u) return response()->json(['error' => 'S’je i kyçur.'], 401);
        if ($u['is_guest'] ?? false) return response()->json(['error' => 'Së pari ruaje llogarinë (tab Ruaje).'], 422);
        $data = $r->validate([
            'current' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.min' => 'Fjalëkalimi i ri së paku 6 karaktere.',
            'password.confirmed' => 'Fjalëkalimet nuk përputhen.',
        ]);
        if (!password_verify($data['current'], $u['password'])) {
            return response()->json(['error' => 'Fjalëkalimi aktual është i gabuar.'], 422);
        }
        UserStore::update($u['id'], ['password' => $data['password']]);
        return response()->json(['message' => 'Fjalëkalimi u ndryshua! 🔒']);
    }
}
