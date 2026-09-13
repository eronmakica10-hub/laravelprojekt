<?php

namespace App\Services\Poker;

/**
 * Motor Texas Hold'em me limit fiks strukturor (no-limit i thjeshtuar).
 *
 * State (array, serializohet në session/cache):
 *  players: [{name,isBot,stack,bet,folded,allin,cards[],sittingOut}]
 *  button, street, community[], deck[], actor, currentBet, minRaise,
 *  contrib[seat=>int], handNo, sb, bb, log[], winners, lastSettle
 */
class TexasHoldem
{
    public const STREETS = ['preflop', 'flop', 'turn', 'river', 'showdown', 'done'];

    public static function newTable(array $names, int $buyIn, int $sb, int $bb, array $isBot = []): array
    {
        $players = [];
        foreach ($names as $i => $n) {
            $players[] = [
                'name' => $n, 'isBot' => $isBot[$i] ?? false,
                'stack' => $buyIn, 'bet' => 0, 'folded' => false, 'allin' => false,
                'cards' => [], 'sittingOut' => false,
            ];
        }
        return [
            'players' => $players, 'buyIn' => $buyIn, 'sb' => $sb, 'bb' => $bb,
            'button' => count($players) - 1, 'street' => 'lobby',
            'community' => [], 'deck' => [], 'actor' => null,
            'currentBet' => 0, 'minRaise' => $bb, 'contrib' => [],
            'handNo' => 0, 'log' => [], 'winners' => null, 'lastSettle' => null,
        ];
    }

    private static function deck(): array
    {
        $d = [];
        foreach (str_split('23456789TJQKA') as $r) {
            foreach (str_split('shdc') as $s) $d[] = $r . $s;
        }
        shuffle($d);
        return $d;
    }

    private static function active(array $s): array
    {
        $out = [];
        foreach ($s['players'] as $i => $p) {
            if (!$p['sittingOut'] && ($p['stack'] > 0 || $p['bet'] > 0 || $p['allin'])) $out[] = $i;
        }
        return $out;
    }

    private static function inHand(array $s): array
    {
        $out = [];
        foreach ($s['players'] as $i => $p) {
            if (!$p['folded'] && !$p['sittingOut']) $out[] = $i;
        }
        return $out;
    }

    public static function startHand(array &$s): bool
    {
        $seats = self::active($s);
        if (count($seats) < 2) {
            $s['log'][] = 'Duhen së paku 2 lojtarë me chipsa.';
            return false;
        }
        $s['handNo']++;
        $s['deck'] = self::deck();
        $s['community'] = [];
        $s['street'] = 'preflop';
        $s['currentBet'] = 0;
        $s['minRaise'] = $s['bb'];
        $s['contrib'] = [];
        $s['winners'] = null;
        $s['lastSettle'] = null;
        $s['lastAction'] = [];
        foreach ($s['players'] as $i => &$p) {
            $p['bet'] = 0; $p['folded'] = false; $p['allin'] = false;
            $p['cards'] = in_array($i, $seats) ? [array_pop($s['deck']), array_pop($s['deck'])] : [];
        }
        unset($p);
        // button te lojtari tjetër aktiv
        $n = count($s['players']);
        do {
            $s['button'] = ($s['button'] + 1) % $n;
        } while (!in_array($s['button'], $seats));
        // blinds (heads-up: button = SB)
        $ordered = self::orderFrom($s, $s['button']);
        if (count($seats) === 2) {
            self::postBlind($s, $ordered[1], $s['sb']); // button
            self::postBlind($s, $ordered[0], $s['bb']);
            $s['actor'] = $ordered[1];
        } else {
            self::postBlind($s, $ordered[0], $s['sb']);
            self::postBlind($s, $ordered[1], $s['bb']);
            $s['actor'] = $ordered[2];
        }
        $s['acted'] = [];
        self::log($s, "Dora #{$s['handNo']} — blinds {$s['sb']}/{$s['bb']}");
        // aktori i parë mban radhën — avanco vetëm nëse ai është all-in nga blindi
        $ap = $s['players'][$s['actor']];
        if ($ap['allin'] || $ap['stack'] <= 0) {
            $s['acted'] = [$s['actor']];
            self::advance($s);
        }
        return true;
    }

    private static function orderFrom(array $s, int $from): array
    {
        // lojtarët në dorë, duke filluar pas $from
        $n = count($s['players']);
        $out = [];
        for ($k = 1; $k <= $n; $k++) {
            $i = ($from + $k) % $n;
            if (!$s['players'][$i]['folded'] && !$s['players'][$i]['sittingOut']) $out[] = $i;
        }
        return $out;
    }

    private static function postBlind(array &$s, int $seat, int $amt): void
    {
        $p = &$s['players'][$seat];
        $pay = min($amt, $p['stack']);
        $p['stack'] -= $pay;
        $p['bet'] += $pay;
        $s['contrib'][$seat] = ($s['contrib'][$seat] ?? 0) + $pay;
        if ($p['stack'] === 0) $p['allin'] = true;
        if ($p['bet'] > $s['currentBet']) {
            $s['minRaise'] = max($s['bb'], $p['bet'] - $s['currentBet']);
            $s['currentBet'] = $p['bet'];
        }
        self::log($s, "{$p['name']} blind {$pay}");
    }

    private static function log(array &$s, string $msg): void
    {
        $s['log'][] = $msg;
        if (count($s['log']) > 30) array_shift($s['log']);
    }

    /** @return array ['ok'=>true] ose ['error'=>msg] */
    public static function act(array &$s, int $seat, string $action, int $amount = 0): array
    {
        if ($s['actor'] !== $seat) return ['error' => 'Nuk e ke radhën.'];
        $p = &$s['players'][$seat];
        $toCall = $s['currentBet'] - $p['bet'];
        $desc = $p['name'];
        switch ($action) {
            case 'fold':
                $p['folded'] = true;
                $s['lastAction'][$seat] = 'FOLD';
                $desc .= ' fold';
                break;
            case 'check':
                if ($toCall > 0) return ['error' => 'Duhet call ' . $toCall . ' ose fold.'];
                $s['lastAction'][$seat] = 'CHECK';
                $desc .= ' check';
                break;
            case 'call':
                $pay = min($toCall, $p['stack']);
                $p['stack'] -= $pay; $p['bet'] += $pay;
                $s['contrib'][$seat] = ($s['contrib'][$seat] ?? 0) + $pay;
                $s['lastAction'][$seat] = 'CALL ' . $pay;
                if ($p['stack'] === 0 && $pay < $toCall) { $p['allin'] = true; $desc .= " all-in {$pay}"; }
                else { if ($p['stack'] === 0) $p['allin'] = true; $desc .= " call {$pay}"; }
                break;
            case 'bet':
            case 'raise':
                if ($toCall > 0 && $action === 'bet') return ['error' => 'Ka bast — bëj raise ose call.'];
                if ($toCall === 0 && $action === 'raise') return ['error' => 'S’ka bast — bëj bet ose check.'];
                $target = max($amount, 0);
                $minTotal = $s['currentBet'] + $s['minRaise'];
                if ($target < $minTotal && $target < $p['bet'] + $p['stack']) {
                    return ['error' => "Raise minimal: shuma totale {$minTotal}."];
                }
                $need = $target - $p['bet'];
                if ($need <= 0) return ['error' => 'Shuma e pavlefshme.'];
                if ($need >= $p['stack']) {
                    // all-in
                    $target = $p['bet'] + $p['stack'];
                    $need = $p['stack'];
                    $p['stack'] = 0; $p['bet'] = $target; $p['allin'] = true;
                    $s['contrib'][$seat] = ($s['contrib'][$seat] ?? 0) + $need;
                    $s['lastAction'][$seat] = 'ALL-IN ' . $target;
                    if ($target > $s['currentBet']) {
                        $raiseBy = $target - $s['currentBet'];
                        if ($raiseBy >= $s['minRaise']) {
                            $s['minRaise'] = $raiseBy;
                            $s['actor'] = $seat;
                            $s['acted'] = [$seat];
                            self::log($s, "$desc all-in {$target}");
                            self::advance($s);
                            return ['ok' => true];
                        }
                        $s['currentBet'] = $target;
                    }
                    $desc .= " all-in {$target}";
                } else {
                    $p['stack'] -= $need; $p['bet'] = $target;
                    $s['contrib'][$seat] = ($s['contrib'][$seat] ?? 0) + $need;
                    $s['minRaise'] = $target - $s['currentBet'];
                    $s['currentBet'] = $target;
                    $s['lastAction'][$seat] = strtoupper($action) . ' ' . $target;
                    $desc .= " {$action} në {$target}";
                    // raundi rifillon pas agresorit
                    $s['actor'] = $seat;
                    $s['acted'] = [$seat];
                    self::log($s, $desc);
                    self::advance($s);
                    return ['ok' => true];
                }
                break;
            case 'allin':
                $target = $p['bet'] + $p['stack'];
                $need = $p['stack'];
                $p['stack'] = 0; $p['bet'] = $target; $p['allin'] = true;
                $s['contrib'][$seat] = ($s['contrib'][$seat] ?? 0) + $need;
                $s['lastAction'][$seat] = 'ALL-IN ' . $target;
                if ($target > $s['currentBet']) {
                    $raiseBy = $target - $s['currentBet'];
                    if ($raiseBy >= $s['minRaise']) {
                        $s['minRaise'] = $raiseBy;
                        $s['currentBet'] = $target;
                        $s['actor'] = $seat;
                        $s['acted'] = [$seat];
                        self::log($s, "$desc all-in {$target}");
                        self::advance($s);
                        return ['ok' => true];
                    }
                    $s['currentBet'] = $target;
                }
                $desc .= " all-in {$target}";
                break;
            default:
                return ['error' => 'Veprim i panjohur.'];
        }
        $s['acted'][] = $seat;
        self::log($s, $desc);
        self::advance($s);
        return ['ok' => true];
    }

    private static function advance(array &$s): void
    {
        // për 20 hapa maksimumi (rrugë, showdown)
        for ($guard = 0; $guard < 20; $guard++) {
            if ($s['street'] === 'done') return;
            $alive = self::inHand($s);
            if (count($alive) === 1) {
                self::awardUncontested($s, $alive[0]);
                return;
            }
            // a mund të veprojë kush? (jo-folded, jo-allin, ka stack)
            $canAct = array_values(array_filter($alive, fn ($i) => !$s['players'][$i]['allin'] && $s['players'][$i]['stack'] > 0));
            if (count($canAct) === 0) {
                // të gjithë all-in → hap letrat deri në fund (showdown thirret nga nextStreet)
                while ($s['street'] !== 'river' && $s['street'] !== 'showdown' && $s['street'] !== 'done') self::nextStreet($s);
                if ($s['street'] === 'river') self::nextStreet($s);
                return;
            }
            if (count($canAct) === 1 && count($alive) > 1) {
                // njëri mund të veprojë — a ka nevojë?
                $only = $canAct[0];
                $needs = array_filter($alive, fn ($i) => $i !== $only && !$s['players'][$i]['allin'] && $s['players'][$i]['bet'] < $s['currentBet']);
                if (empty($needs) && in_array($only, $s['acted'] ?? [])) {
                    self::nextStreet($s);
                    continue;
                }
                // kontrollo nëse raundi i basteve ka përfunduar
                if (self::roundClosed($s, $canAct)) {
                    self::nextStreet($s);
                    continue;
                }
                // gjej aktorin tjetër që mund të veprojë
                $nxt = self::nextActor($s, $s['actor'] ?? $s['button'], $canAct);
                if ($nxt === null) {
                    self::nextStreet($s);
                    continue;
                }
                $s['actor'] = $nxt;
                return;
            }
            if (self::roundClosed($s, $canAct)) {
                self::nextStreet($s);
                continue;
            }
            $nxt = self::nextActor($s, $s['actor'] ?? $s['button'], $canAct);
            if ($nxt === null) {
                self::nextStreet($s);
                continue;
            }
            $s['actor'] = $nxt;
            return;
        }
    }

    private static function roundClosed(array $s, array $canAct): bool
    {
        foreach ($canAct as $i) {
            if (!in_array($i, $s['acted'] ?? [])) return false;
            if ($s['players'][$i]['bet'] < $s['currentBet']) return false;
        }
        return true;
    }

    private static function nextActor(array $s, int $from, array $canAct): ?int
    {
        $n = count($s['players']);
        for ($k = 1; $k <= $n; $k++) {
            $i = ($from + $k) % $n;
            if (in_array($i, $canAct) && !in_array($i, $s['acted'] ?? []) ) return $i;
            if (in_array($i, $canAct) && $s['players'][$i]['bet'] < $s['currentBet']) return $i;
        }
        // nëse të gjithë kanë vepruar por dikush s'ka barazuar (s'duhet të ndodhë), kthe të parin
        foreach ($canAct as $i) {
            if ($s['players'][$i]['bet'] < $s['currentBet']) return $i;
        }
        return null;
    }

    private static function nextStreet(array &$s): void
    {
        if ($s['street'] === 'done' || $s['street'] === 'showdown') return;
        // mblidh bastet në pot (pot mbahet në contrib; display llogaritet)
        foreach ($s['players'] as &$p) $p['bet'] = 0;
        unset($p);
        $s['currentBet'] = 0;
        $s['minRaise'] = $s['bb'];
        $s['acted'] = [];
        $s['lastAction'] = [];
        switch ($s['street']) {
            case 'preflop':
                $s['community'] = [array_pop($s['deck']), array_pop($s['deck']), array_pop($s['deck'])];
                $s['street'] = 'flop';
                self::log($s, 'Flop: ' . implode(' ', $s['community']));
                break;
            case 'flop':
                $s['community'][] = array_pop($s['deck']);
                $s['street'] = 'turn';
                self::log($s, 'Turn: ' . end($s['community']));
                break;
            case 'turn':
                $s['community'][] = array_pop($s['deck']);
                $s['street'] = 'river';
                self::log($s, 'River: ' . end($s['community']));
                break;
            case 'river':
                $s['street'] = 'showdown';
                self::showdown($s);
                return;
        }
        $alive = self::inHand($s);
        $canAct = array_values(array_filter($alive, fn ($i) => !$s['players'][$i]['allin'] && $s['players'][$i]['stack'] > 0));
        if (empty($canAct)) {
            // askush s'mund të veprojë → vazhdo rrugën
            return;
        }
        // i pari pas buttonit
        $n = count($s['players']);
        for ($k = 1; $k <= $n; $k++) {
            $i = ($s['button'] + $k) % $n;
            if (in_array($i, $canAct)) {
                $s['actor'] = $i;
                return;
            }
        }
    }

    private static function potTotal(array $s): int
    {
        return array_sum($s['contrib'] ?? []);
    }

    private static function awardUncontested(array &$s, int $winner): void
    {
        $pot = self::potTotal($s);
        $s['players'][$winner]['stack'] += $pot;
        $s['winners'] = [['seat' => $winner, 'amount' => $pot, 'hand' => null, 'desc' => 'fitoi pa showdown']];
        $s['lastSettle'] = ['pot' => $pot];
        self::log($s, "{$s['players'][$winner]['name']} fiton {$pot} (të gjithë fold)");
        self::finishHand($s);
    }

    private static function showdown(array &$s): void
    {
        $alive = self::inHand($s);
        $ranks = [];
        foreach ($alive as $i) {
            $ranks[$i] = HandEvaluator::rank(array_merge($s['players'][$i]['cards'], $s['community']));
        }
        // side pots nga nivelet e kontributeve
        $levels = array_unique(array_values(array_filter($s['contrib'] ?? [], fn ($v) => $v > 0)));
        sort($levels);
        $prev = 0;
        $awards = [];
        foreach ($levels as $lv) {
            $pot = 0;
            $eligible = [];
            foreach ($s['contrib'] as $seat => $c) {
                $take = min($c, $lv) - min($c, $prev);
                if ($take > 0) {
                    $pot += $take;
                    if (in_array($seat, $alive)) $eligible[] = $seat;
                }
            }
            $prev = $lv;
            if ($pot <= 0 || empty($eligible)) continue;
            $best = null;
            $champs = [];
            foreach ($eligible as $i) {
                if ($best === null || HandEvaluator::compare($ranks[$i], $best) > 0) {
                    $best = $ranks[$i];
                    $champs = [$i];
                } elseif (HandEvaluator::compare($ranks[$i], $best) === 0) {
                    $champs[] = $i;
                }
            }
            $share = intdiv($pot, count($champs));
            $rem = $pot % count($champs);
            foreach ($champs as $k => $i) {
                $amt = $share + ($k === 0 ? $rem : 0);
                $s['players'][$i]['stack'] += $amt;
                $awards[] = ['seat' => $i, 'amount' => $amt, 'hand' => $ranks[$i], 'desc' => HandEvaluator::name($ranks[$i])];
            }
        }
        $s['winners'] = $awards;
        $s['lastSettle'] = ['pot' => self::potTotal($s)];
        foreach ($awards as $a) {
            self::log($s, "{$s['players'][$a['seat']]['name']} fiton {$a['amount']} ({$a['desc']})");
        }
        self::finishHand($s);
    }

    private static function finishHand(array &$s): void
    {
        $s['street'] = 'done';
        $s['actor'] = null;
        // lojtarët pa chipsa ulen jashtë
        foreach ($s['players'] as &$p) {
            $p['bet'] = 0;
            if ($p['stack'] <= 0) $p['sittingOut'] = true;
        }
        unset($p);
    }

    public static function pot(array $s): int
    {
        $p = self::potTotal($s);
        foreach ($s['players'] as $pl) $p += $pl['bet'];
        return $p;
    }

    /** Pamje e filtruar për lojtarin $seat (letrat e të tjerëve fshihen). */
    public static function viewFor(array $s, int $seat): array
    {
        $players = [];
        foreach ($s['players'] as $i => $p) {
            $players[] = [
                'name' => $p['name'], 'isBot' => $p['isBot'],
                'stack' => $p['stack'], 'bet' => $p['bet'],
                'folded' => $p['folded'], 'allin' => $p['allin'],
                'sittingOut' => $p['sittingOut'],
                'cards' => $i === $seat ? $p['cards'] : (isset($s['winners']) && $s['street'] === 'done' && !$p['folded'] ? $p['cards'] : []),
                'isDealer' => $i === $s['button'],
                'isActor' => $i === $s['actor'],
                'isYou' => $i === $seat,
                'lastAction' => $s['lastAction'][$i] ?? '',
            ];
        }
        return [
            'players' => $players,
            'button' => $s['button'],
            'street' => $s['street'],
            'community' => $s['community'],
            'pot' => self::pot($s),
            'actor' => $s['actor'],
            'currentBet' => $s['currentBet'],
            'minRaise' => $s['minRaise'],
            'toCall' => $seat >= 0 ? max(0, $s['currentBet'] - ($s['players'][$seat]['bet'] ?? 0)) : 0,
            'handNo' => $s['handNo'],
            'sb' => $s['sb'], 'bb' => $s['bb'],
            'log' => array_slice($s['log'], -12),
            'winners' => $s['winners'],
        ];
    }
}
