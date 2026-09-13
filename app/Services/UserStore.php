<?php

namespace App\Services;

class UserStore
{
    private static function path()
    {
        return storage_path('app/users.json');
    }

    public static function all()
    {
        $p = self::path();
        if (!file_exists($p)) return [];
        $data = json_decode(file_get_contents($p), true);
        return is_array($data) ? $data : [];
    }

    private static function save($users)
    {
        file_put_contents(self::path(), json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public static function findByEmail($email)
    {
        foreach (self::all() as $u) {
            if (strtolower($u['email']) === strtolower($email)) return $u;
        }
        return null;
    }

    public static function findById($id)
    {
        foreach (self::all() as $u) {
            if (($u['id'] ?? null) == $id) return $u;
        }
        return null;
    }

    public static function create($name, $email, $password, $balance = 0, $extra = [])
    {
        $users = self::all();
        $user = [
            'id' => count($users) + 1,
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'balance' => round($balance, 2),
            'avatar' => $extra['avatar'] ?? '🦅',
            'is_guest' => $extra['is_guest'] ?? false,
            'created_at' => now()->toDateTimeString(),
        ];
        $users[] = $user;
        self::save($users);
        return $user;
    }

    public static function update($id, array $data)
    {
        $users = self::all();
        foreach ($users as &$u) {
            if ($u['id'] == $id) {
                foreach (['name', 'email', 'avatar', 'is_guest'] as $k) {
                    if (array_key_exists($k, $data)) $u[$k] = $data[$k];
                }
                if (isset($data['password'])) $u['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
                self::save($users);
                return $u;
            }
        }
        return null;
    }

    public static function updateBalance($id, $balance)
    {
        $users = self::all();
        foreach ($users as &$u) {
            if ($u['id'] == $id) {
                $u['balance'] = max(0, round($balance, 2));
                self::save($users);
                return $u['balance'];
            }
        }
        return null;
    }

    public static function addTransaction($id, $type, $amount, $note = '')
    {
        $users = self::all();
        foreach ($users as &$u) {
            if ($u['id'] == $id) {
                $txs = $u['transactions'] ?? [];
                $txs[] = [
                    'id' => count($txs) + 1,
                    'type' => $type, // deposit | withdraw
                    'amount' => round($amount, 2),
                    'balance_after' => $u['balance'],
                    'note' => $note,
                    'date' => now()->format('d.m.Y H:i'),
                ];
                $u['transactions'] = $txs;
                self::save($users);
                return end($txs);
            }
        }
        return null;
    }

    public static function transactions($id)
    {
        $u = self::findById($id);
        return array_reverse($u['transactions'] ?? []);
    }

    public static function current()
    {
        $id = session('user_id');
        if (!$id) return null;
        return self::findById($id);
    }
}
