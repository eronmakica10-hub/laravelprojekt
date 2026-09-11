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

    public function slots()
    {
        return view('slots', ['balance' => $this->balance()]);
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

    public function sports()
    {
        $matches = $this->matches();
        $tickets = session('tickets', []);
        return view('sports', ['balance' => $this->balance(), 'matches' => $matches, 'tickets' => $tickets]);
    }

    private function matches()
    {
        // Ndeshje reale aktuale — java e 11-14 Shtator 2026 (burimi: PremierLeague.com, UEFA, Gazzetta)
        return [
            ['id' => 1, 'league' => 'Serie A 🇮🇹', 'home' => 'Venezia', 'away' => 'Fiorentina', 'time' => 'Sot 20:45', 'o1' => 3.00, 'ox' => 3.10, 'o2' => 2.45, 'over' => 2.10, 'under' => 1.72],
            ['id' => 2, 'league' => 'La Liga 🇪🇸', 'home' => 'Sevilla', 'away' => 'Valencia', 'time' => 'Sot 21:00', 'o1' => 2.20, 'ox' => 3.20, 'o2' => 3.30, 'over' => 2.05, 'under' => 1.76],
            ['id' => 3, 'league' => 'Bundesliga 🇩🇪', 'home' => 'Union Berlin', 'away' => 'Schalke 04', 'time' => 'Sot 20:30', 'o1' => 2.00, 'ox' => 3.40, 'o2' => 3.70, 'over' => 1.95, 'under' => 1.85],
            ['id' => 4, 'league' => 'Premier League 🏴󐁧󐁢󐁥󐁮󐁧󐁿', 'home' => 'Sunderland', 'away' => 'Arsenal', 'time' => 'Nesër 20:00', 'o1' => 5.00, 'ox' => 3.80, 'o2' => 1.65, 'over' => 1.90, 'under' => 1.90],
            ['id' => 5, 'league' => 'Premier League 🏴󐁧󐁢󐁥󐁮󐁧󐁿', 'home' => 'Tottenham', 'away' => 'Everton', 'time' => 'Nesër 17:30', 'o1' => 1.75, 'ox' => 3.70, 'o2' => 4.50, 'over' => 1.85, 'under' => 1.95],
            ['id' => 6, 'league' => 'Premier League 🏴󐁧󐁢󐁥󐁮󐁧󐁿', 'home' => 'Liverpool', 'away' => 'Fulham', 'time' => 'Nesër 16:00', 'o1' => 1.28, 'ox' => 5.75, 'o2' => 9.50, 'over' => 1.60, 'under' => 2.30],
            ['id' => 7, 'league' => 'Premier League — DERBI 🔥', 'home' => 'Man United', 'away' => 'Man City', 'time' => 'E Diel 16:30', 'o1' => 3.40, 'ox' => 3.50, 'o2' => 2.05, 'over' => 1.80, 'under' => 2.00],
            ['id' => 8, 'league' => 'La Liga 🇪🇸', 'home' => 'Levante', 'away' => 'Barcelona', 'time' => 'E Diel 16:15', 'o1' => 7.00, 'ox' => 5.00, 'o2' => 1.40, 'over' => 1.65, 'under' => 2.20],
            ['id' => 9, 'league' => 'La Liga 🇪🇸', 'home' => 'Real Sociedad', 'away' => 'Atlético Madrid', 'time' => 'E Diel 21:00', 'o1' => 3.10, 'ox' => 3.10, 'o2' => 2.40, 'over' => 2.20, 'under' => 1.66],
            ['id' => 10, 'league' => 'Serie A 🇮🇹', 'home' => 'Napoli', 'away' => 'Bologna', 'time' => 'E Diel 18:00', 'o1' => 1.70, 'ox' => 3.60, 'o2' => 5.00, 'over' => 1.90, 'under' => 1.90],
            ['id' => 11, 'league' => 'Superliga e Kosovës 🇽🇰', 'home' => 'Prishtina', 'away' => 'Feronikeli', 'time' => 'E Diel 16:00', 'o1' => 1.45, 'ox' => 4.20, 'o2' => 6.00, 'over' => 1.80, 'under' => 2.00],
            ['id' => 12, 'league' => 'Superliga e Kosovës 🇽🇰', 'home' => 'Drita', 'away' => 'Dukagjini', 'time' => 'Nesër 16:00', 'o1' => 1.55, 'ox' => 3.80, 'o2' => 5.50, 'over' => 1.85, 'under' => 1.95],
        ];
    }

    // ---------- API ----------
    public function getBalance()
    {
        return response()->json(['balance' => $this->balance()]);
    }

    // SLOTS
    public function spinSlots(Request $r)
    {
        $bet = (float)$r->input('bet', 10);
        $balance = $this->balance();
        if ($bet <= 0 || $bet > $balance) {
            return response()->json(['error' => 'Balancë e pamjaftueshme! Depozito nga 💳 Portofoli.'], 422);
        }
        $symbols = ['🍒', '🍋', '🔔', '⭐', '💎', '7️⃣'];
        $reels = [$symbols[array_rand($symbols)], $symbols[array_rand($symbols)], $symbols[array_rand($symbols)]];
        $mult = 0; $win = 0; $msg = 'Humbët. Provo përsëri!';
        if ($reels[0] === $reels[1] && $reels[1] === $reels[2]) {
            $mult = match ($reels[0]) {
                '7️⃣' => 10, '💎' => 8, '⭐' => 5, '🔔' => 4, '🍋' => 3, default => 2.5,
            };
            $msg = "JACKPOT! 3x {$reels[0]}";
        } elseif ($reels[0] === $reels[1] || $reels[1] === $reels[2] || $reels[0] === $reels[2]) {
            // 2 same: small win, higher if includes 7 or diamond
            $mult = (in_array('7️⃣', $reels) || in_array('💎', $reels)) ? 0.8 : 0.4;
            $msg = 'Fitore e vogël!';
            if ($mult >= 1) { /* */ }
        }
        $win = round($bet * $mult, 2);
        $profit = round($win - $bet, 2);
        $newBalance = $balance - $bet + $win;
        $this->setBalance($newBalance);
        // fitore e vërtetë vetëm nëse fitimi neto > 0 (kthimi pjesor x0.4/x0.8 nuk është fitore)
        if ($profit > 0 && $mult >= 2.5) {
            $msg .= " Fitimi neto +{$profit}€!";
        } elseif ($win > 0 && $profit <= 0) {
            $msg = "Ktheve {$win}€ nga {$bet}€ bast (humbje neto {$profit}€).";
        }
        return response()->json([
            'reels' => $reels, 'win' => $win, 'profit' => $profit, 'bet' => $bet,
            'balance' => $this->balance(), 'isWin' => $profit > 0,
            'isJackpot' => $mult >= 5, 'message' => $msg, 'multiplier' => $mult,
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

    // SPORTS
    public function placeBet(Request $r)
    {
        $selections = $r->input('selections', []); // [{match_id, pick, odd, label}]
        $stake = (float)$r->input('stake', 10);
        $balance = $this->balance();
        if (empty($selections)) return response()->json(['error' => 'Zgjidh të paktën 1 ndeshje.'], 422);
        if ($stake <= 0 || $stake > $balance) return response()->json(['error' => 'Stake i pavlefshëm ose balancë e pamjaftueshme.'], 422);
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
