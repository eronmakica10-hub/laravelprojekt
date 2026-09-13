<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CasinoController extends Controller
{
    private function balance()
    {
        // nëse user është i loguar, balanca ruhet në llogarinë e tij
        if (session('user_id')) {
            $u = \App\Services\UserStore::findById(session('user_id'));
            if ($u) {
                session(['balance' => $u['balance']]);
                return $u['balance'];
            }
        }
        if (!session()->has('balance')) {
            session(['balance' => 0]);
        }
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

    // ---------- PAGES ----------
    public function home()
    {
        $balance = $this->balance();
        $jackpot = 125000 + (int)(now()->timestamp % 50000) + rand(0, 900);
        return view('home', compact('balance', 'jackpot'));
    }

    // SLOTS — video-slot 5x3, 5 linja, simbole me peshë (RTP ~95%)
    private const SLOT_THEMES = [
        'gold' => [
            'name' => 'Slotet e Artë',
            'symbols' => ['🍒', '🍋', '🔔', '⭐', '💎', '7️⃣'],
            'weights' => [26, 20, 14, 10, 6, 4],
            'pay' => ['7️⃣' => 24, '💎' => 14, '⭐' => 9, '🔔' => 7, '🍋' => 5, '🍒' => 3.5],
        ],
        'fruits' => [
            'name' => 'Frutat Tropicale',
            'symbols' => ['🍒', '🍋', '🍊', '🍉', '🍇', '🔔', '💎', '7️⃣'],
            'weights' => [30, 24, 20, 16, 12, 9, 6, 3],
            'pay' => ['7️⃣' => 92, '💎' => 54, '🍇' => 35, '🍉' => 27, '🍊' => 19, '🔔' => 16, '🍋' => 11, '🍒' => 11],
        ],
        'egypt' => [
            'name' => 'Thesari i Egjiptit',
            'symbols' => ['🏺', '🐪', '🦂', '👁️', '🐍', '👑', '☀️', '💎'],
            'weights' => [30, 24, 20, 16, 12, 9, 6, 3],
            'pay' => ['💎' => 92, '☀️' => 54, '👑' => 35, '🐍' => 27, '👁️' => 19, '🦂' => 16, '🐪' => 11, '🏺' => 11],
        ],
        'deluxe777' => [
            'name' => 'Super 777 Deluxe',
            'symbols' => ['🍒', '🍋', '🔔', '⭐', '💎', '7️⃣'],
            'weights' => [26, 20, 14, 10, 6, 4],
            'pay' => ['7️⃣' => 24, '💎' => 14, '⭐' => 9, '🔔' => 7, '🍋' => 5, '🍒' => 3.5],
        ],
    ];

    // 5 linjat paguese (rreshti për çdo rrotull): 3 horizontale + V + Λ
    private const SLOT_LINES = [[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2]];

    private function slotDraw(array $symbols, array $weights): string
    {
        $total = array_sum($weights);
        $r = mt_rand(1, $total);
        $acc = 0;
        foreach ($symbols as $i => $s) {
            $acc += $weights[$i] ?? 1;
            if ($r <= $acc) return $s;
        }
        return $symbols[0];
    }

    public function slots()
    {
        return view('slots', ['balance' => $this->balance(), 'themes' => self::SLOT_THEMES]);
    }

    public function roulette()
    {
        return view('roulette', ['balance' => $this->balance()]);
    }

    public function blackjack()
    {
        return view('blackjack', ['balance' => $this->balance()]);
    }

    public function dice()
    {
        return view('dice', ['balance' => $this->balance()]);
    }

    public function crash()
    {
        return view('crash', ['balance' => $this->balance()]);
    }

    public function mines()
    {
        return view('mines', ['balance' => $this->balance()]);
    }

    public function hilo()
    {
        return view('hilo', ['balance' => $this->balance()]);
    }

    public function sports()
    {
        $offer = \App\Services\MatchOffer::today();
        $tickets = session('tickets', []);
        return view('sports', [
            'balance' => $this->balance(),
            'matches' => $offer['matches'],
            'tickets' => $tickets,
            'offerDate' => $offer['offerDate'],
            'offerSource' => $offer['source'],
            'offerProvider' => $offer['provider'],
            'offerOddsLive' => $offer['oddsLive'],
        ]);
    }

    private function matches()
    {
        // Oferta zyrtare e ditës: reale kur ka, demo si rezervë
        return \App\Services\MatchOffer::today()['matches'];
    }

    // ---------- API ----------
    public function getBalance()
    {
        return response()->json(['balance' => $this->balance()]);
    }

    // SLOTS — video slot 5x3: grid, 5 linja, 3+ njësoj nga e majta
    public function spinSlots(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $themeKey = $r->input('theme', 'gold');
        $themeKey = isset(self::SLOT_THEMES[$themeKey]) ? $themeKey : 'gold';
        $theme = self::SLOT_THEMES[$themeKey];
        $symbols = $theme['symbols'];
        $weights = $theme['weights'];
        $pay = $theme['pay'];
        // grid 5 kolona x 3 rreshta me simbole me peshë
        $grid = [];
        for ($c = 0; $c < 5; $c++) {
            $grid[$c] = [$this->slotDraw($symbols, $weights), $this->slotDraw($symbols, $weights), $this->slotDraw($symbols, $weights)];
        }
        $lineBet = round($bet / 5, 2);
        $win = 0; $lineWins = [];
        foreach (self::SLOT_LINES as $li => $rows) {
            $run = 1;
            while ($run < 5 && $grid[$run][$rows[$run]] === $grid[0][$rows[0]]) $run++;
            if ($run >= 3) {
                $base = $pay[$grid[0][$rows[0]]] ?? 2;
                $mult = $run === 3 ? $base : ($run === 4 ? $base * 5 : $base * 25);
                $amt = round($lineBet * $mult, 2);
                $win += $amt;
                $lineWins[] = ['line' => $li, 'sym' => $grid[0][$rows[0]], 'count' => $run, 'mult' => $mult, 'amount' => $amt];
            } elseif ($grid[0][$rows[0]] === $grid[1][$rows[1]]) {
                $amt = round($lineBet * 0.4, 2);
                $win += $amt;
                $lineWins[] = ['line' => $li, 'sym' => $grid[0][$rows[0]], 'count' => 2, 'mult' => 0.3, 'amount' => $amt];
            }
        }
        $win = round($win, 2);
        $profit = round($win - $bet, 2);
        $this->setBalance($balance - $bet + $win);
        $isJackpot = $win >= $bet * 10;
        if ($isJackpot) {
            $big = max(array_column($lineWins, 'count'));
            $msg = "JACKPOT! Fitimi +{$win}€!";
        } elseif ($profit > 0) {
            $msg = "Fitore +{$win}€ me " . count($lineWins) . " linja! Fitimi neto +{$profit}€!";
        } elseif ($win > 0) {
            $msg = "Ktheve {$win}€ nga {$bet}€ bast (humbje neto {$profit}€).";
        } else {
            $msg = 'Humbët. Provo përsëri!';
        }
        return response()->json([
            'grid' => $grid, 'lineWins' => $lineWins, 'lines' => count(self::SLOT_LINES),
            'win' => $win, 'profit' => $profit, 'bet' => $bet, 'theme' => $themeKey,
            'balance' => $this->balance(), 'isWin' => $profit > 0,
            'isJackpot' => $isJackpot, 'message' => $msg, 'multiplier' => $bet > 0 ? round($win / $bet, 2) : 0,
        ]);
    }

    // ROULETTE
    public function spinRoulette(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $type = $r->input('type', 'red'); // red, black, green, even, odd, number
        $numberBet = (int)$r->input('number', 0);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $result = rand(0, 36);
        $reds = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
        $isRed = in_array($result, $reds);
        $isBlack = $result !== 0 && !$isRed;
        $isEven = $result !== 0 && $result % 2 === 0;
        $isOdd = $result % 2 === 1;
        $win = 0;
        $won = match ($type) {
            'red' => $isRed, 'black' => $isBlack,
            'even' => $isEven, 'odd' => $isOdd,
            'green' => $result === 0,
            'number' => $result === $numberBet,
            default => false,
        };
        if ($won) {
            $mult = ($type === 'number' || $type === 'green') ? 36 : 2;
            $win = round($bet * $mult, 2);
        }
        $newBalance = $balance - $bet + $win;
        $this->setBalance($newBalance);
        return response()->json([
            'number' => $result,
            'color' => $result === 0 ? 'green' : ($isRed ? 'red' : 'black'),
            'win' => $win, 'profit' => round($win - $bet, 2), 'bet' => $bet, 'won' => $won,
            'balance' => $this->balance(),
        ]);
    }

    // BLACKJACK
    private function cardValue($cards)
    {
        $total = 0; $aces = 0;
        foreach ($cards as $c) {
            $v = $c['v'];
            if ($v === 'A') { $aces++; $total += 11; }
            elseif (in_array($v, ['J','Q','K'])) $total += 10;
            else $total += (int)$v;
        }
        while ($total > 21 && $aces > 0) { $total -= 10; $aces--; }
        return $total;
    }

    private function drawCard()
    {
        $suits = ['♠','♥','♦','♣'];
        $vals = ['A','2','3','4','5','6','7','8','9','10','J','Q','K'];
        return ['v' => $vals[array_rand($vals)], 's' => $suits[array_rand($suits)]];
    }

    public function bjNew(Request $r)
    {
        $bet = (float)$r->input('bet', 25);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $this->setBalance($balance - $bet);
        $player = [$this->drawCard(), $this->drawCard()];
        $dealer = [$this->drawCard(), $this->drawCard()];
        $pv = $this->cardValue($player);
        $game = ['player' => $player, 'dealer' => $dealer, 'bet' => $bet, 'over' => false];
        // natural blackjack?
        if ($pv === 21) {
            $dv = $this->cardValue($dealer);
            if ($dv === 21) {
                $this->setBalance($this->balance() + $bet); // push
                $game['over'] = true;
                session(['bj' => $game]);
                return response()->json(['player' => $player, 'dealer' => $dealer, 'pval' => $pv, 'dval' => $dv, 'balance' => $this->balance(), 'status' => 'push', 'message' => 'Barazim! Të dy Blackjack.']);
            }
            $win = round($bet * 2.5, 2);
            $profit = round($win - $bet, 2);
            $this->setBalance($this->balance() + $win);
            $game['over'] = true;
            session(['bj' => $game]);
            return response()->json(['player' => $player, 'dealer' => $dealer, 'pval' => $pv, 'dval' => $this->cardValue($dealer), 'balance' => $this->balance(), 'status' => 'blackjack', 'win' => $win, 'profit' => $profit, 'message' => "BLACKJACK! Fitimi neto +{$profit}€"]);
        }
        session(['bj' => $game]);
        return response()->json(['player' => $player, 'dealer' => [$dealer[0], ['v' => '?', 's' => '🂠']], 'pval' => $pv, 'balance' => $this->balance(), 'status' => 'playing']);
    }

    public function bjHit()
    {
        $game = session('bj');
        if (!$game || $game['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        $game['player'][] = $this->drawCard();
        $pv = $this->cardValue($game['player']);
        if ($pv > 21) {
            $game['over'] = true; session(['bj' => $game]);
            return response()->json(['player' => $game['player'], 'dealer' => $game['dealer'], 'pval' => $pv, 'dval' => $this->cardValue($game['dealer']), 'balance' => $this->balance(), 'status' => 'bust', 'message' => "Dogjët! ($pv). Humbët {$game['bet']}€"]);
        }
        session(['bj' => $game]);
        return response()->json(['player' => $game['player'], 'pval' => $pv, 'balance' => $this->balance(), 'status' => 'playing']);
    }

    public function bjStand()
    {
        $game = session('bj');
        if (!$game || $game['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        $game['over'] = true;
        $pv = $this->cardValue($game['player']);
        $dv = $this->cardValue($game['dealer']);
        while ($dv < 17) { $game['dealer'][] = $this->drawCard(); $dv = $this->cardValue($game['dealer']); }
        session(['bj' => $game]);
        $bet = $game['bet'];
        if ($dv > 21 || $pv > $dv) {
            $win = $bet * 2;
            $profit = round($win - $bet, 2);
            $this->setBalance($this->balance() + $win);
            return response()->json(['player' => $game['player'], 'dealer' => $game['dealer'], 'pval' => $pv, 'dval' => $dv, 'balance' => $this->balance(), 'status' => 'win', 'win' => $win, 'profit' => $profit, 'message' => "Fituat! $pv vs $dv. Fitimi neto +{$profit}€"]);
        } elseif ($pv === $dv) {
            $this->setBalance($this->balance() + $bet);
            return response()->json(['player' => $game['player'], 'dealer' => $game['dealer'], 'pval' => $pv, 'dval' => $dv, 'balance' => $this->balance(), 'status' => 'push', 'message' => "Barazim $pv-$dv. Kthehet basti."]);
        } else {
            return response()->json(['player' => $game['player'], 'dealer' => $game['dealer'], 'pval' => $pv, 'dval' => $dv, 'balance' => $this->balance(), 'status' => 'lose', 'message' => "Humbët! $pv vs $dv."]);
        }
    }

    // DICE
    public function rollDice(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $choice = $r->input('choice', 'high'); // high(4-6), low(1-3), even, odd, exact:1-6
        $exact = (int)$r->input('exact', 6);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $roll = rand(1, 6);
        $won = false; $mult = 0;
        if ($choice === 'high') { $won = $roll >= 4; $mult = 1.9; }
        elseif ($choice === 'low') { $won = $roll <= 3; $mult = 1.9; }
        elseif ($choice === 'even') { $won = $roll % 2 === 0; $mult = 1.9; }
        elseif ($choice === 'odd') { $won = $roll % 2 === 1; $mult = 1.9; }
        elseif ($choice === 'exact') { $won = $roll === $exact; $mult = 5.5; }
        $win = $won ? round($bet * $mult, 2) : 0;
        $this->setBalance($balance - $bet + $win);
        return response()->json(['roll' => $roll, 'won' => $won, 'win' => $win, 'profit' => round($win - $bet, 2), 'balance' => $this->balance(), 'multiplier' => $mult]);
    }

    // CRASH (Aviator-style) — shumëzuesi llogaritet nga serveri sipas kohës
    private const CRASH_GROWTH = 0.00006; // mult = e^(k*ms)
    // Ndarja e fitimit: 3% shtëpia / 97% klienti (si kazinot reale).
    private const CRASH_EDGE = 0.03;

    private function crashMult(int $ms): float
    {
        return floor(pow(M_E, self::CRASH_GROWTH * max(0, $ms)) * 100) / 100;
    }

    private function pushCrashHistory(float $crash): array
    {
        $h = session('crash_history', []);
        array_unshift($h, $crash);
        $h = array_slice($h, 0, 12);
        session(['crash_history' => $h]);
        return $h;
    }

    public function crashStart(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $g = session('crash');
        if ($g && !$g['over']) {
            // raundi i vjetër mbyllet si i rrëzuar dhe regjistrohet në histori
            $g['over'] = true;
            session(['crash' => $g]);
            $this->pushCrashHistory($g['crash']);
        }
        // Provably fair: h uniform [0,1) nga seed-i; h < edge → rrëzim instant (pjesa e shtëpisë)
        $seed = bin2hex(random_bytes(16));
        $seedHash = hash('sha256', $seed);
        $h = hexdec(substr($seedHash, 0, 8)) / 4294967295;
        $edge = self::CRASH_EDGE;
        if ($h < $edge) {
            $crash = 1.00; // rrëzim instant (3% e raundeve)
        } else {
            $crash = floor(max(1.01, (1 - $edge) / (1 - $h)) * 100) / 100;
            $crash = min($crash, 100.0);
        }
        $this->setBalance($balance - $bet);
        $round = (int)session('crash_round', 0) + 1;
        session(['crash_round' => $round]);
        $game = ['bet' => $bet, 'start' => (int)(microtime(true) * 1000), 'crash' => $crash, 'over' => false,
            'seed' => $seed, 'hash' => hash('sha256', $seed), 'round' => $round];
        session(['crash' => $game]);
        return response()->json(['bet' => $bet, 'start' => $game['start'], 'round' => $round,
            'hash' => $game['hash'], 'balance' => $this->balance()]);
    }

    public function crashState()
    {
        $g = session('crash');
        if (!$g) return response()->json(['active' => false]);
        $elapsed = (int)(microtime(true) * 1000) - $g['start'];
        $mult = $this->crashMult($elapsed);
        $crashed = $mult >= $g['crash'];
        return response()->json([
            'active' => !$g['over'], 'mult' => $mult, 'crashed' => $crashed,
            'crash' => ($crashed || $g['over']) ? $g['crash'] : null,
            'bet' => $g['bet'], 'history' => session('crash_history', []),
            'round' => $g['round'] ?? null, 'hash' => $g['hash'] ?? null,
            'seed' => ($crashed || $g['over']) ? ($g['seed'] ?? null) : null,
        ]);
    }

    public function crashCashout()
    {
        $g = session('crash');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një raund të ri.'], 422);
        $g['over'] = true;
        $elapsed = (int)(microtime(true) * 1000) - $g['start'];
        $mult = max(1.00, $this->crashMult($elapsed));
        if ($mult >= $g['crash']) {
            session(['crash' => $g]);
            $history = $this->pushCrashHistory($g['crash']);
            return response()->json([
                'busted' => true, 'crash' => $g['crash'], 'mult' => $mult,
                'win' => 0, 'profit' => -$g['bet'], 'balance' => $this->balance(),
                'history' => $history, 'round' => $g['round'] ?? null,
                'hash' => $g['hash'] ?? null, 'seed' => $g['seed'] ?? null,
                'message' => "U rrëzua te {$g['crash']}x! Humbët {$g['bet']}€",
            ]);
        }
        $win = round($g['bet'] * $mult, 2);
        $profit = round($win - $g['bet'], 2);
        $this->setBalance($this->balance() + $win);
        session(['crash' => $g]);
        return response()->json([
            'busted' => false, 'mult' => $mult, 'win' => $win, 'profit' => $profit,
            'balance' => $this->balance(), 'history' => $this->pushCrashHistory($g['crash']),
            'round' => $g['round'] ?? null, 'hash' => $g['hash'] ?? null, 'seed' => $g['seed'] ?? null,
            'message' => "Cashout te {$mult}x! Fitimi neto +{$profit}€",
        ]);
    }

    // MINES 5x5
    private function comb(int $n, int $k): float
    {
        if ($k < 0 || $k > $n) return 0;
        $k = min($k, $n - $k);
        $res = 1.0;
        for ($i = 1; $i <= $k; $i++) $res = $res * ($n - $k + $i) / $i;
        return $res;
    }

    private function minesMult(int $revealed, int $mines): float
    {
        if ($revealed <= 0) return 1.0;
        return round($this->comb(25, $revealed) / $this->comb(25 - $mines, $revealed) * 0.97, 2);
    }

    public function minesStart(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $mines = max(1, min(10, (int)$r->input('mines', 3)));
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $g = session('mines');
        if ($g && !$g['over']) {
            return response()->json(['error' => 'Loja është aktive — bëj cashout ose nis pas përfundimit.'], 422);
        }
        $cells = range(0, 24);
        shuffle($cells);
        $this->setBalance($balance - $bet);
        session(['mines' => [
            'bet' => $bet, 'count' => $mines, 'positions' => array_slice($cells, 0, $mines),
            'revealed' => [], 'over' => false,
        ]]);
        return response()->json(['cells' => 25, 'mines' => $mines, 'bet' => $bet, 'balance' => $this->balance()]);
    }

    public function minesReveal(Request $r)
    {
        $g = session('mines');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        $idx = (int)$r->input('index', -1);
        if ($idx < 0 || $idx > 24 || in_array($idx, $g['revealed'])) {
            return response()->json(['error' => 'Katror i pavlefshëm.'], 422);
        }
        if (in_array($idx, $g['positions'])) {
            $g['over'] = true;
            session(['mines' => $g]);
            return response()->json([
                'hit' => true, 'mines' => $g['positions'], 'revealed' => $g['revealed'],
                'win' => 0, 'profit' => -$g['bet'], 'balance' => $this->balance(),
                'message' => "💥 Minë! Humbët {$g['bet']}€",
            ]);
        }
        $g['revealed'][] = $idx;
        session(['mines' => $g]);
        $mult = $this->minesMult(count($g['revealed']), $g['count']);
        $cashout = round($g['bet'] * $mult, 2);
        return response()->json([
            'hit' => false, 'revealed' => $g['revealed'], 'mult' => $mult,
            'cashout' => $cashout, 'safeLeft' => 25 - $g['count'] - count($g['revealed']),
            'balance' => $this->balance(),
        ]);
    }

    public function minesCashout()
    {
        $g = session('mines');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        if (count($g['revealed']) === 0) return response()->json(['error' => 'Hap të paktën 1 katror të sigurt.'], 422);
        $g['over'] = true;
        $mult = $this->minesMult(count($g['revealed']), $g['count']);
        $win = round($g['bet'] * $mult, 2);
        $profit = round($win - $g['bet'], 2);
        $this->setBalance($this->balance() + $win);
        session(['mines' => $g]);
        return response()->json([
            'win' => $win, 'profit' => $profit, 'mult' => $mult,
            'mines' => $g['positions'], 'balance' => $this->balance(),
            'message' => "Cashout x{$mult}! Fitimi neto +{$profit}€",
        ]);
    }

    // HI-LO me letra
    private function cardLabel(int $rank): string
    {
        return match (true) {
            $rank === 1 => 'A', $rank <= 10 => (string)$rank,
            $rank === 11 => 'J', $rank === 12 => 'Q', default => 'K',
        };
    }

    private function drawHiloCard(): array
    {
        $suits = ['♠', '♥', '♦', '♣'];
        $rank = mt_rand(1, 13);
        return ['rank' => $rank, 'label' => $this->cardLabel($rank), 'suit' => $suits[array_rand($suits)]];
    }

    public function hiloStart(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $g = session('hilo');
        if ($g && !$g['over']) {
            return response()->json(['error' => 'Seria është aktive — bëj cashout ose vazhdo.'], 422);
        }
        $this->setBalance($balance - $bet);
        $card = $this->drawHiloCard();
        session(['hilo' => ['bet' => $bet, 'current' => $card, 'streak' => 0, 'mult' => 1.0, 'over' => false]]);
        return response()->json(['card' => $card, 'streak' => 0, 'mult' => 1.0, 'balance' => $this->balance()]);
    }

    public function hiloGuess(Request $r)
    {
        $g = session('hilo');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një seri të re.'], 422);
        $dir = $r->input('dir', 'higher');
        if (!in_array($dir, ['higher', 'lower'])) return response()->json(['error' => 'Zgjedhje e pavlefshme.'], 422);
        $cur = $g['current']['rank'];
        $fav = $dir === 'higher' ? (13 - $cur) : ($cur - 1);
        if ($fav <= 0) return response()->json(['error' => 'Kjo lëvizje është e pamundur me këtë letër.'], 422);
        $next = $this->drawHiloCard();
        $correct = $dir === 'higher' ? ($next['rank'] > $cur) : ($next['rank'] < $cur);
        if ($correct) {
            $step = round((13 / $fav) * 0.98, 2);
            $g['mult'] = round($g['mult'] * $step, 2);
            $g['streak']++;
            $g['current'] = $next;
            session(['hilo' => $g]);
            $cashout = round($g['bet'] * $g['mult'], 2);
            return response()->json([
                'correct' => true, 'card' => $next, 'prev' => $g['current'],
                'streak' => $g['streak'], 'mult' => $g['mult'], 'cashout' => $cashout,
                'balance' => $this->balance(),
            ]);
        }
        $g['over'] = true;
        session(['hilo' => $g]);
        return response()->json([
            'correct' => false, 'card' => $next, 'streak' => $g['streak'],
            'win' => 0, 'profit' => -$g['bet'], 'balance' => $this->balance(),
            'message' => "Gabim! Doli {$next['label']}. Humbët {$g['bet']}€",
        ]);
    }

    public function hiloCashout()
    {
        $g = session('hilo');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një seri të re.'], 422);
        if ($g['streak'] === 0) return response()->json(['error' => 'Gjej të paktën 1 letër.'], 422);
        $g['over'] = true;
        $win = round($g['bet'] * $g['mult'], 2);
        $profit = round($win - $g['bet'], 2);
        $this->setBalance($this->balance() + $win);
        session(['hilo' => $g]);
        return response()->json([
            'win' => $win, 'profit' => $profit, 'mult' => $g['mult'],
            'streak' => $g['streak'], 'balance' => $this->balance(),
            'message' => "Cashout x{$g['mult']} pas {$g['streak']} goditjesh! +{$profit}€",
        ]);
    }

    public function plinko()
    {
        return view('plinko', ['balance' => $this->balance()]);
    }

    public function chicken()
    {
        return view('chicken', ['balance' => $this->balance()]);
    }

    // PLINKO — topi bie nëpër kunja, shumëzuesi varet nga kutia
    private const PLINKO_TABLES = [
        8 => [
            'low' => [5.6, 2.1, 1.1, 1, 0.5, 1, 1.1, 2.1, 5.6],
            'medium' => [13, 3, 1.3, 0.7, 0.4, 0.7, 1.3, 3, 13],
            'high' => [29, 4, 1.5, 0.7, 0.2, 0.7, 1.5, 4, 29],
        ],
        12 => [
            'low' => [10, 3, 1.6, 1.4, 1.1, 1, 0.5, 1, 1.1, 1.4, 1.6, 3, 10],
            'medium' => [33, 11, 4, 2, 1.1, 0.6, 0.3, 0.6, 1.1, 2, 4, 11, 33],
            'high' => [170, 24, 8.1, 2, 0.7, 0.2, 0.2, 0.2, 0.7, 2, 8.1, 24, 170],
        ],
        16 => [
            'low' => [16, 9, 2, 1.4, 1.4, 1.2, 1.1, 1, 0.5, 1, 1.1, 1.2, 1.4, 1.4, 2, 9, 16],
            'medium' => [110, 41, 10, 5, 3, 1.5, 1, 0.5, 0.3, 0.5, 1, 1.5, 3, 5, 10, 41, 110],
            'high' => [1000, 130, 26, 9, 4, 2, 0.2, 0.2, 0.2, 0.2, 0.2, 2, 4, 9, 26, 130, 1000],
        ],
    ];

    public function plinkoTable(Request $r)
    {
        $rows = (int)$r->input('rows', 12);
        $risk = $r->input('risk', 'medium');
        if (!isset(self::PLINKO_TABLES[$rows][$risk])) {
            return response()->json(['error' => 'Konfigurim i pavlefshëm.'], 422);
        }
        return response()->json(['rows' => $rows, 'risk' => $risk, 'table' => self::PLINKO_TABLES[$rows][$risk]]);
    }

    public function plinkoDrop(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $rows = (int)$r->input('rows', 12);
        $risk = $r->input('risk', 'medium');
        if (!isset(self::PLINKO_TABLES[$rows][$risk])) {
            return response()->json(['error' => 'Konfigurim i pavlefshëm.'], 422);
        }
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $table = self::PLINKO_TABLES[$rows][$risk];
        $path = [];
        $rights = 0;
        for ($i = 0; $i < $rows; $i++) {
            $goRight = mt_rand(0, 1) === 1;
            $path[] = $goRight ? 'R' : 'L';
            if ($goRight) $rights++;
        }
        $mult = $table[$rights];
        $win = round($bet * $mult, 2);
        $profit = round($win - $bet, 2);
        $this->setBalance($balance - $bet + $win);
        return response()->json([
            'path' => $path, 'bin' => $rights, 'mult' => $mult,
            'win' => $win, 'profit' => $profit, 'bet' => $bet,
            'table' => $table, 'rows' => $rows, 'risk' => $risk,
            'balance' => $this->balance(), 'isWin' => $profit > 0,
        ]);
    }

    // CHICKEN ROAD — pula kalon korsitë, cashout para se ta kap makina
    private const CHICKEN_LANES = 15;
    private const CHICKEN_DIFFS = [
        'easy' => ['name' => 'Lehtë', 'survive' => 0.95],
        'medium' => ['name' => 'Mesëm', 'survive' => 0.90],
        'hard' => ['name' => 'Vështirë', 'survive' => 0.80],
        'hardcore' => ['name' => 'Ekstrem', 'survive' => 0.65],
    ];

    private function chickenTable(string $diff): ?array
    {
        if (!isset(self::CHICKEN_DIFFS[$diff])) return null;
        $p = self::CHICKEN_DIFFS[$diff]['survive'];
        $lanes = [];
        for ($l = 1; $l <= self::CHICKEN_LANES; $l++) {
            $lanes[] = round((1 / pow($p, $l)) * 0.97, 2);
        }
        return $lanes;
    }

    public function chickenTableApi(Request $r)
    {
        $diff = $r->input('difficulty', 'medium');
        $lanes = $this->chickenTable($diff);
        if (!$lanes) return response()->json(['error' => 'Vështirësi e pavlefshme.'], 422);
        return response()->json(['difficulty' => $diff, 'lanes' => $lanes]);
    }

    public function chickenStart(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $diff = $r->input('difficulty', 'medium');
        $lanes = $this->chickenTable($diff);
        if (!$lanes) return response()->json(['error' => 'Vështirësi e pavlefshme.'], 422);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $g = session('chicken');
        if ($g && !$g['over']) {
            return response()->json(['error' => 'Loja është aktive — bëj cashout ose vazhdo.'], 422);
        }
        $this->setBalance($balance - $bet);
        session(['chicken' => ['bet' => $bet, 'diff' => $diff, 'lane' => 0, 'over' => false]]);
        return response()->json(['bet' => $bet, 'difficulty' => $diff,
            'diffName' => self::CHICKEN_DIFFS[$diff]['name'],
            'lanes' => $lanes, 'lane' => 0, 'balance' => $this->balance()]);
    }

    public function chickenGo()
    {
        $g = session('chicken');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        if ($g['lane'] >= self::CHICKEN_LANES) return response()->json(['error' => 'Ke kaluar të gjitha korsitë — bëj cashout!'], 422);
        $p = self::CHICKEN_DIFFS[$g['diff']]['survive'];
        $g['lane']++;
        $hit = (mt_rand(1, 10000) / 10000) > $p;
        $lanes = $this->chickenTable($g['diff']);
        if ($hit) {
            $g['over'] = true;
            session(['chicken' => $g]);
            return response()->json([
                'hit' => true, 'lane' => $g['lane'], 'mult' => $lanes[$g['lane'] - 1],
                'win' => 0, 'profit' => -$g['bet'], 'balance' => $this->balance(),
                'message' => "🚗 U godit në korsinë {$g['lane']}! Humbët {$g['bet']}€",
            ]);
        }
        session(['chicken' => $g]);
        $mult = $lanes[$g['lane'] - 1];
        $cashout = round($g['bet'] * $mult, 2);
        return response()->json([
            'hit' => false, 'lane' => $g['lane'], 'mult' => $mult,
            'cashout' => $cashout, 'finished' => $g['lane'] >= self::CHICKEN_LANES,
            'balance' => $this->balance(),
        ]);
    }

    public function chickenCashout()
    {
        $g = session('chicken');
        if (!$g || $g['over']) return response()->json(['error' => 'Nis një lojë të re.'], 422);
        if ($g['lane'] === 0) return response()->json(['error' => 'Kalo të paktën 1 korsi.'], 422);
        $g['over'] = true;
        $lanes = $this->chickenTable($g['diff']);
        $mult = $lanes[$g['lane'] - 1];
        $win = round($g['bet'] * $mult, 2);
        $profit = round($win - $g['bet'], 2);
        $this->setBalance($this->balance() + $win);
        session(['chicken' => $g]);
        return response()->json([
            'win' => $win, 'profit' => $profit, 'mult' => $mult,
            'lane' => $g['lane'], 'balance' => $this->balance(),
            'message' => "Cashout x{$mult} në korsinë {$g['lane']}! +{$profit}€",
        ]);
    }

    // SPORTS
    public function placeBet(Request $r)
    {
        $selections = $r->input('selections', []); // [{match_id, pick, odd, label}]
        $stake = (float)$r->input('stake', 10);
        $balance = $this->balance();
        if (empty($selections)) return response()->json(['error' => 'Zgjidh të paktën 1 ndeshje.'], 422);
        if ($stake <= 0 || $stake > $balance) return response()->json(['error' => 'Stake i pavlefshëm ose balancë e pamjaftueshme.'], 422);
        // Kuotat merren nga oferta zyrtare e ditës në server (jo nga browseri),
        // që bileta të jetë e vlefshme edhe kur oferta ndryshon çdo ditë.
        $offer = [];
        foreach ($this->matches() as $m) $offer[$m['id']] = $m;
        $clean = [];
        foreach ($selections as $s) {
            $mid = $s['match_id'] ?? null;
            $pick = $s['pick'] ?? null;
            if (!isset($offer[$mid])) return response()->json(['error' => 'Ndeshja nuk është më në ofertë (oferta ndryshon çdo ditë). Rifresko faqen.'], 422);
            $odd = \App\Services\SportsOffer::oddFor($offer[$mid], $pick);
            if ($odd === null) return response()->json(['error' => 'Përzgjedhje e pavlefshme.'], 422);
            $m = $offer[$mid];
            $pickLabel = match ($pick) {
                '1' => $m['home'],
                'X' => 'Barazim',
                '2' => $m['away'],
                '1X' => 'Shans i dyfishtë 1X',
                '12' => 'Shans i dyfishtë 12',
                'X2' => 'Shans i dyfishtë X2',
                'GG' => 'GG (shënojnë të dy)',
                'NG' => 'NG (nuk shënojnë të dy)',
                default => $pick,
            };
            $clean[] = [
                'match_id' => $mid,
                'pick' => $pick,
                'odd' => $odd,
                'label' => $m['home'] . ' - ' . $m['away'] . ' → ' . $pickLabel . ' (' . $pick . ')',
            ];
        }
        $selections = $clean;
        $totalOdd = 1;
        foreach ($selections as $s) $totalOdd *= (float)$s['odd'];
        $totalOdd = round($totalOdd, 2);
        $potential = round($stake * $totalOdd, 2);
        $this->setBalance($balance - $stake);
        $tickets = session('tickets', []);
        $ticket = [
            'id' => count($tickets) + 1,
            'selections' => $selections,
            'stake' => $stake,
            'totalOdd' => $totalOdd,
            'potential' => $potential,
            'status' => 'pending',
            'created' => now()->format('d.m.Y H:i'),
        ];
        $tickets[] = $ticket;
        session(['tickets' => $tickets]);
        return response()->json(['ticket' => $ticket, 'balance' => $this->balance()]);
    }

    public function simulateBets()
    {
        $tickets = session('tickets', []);
        foreach ($tickets as &$t) {
            if ($t['status'] !== 'pending') continue;
            // probability based on total odd: higher odd = lower chance
            $chance = max(0.08, min(0.65, 0.9 / $t['totalOdd']));
            $won = (mt_rand(1, 1000) / 1000) < $chance;
            if ($won) {
                $t['status'] = 'won';
                $this->setBalance($this->balance() + $t['potential']);
            } else {
                $t['status'] = 'lost';
            }
        }
        session(['tickets' => $tickets]);
        return response()->json(['tickets' => array_reverse($tickets), 'balance' => $this->balance()]);
    }
}
