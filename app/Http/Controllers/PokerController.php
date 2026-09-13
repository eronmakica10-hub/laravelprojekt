<?php

namespace App\Http\Controllers;

use App\Services\Poker\BotPlayer;
use App\Services\Poker\TexasHoldem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PokerController extends Controller
{
    private function balance()
    {
        if (session('user_id')) {
            $u = \App\Services\UserStore::findById(session('user_id'));
            if ($u) {
                session(['balance' => $u['balance']]);
                return $u['balance'];
            }
        }
        if (!session()->has('balance')) session(['balance' => 0]);
        return session('balance');
    }

    private function setBalance($v)
    {
        $v = max(0, round($v, 2));
        session(['balance' => $v]);
        if (session('user_id')) {
            \App\Services\UserStore::updateBalance(session('user_id'), $v);
        }
    }

    public function index()
    {
        return view('poker', ['balance' => $this->balance()]);
    }

    private static function blindsFor(int $buyIn): array
    {
        return match (true) {
            $buyIn >= 1000 => [10, 20],
            $buyIn >= 500 => [5, 10],
            default => [1, 2],
        };
    }

    // ============ OFFLINE (me bota, në session) ============

    /** Sekondat e mbetura offline (40s për radhë, si online). */
    private function offSecs(): ?int
    {
        $exp = session('poker_off_expires');
        if (!$exp) return null;
        return (int) max(0, now()->diffInSeconds($exp, false));
    }

    private function offTouch(): void
    {
        session(['poker_off_expires' => now()->addSeconds(self::TURN_SECS)->toDateTimeString()]);
    }

    /** Auto-veprim offline kur skadon koha (40s): check nëse s'ka bast, ndryshe fold. Kthen true nëse veproi. */
    private function offEnforce(array &$g): bool
    {
        if (($g['street'] ?? '') === 'done' || ($g['street'] ?? '') === 'lobby') return false;
        if (($g['actor'] ?? null) !== 0) return false; // botat veprojnë menjëherë
        $exp = session('poker_off_expires');
        if (!$exp || now()->lt($exp)) return false;
        $toCall = $g['currentBet'] - ($g['players'][0]['bet'] ?? 0);
        TexasHoldem::act($g, 0, $toCall <= 0 ? 'check' : 'fold');
        self::botsAct($g);
        $this->offTouch();
        return true;
    }

    private function offView(array $g): array
    {
        $secs = ($g['actor'] ?? null) === 0 ? $this->offSecs() : null;
        return ['seated' => true, 'balance' => $this->balance(), 'turnSecs' => $secs] + TexasHoldem::viewFor($g, 0);
    }

    public function offState()
    {
        $g = session('poker_off');
        if (!$g) return response()->json(['seated' => false]);
        $this->offEnforce($g);
        session(['poker_off' => $g]);
        return response()->json($this->offView($g));
    }

    public function offSit(Request $r)
    {
        $buyIn = (int)$r->input('buyIn', 500);
        if (!in_array($buyIn, [100, 500, 1000])) $buyIn = 500;
        $nBots = max(1, min(5, (int)$r->input('bots', 4)));
        $balance = $this->balance();
        if ($buyIn > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $this->setBalance($balance - $buyIn);
        [$sb, $bb] = self::blindsFor($buyIn);
        $names = array_merge([session('user_name', 'Ti')], BotPlayer::botNames($nBots));
        $isBot = array_merge([false], array_fill(0, $nBots, true));
        $g = TexasHoldem::newTable($names, $buyIn, $sb, $bb, $isBot);
        foreach ($g['players'] as $i => &$pl) {
            if ($pl['isBot']) $pl['style'] = BotPlayer::randomStyle();
        }
        unset($pl);
        TexasHoldem::startHand($g);
        self::botsAct($g);
        session(['poker_off' => $g]);
        $this->offTouch();
        return response()->json($this->offView($g));
    }

    public function offAction(Request $r)
    {
        $g = session('poker_off');
        if (!$g) return response()->json(['error' => 'Ulu në tavolinë së pari.'], 422);
        if (($g['street'] ?? '') === 'done') return response()->json(['error' => 'Dora mbaroi — shtyp “Dora tjetër”.'], 422);
        if ($this->offEnforce($g)) {
            session(['poker_off' => $g]);
            return response()->json(['timeout' => true, 'message' => 'Koha mbaroi (40s) — u bë auto-veprim.'] + $this->offView($g));
        }
        $res = TexasHoldem::act($g, 0, $r->input('action', ''), (int)$r->input('amount', 0));
        if (isset($res['error'])) return response()->json(['error' => $res['error']], 422);
        self::botsAct($g);
        session(['poker_off' => $g]);
        $this->offTouch();
        return response()->json($this->offView($g));
    }

    public function offNext()
    {
        $g = session('poker_off');
        if (!$g) return response()->json(['error' => 'Ulu në tavolinë së pari.'], 422);
        if (($g['street'] ?? '') !== 'done') return response()->json(['error' => 'Dora është aktive.'], 422);
        if (($g['players'][0]['stack'] ?? 0) < $g['bb']) {
            return response()->json(['needRebuy' => true, 'stack' => $g['players'][0]['stack'] ?? 0, 'balance' => $this->balance()]);
        }
        TexasHoldem::startHand($g);
        self::botsAct($g);
        session(['poker_off' => $g]);
        $this->offTouch();
        return response()->json($this->offView($g));
    }

    public function offRebuy(Request $r)
    {
        $g = session('poker_off');
        if (!$g) return response()->json(['error' => 'Ulu në tavolinë së pari.'], 422);
        $buyIn = max(100, $g['buyIn'] ?? 500);
        $balance = $this->balance();
        if ($buyIn > $balance) return response()->json(['error' => 'Balancë e pamjaftueshme për rebuy.'], 422);
        $this->setBalance($balance - $buyIn);
        $g['players'][0]['stack'] += $buyIn;
        $g['players'][0]['sittingOut'] = false;
        TexasHoldem::startHand($g);
        self::botsAct($g);
        session(['poker_off' => $g]);
        $this->offTouch();
        return response()->json($this->offView($g));
    }

    public function offLeave()
    {
        $g = session('poker_off');
        if ($g) {
            $this->setBalance($this->balance() + ($g['players'][0]['stack'] ?? 0));
            session()->forget(['poker_off', 'poker_off_expires']);
        }
        return response()->json(['seated' => false, 'balance' => $this->balance()]);
    }

    private static function botsAct(array &$g): void
    {
        for ($guard = 0; $guard < 60; $guard++) {
            if (($g['street'] ?? '') === 'done') return;
            $a = $g['actor'];
            if ($a === null || !($g['players'][$a]['isBot'] ?? false)) return;
            [$act, $amt] = BotPlayer::decide($g, $a);
            $res = TexasHoldem::act($g, $a, $act, $amt);
            if (isset($res['error'])) {
                $toCall = $g['currentBet'] - $g['players'][$a]['bet'];
                TexasHoldem::act($g, $a, $toCall <= 0 ? 'check' : 'fold');
            }
        }
    }

    // ============ ONLINE (dhoma me kod, polling) ============

    private const ROOM_TTL = 7200;
    private const TURN_SECS = 40;
    private const MAX_SEATS = 6;

    private static function roomKey(string $code): string
    {
        return 'poker-room-' . strtoupper($code);
    }

    private static function loadRoom(string $code): ?array
    {
        return Cache::get(self::roomKey($code));
    }

    private static function saveRoom(array $room): void
    {
        Cache::put(self::roomKey($room['code']), $room, self::ROOM_TTL);
    }

    private static function seatOf(array $room, string $token): ?int
    {
        $pid = $room['tokens'][$token] ?? null;
        if (!$pid) return null;
        foreach ($room['game']['players'] as $i => $p) {
            if (($p['pid'] ?? null) === $pid) return $i;
        }
        return null;
    }

    public function roomCreate(Request $r)
    {
        $buyIn = (int)$r->input('buyIn', 500);
        if (!in_array($buyIn, [100, 500, 1000])) $buyIn = 500;
        $maxSeats = max(2, min(self::MAX_SEATS, (int)$r->input('maxSeats', self::MAX_SEATS)));
        $name = trim((string)$r->input('name', '')) ?: session('user_name', 'Lojtari');
        $name = mb_substr($name, 0, 16);
        $balance = $this->balance();
        if ($buyIn > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $this->setBalance($balance - $buyIn);
        [$sb, $bb] = self::blindsFor($buyIn);
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 4));
        } while (Cache::has(self::roomKey($code)));
        $token = bin2hex(random_bytes(8));
        $pid = uniqid('p');
        $game = TexasHoldem::newTable([$name], $buyIn, $sb, $bb, [false]);
        $game['players'][0]['pid'] = $pid;
        // bota direkt në krijim (deri në maxSeats-1)
        $nBots = max(0, min($maxSeats - 1, (int)$r->input('bots', 0)));
        $used = [$name];
        for ($b = 0; $b < $nBots; $b++) {
            $bname = null;
            foreach (BotPlayer::botNames(10) as $bn) {
                if (!in_array($bn, $used)) { $bname = $bn; break; }
            }
            $bname = $bname ?? ('Bot' . mt_rand(10, 99));
            $used[] = $bname;
            $game['players'][] = [
                'name' => $bname, 'isBot' => true, 'pid' => uniqid('b'),
                'style' => BotPlayer::randomStyle(),
                'stack' => $buyIn, 'bet' => 0, 'folded' => false, 'allin' => false,
                'cards' => [], 'sittingOut' => false,
            ];
        }
        $room = [
            'code' => $code, 'buyIn' => $buyIn, 'maxSeats' => $maxSeats, 'host' => $pid,
            'game' => $game, 'tokens' => [$token => $pid],
            'turnExpires' => null, 'created' => now()->toDateTimeString(),
        ];
        self::saveRoom($room);
        return response()->json(['code' => $code, 'token' => $token, 'balance' => $this->balance()] + self::roomView($room, $token));
    }

    public function roomJoin(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $room = self::loadRoom($code);
        if (!$room) return response()->json(['error' => 'Dhoma nuk u gjet. Kontrollo kodin.'], 404);
        if (count($room['game']['players']) >= ($room['maxSeats'] ?? self::MAX_SEATS)) {
            return response()->json(['error' => 'Dhoma është plot (' . ($room['maxSeats'] ?? self::MAX_SEATS) . ' vende).'], 422);
        }
        if (($room['game']['street'] ?? 'lobby') !== 'lobby' && ($room['game']['street'] ?? '') !== 'done') {
            // lejohet hyrja si spektator i ulur jashtë deri në dorën tjetër
        }
        $name = trim((string)$r->input('name', '')) ?: session('user_name', 'Lojtari');
        $name = mb_substr($name, 0, 16);
        foreach ($room['game']['players'] as $p) {
            if (strtolower($p['name']) === strtolower($name)) {
                return response()->json(['error' => 'Ky emër është zënë në dhomë.'], 422);
            }
        }
        $buyIn = $room['buyIn'];
        $balance = $this->balance();
        if ($buyIn > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme për buy-in ' . $buyIn . '€.'], 422);
        }
        $this->setBalance($balance - $buyIn);
        $token = bin2hex(random_bytes(8));
        $pid = uniqid('p');
        $room['game']['players'][] = [
            'name' => $name, 'isBot' => false, 'pid' => $pid,
            'stack' => $buyIn, 'bet' => 0, 'folded' => false, 'allin' => false,
            'cards' => [], 'sittingOut' => ($room['game']['street'] ?? 'lobby') !== 'lobby' && ($room['game']['street'] ?? '') !== 'done',
        ];
        $room['tokens'][$token] = $pid;
        self::saveRoom($room);
        return response()->json(['code' => $code, 'token' => $token, 'balance' => $this->balance()] + self::roomView($room, $token));
    }

    public function roomAddBot(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $token = (string)$r->input('token', '');
        return $this->locked($code, function (&$room) use ($token) {
            $seat = self::seatOf($room, $token);
            if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
            if (($room['game']['players'][$seat]['pid'] ?? null) !== $room['host']) {
                return response()->json(['error' => 'Vetëm hosti shton bota.'], 403);
            }
            if (count($room['game']['players']) >= ($room['maxSeats'] ?? self::MAX_SEATS)) {
                return response()->json(['error' => 'Dhoma është plot.'], 422);
            }
            if (($room['game']['street'] ?? 'lobby') !== 'lobby' && ($room['game']['street'] ?? '') !== 'done') {
                return response()->json(['error' => 'Prit fundin e dorës.'], 422);
            }
            $used = array_column($room['game']['players'], 'name');
            $name = null;
            foreach (BotPlayer::botNames(10) as $n) {
                if (!in_array($n, $used)) { $name = $n; break; }
            }
            $room['game']['players'][] = [
                'name' => $name ?? ('Bot' . mt_rand(10, 99)), 'isBot' => true, 'pid' => uniqid('b'),
                'style' => BotPlayer::randomStyle(),
                'stack' => $room['buyIn'], 'bet' => 0, 'folded' => false, 'allin' => false,
                'cards' => [], 'sittingOut' => false,
            ];
            self::saveRoom($room);
            return response()->json(self::roomView($room, $token));
        });
    }

    public function roomStart(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $token = (string)$r->input('token', '');
        return $this->locked($code, function (&$room) use ($token) {
            $seat = self::seatOf($room, $token);
            if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
            if (($room['game']['players'][$seat]['pid'] ?? null) !== $room['host']) {
                return response()->json(['error' => 'Vetëm hosti e nis lojën.'], 403);
            }
            self::prune($room);
            $active = array_filter($room['game']['players'], fn ($p) => !$p['sittingOut'] && $p['stack'] > 0);
            if (count($active) < 2) return response()->json(['error' => 'Duhen së paku 2 lojtarë.'], 422);
            TexasHoldem::startHand($room['game']);
            $room['turnExpires'] = now()->addSeconds(self::TURN_SECS)->toDateTimeString();
            self::botsActRoom($room);
            self::saveRoom($room);
            return response()->json(self::roomView($room, $token));
        });
    }

    public function roomNext(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $token = (string)$r->input('token', '');
        return $this->locked($code, function (&$room) use ($token) {
            $seat = self::seatOf($room, $token);
            if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
            if (($room['game']['street'] ?? '') !== 'done') return response()->json(['error' => 'Dora është aktive.'], 422);
            if (($room['game']['players'][$seat]['pid'] ?? null) !== $room['host']) {
                return response()->json(['error' => 'Vetëm hosti e nis dorën tjetër.'], 403);
            }
            self::prune($room);
            if (!TexasHoldem::startHand($room['game'])) {
                self::saveRoom($room);
                return response()->json(['error' => 'Duhen së paku 2 lojtarë me chipsa.'] + self::roomView($room, $token), 422);
            }
            $room['turnExpires'] = now()->addSeconds(self::TURN_SECS)->toDateTimeString();
            self::botsActRoom($room);
            self::saveRoom($room);
            return response()->json(self::roomView($room, $token));
        });
    }

    public function roomAction(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $token = (string)$r->input('token', '');
        return $this->locked($code, function (&$room) use ($r, $token) {
            $seat = self::seatOf($room, $token);
            if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
            self::enforceTimeout($room);
            if (($room['game']['actor'] ?? null) !== $seat) {
                self::saveRoom($room);
                return response()->json(['error' => 'Nuk e ke radhën.'] + self::roomView($room, $token), 422);
            }
            $res = TexasHoldem::act($room['game'], $seat, $r->input('action', ''), (int)$r->input('amount', 0));
            if (isset($res['error'])) return response()->json(['error' => $res['error']] + self::roomView($room, $token), 422);
            $room['turnExpires'] = now()->addSeconds(self::TURN_SECS)->toDateTimeString();
            self::botsActRoom($room);
            self::saveRoom($room);
            return response()->json(self::roomView($room, $token));
        });
    }

    public function roomState(Request $r)
    {
        $code = strtoupper(trim((string)$r->query('code', '')));
        $token = (string)$r->query('token', '');
        $room = self::loadRoom($code);
        if (!$room) return response()->json(['error' => 'Dhoma u mbyll.'], 404);
        $seat = self::seatOf($room, $token);
        if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
        // Poll pa lock bllokues — ndryshe 2 lojtarë që polojnë çdo 1.5s marrin 409 "Provo përsëri".
        // Mundohemi me lock jo-bllokues për timeout/bota; nëse dështon kthejmë view pa save.
        $lock = \Illuminate\Support\Facades\Cache::lock('poker-lock-' . strtoupper($code), 10);
        try {
            if ($lock->get()) {
                try {
                    $fresh = self::loadRoom($code);
                    if ($fresh) {
                        $room = $fresh;
                        self::enforceTimeout($room);
                        self::botsActRoom($room);
                        self::saveRoom($room);
                    }
                } finally {
                    $lock->release();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
        // seat mund të ketë ndryshuar pas prune() — rillogarit
        $seat = self::seatOf($room, $token);
        if ($seat === null) return response()->json(['error' => 'S’je në dhomë.'], 403);
        return response()->json(self::roomView($room, $token));
    }

    public function roomLeave(Request $r)
    {
        $code = strtoupper(trim((string)$r->input('code', '')));
        $token = (string)$r->input('token', '');
        $room = self::loadRoom($code);
        if (!$room) return response()->json(['left' => true, 'balance' => $this->balance()]);
        $seat = self::seatOf($room, $token);
        if ($seat === null) return response()->json(['left' => true, 'balance' => $this->balance()]);
        return $this->locked($code, function (&$room) use ($seat, $token) {
            $p = &$room['game']['players'][$seat];
            // paratë e pakontestuara kthehen; bastet e hedhura mbesin në pot
            $this->setBalance($this->balance() + $p['stack']);
            $wasHost = ($p['pid'] ?? null) === $room['host'];
            $p['folded'] = true;
            $p['sittingOut'] = true;
            $p['stack'] = 0;
            unset($room['tokens'][$token]);
            // hosti kalon te tjetri (edhe te lojtari që pret dorën tjetër)
            if ($wasHost) {
                foreach ($room['game']['players'] as $q) {
                    if (!($q['isBot'] ?? false) && ($q['stack'] ?? 0) > 0) { $room['host'] = $q['pid']; break; }
                }
            }
            if (($room['game']['street'] ?? 'lobby') !== 'lobby' && ($room['game']['street'] ?? '') !== 'done') {
                // dora vazhdon pa të; nëse ishte radha jote, auto-fold
                if (($room['game']['actor'] ?? null) === $seat) {
                    TexasHoldem::act($room['game'], $seat, 'fold');
                    self::botsActRoom($room);
                }
            }
            // fshi dhomën bosh (numërohen edhe ata që presin dorën tjetër — sittingOut me stack>0)
            $humans = array_filter($room['game']['players'], fn ($q) => !($q['isBot'] ?? false) && ($q['stack'] ?? 0) > 0);
            if (empty($humans)) {
                Cache::forget(self::roomKey($room['code']));
                return response()->json(['left' => true, 'balance' => $this->balance()]);
            }
            self::saveRoom($room);
            return response()->json(['left' => true, 'balance' => $this->balance()]);
        });
    }

    private function locked(string $code, callable $fn)
    {
        $lock = Cache::lock('poker-lock-' . strtoupper($code), 10);
        try {
            return $lock->block(5, function () use ($code, $fn) {
                $room = self::loadRoom($code);
                if (!$room) return response()->json(['error' => 'Dhoma u mbyll.'], 404);
                return $fn($room);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['error' => 'Provo përsëri.'], 409);
        }
    }

    /** Hiq lojtarët pa chipsa para dorës së re (ri-indekso). Lojtarët që pritën (sittingOut + stack>0) aktivizohen. */
    private static function prune(array &$room): void
    {
        $keep = [];
        foreach ($room['game']['players'] as $p) {
            if (($p['stack'] ?? 0) <= 0) {
                // pa chipsa / i larguar — fshi tokenat
                foreach ($room['tokens'] as $tok => $pid) {
                    if ($pid === ($p['pid'] ?? null)) unset($room['tokens'][$tok]);
                }
                continue;
            }
            // kush ka stack > 0 kthehet në lojë (edhe nëse hyri në mes të dorës si sittingOut)
            $p['sittingOut'] = false;
            $keep[] = $p;
        }
        $room['game']['players'] = array_values($keep);
        if (($room['game']['button'] ?? 0) >= count($keep)) $room['game']['button'] = 0;
        // hosti duhet të ekzistojë
        $hostOk = false;
        foreach ($keep as $p) {
            if (($p['pid'] ?? null) === $room['host'] && !($p['isBot'] ?? false)) $hostOk = true;
        }
        if (!$hostOk) {
            foreach ($keep as $p) {
                if (!($p['isBot'] ?? false)) { $room['host'] = $p['pid']; break; }
            }
        }
    }

    /** Auto-veprim kur skadon koha (40s). Kthen true nëse ndryshoi. */
    private static function enforceTimeout(array &$room): bool
    {
        $g = &$room['game'];
        if (($g['street'] ?? '') === 'done' || ($g['street'] ?? '') === 'lobby') return false;
        if ($g['actor'] === null) return false;
        if (!$room['turnExpires'] || now()->lt($room['turnExpires'])) return false;
        $a = $g['actor'];
        $p = $g['players'][$a];
        if ($p['isBot'] ?? false) return false; // botat veprojnë menjëherë
        $toCall = $g['currentBet'] - $p['bet'];
        TexasHoldem::act($g, $a, $toCall <= 0 ? 'check' : 'fold');
        $room['turnExpires'] = now()->addSeconds(self::TURN_SECS)->toDateTimeString();
        return true;
    }

    private static function botsActRoom(array &$room): void
    {
        $g = &$room['game'];
        for ($guard = 0; $guard < 60; $guard++) {
            if (($g['street'] ?? '') === 'done' || ($g['street'] ?? '') === 'lobby') return;
            $a = $g['actor'];
            if ($a === null || !($g['players'][$a]['isBot'] ?? false)) {
                if ($a !== null) $room['turnExpires'] = now()->addSeconds(self::TURN_SECS)->toDateTimeString();
                return;
            }
            [$act, $amt] = BotPlayer::decide($g, $a);
            $res = TexasHoldem::act($g, $a, $act, $amt);
            if (isset($res['error'])) {
                $toCall = $g['currentBet'] - $g['players'][$a]['bet'];
                TexasHoldem::act($g, $a, $toCall <= 0 ? 'check' : 'fold');
            }
        }
    }

    private static function roomView(array $room, string $token): array
    {
        $seat = self::seatOf($room, $token);
        $g = $room['game'];
        $seats = [];
        foreach ($g['players'] as $i => $p) {
            $seats[] = ['name' => $p['name'], 'isBot' => $p['isBot'] ?? false,
                'isHost' => ($p['pid'] ?? null) === $room['host'],
                'isYou' => $i === $seat, 'sittingOut' => $p['sittingOut'] ?? false];
        }
        return [
            'room' => [
                'code' => $room['code'], 'buyIn' => $room['buyIn'],
                'max' => $room['maxSeats'] ?? self::MAX_SEATS,
                'isHost' => $seat !== null && (($g['players'][$seat]['pid'] ?? null) === $room['host']),
                'seats' => $seats, 'count' => count($g['players']),
            ],
            'you' => ['seat' => $seat],
            'turnSecs' => $seat !== null && ($g['actor'] ?? null) === $seat && $room['turnExpires']
                ? (int) max(0, now()->diffInSeconds($room['turnExpires'], false)) : null,
            'state' => $seat === null ? null : TexasHoldem::viewFor($g, $seat),
        ];
    }
}
