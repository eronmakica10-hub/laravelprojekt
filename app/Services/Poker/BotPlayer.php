<?php

namespace App\Services\Poker;

/**
 * AI për botat: forca e dorës (Chen preflop / made-hand + outs postflop)
 * + personalitet (tight/loose, agresiv/pasiv, bluff).
 */
class BotPlayer
{
    /** @return array [action, amount] — AI eksperte: ranges, pozita, pot odds, value, bluff */
    public static function decide(array $s, int $seat): array
    {
        $p = $s['players'][$seat];
        $toCall = $s['currentBet'] - $p['bet'];
        $pot = TexasHoldem::pot($s);
        $style = $p['style'] ?? ['tight' => 0.5, 'aggr' => 0.5, 'bluff' => 0.06];
        $canCheck = $toCall <= 0;
        $eq = self::equity($p['cards'], $s['community'], $s['street']);
        $eq += self::positionBonus($s, $seat) + (0.5 - $style['tight']) * 0.06;

        if ($s['street'] === 'preflop') {
            return self::preflop($s, $seat, $toCall, $pot, $eq, $style, $canCheck);
        }
        return self::postflop($s, $seat, $toCall, $pot, $eq, $style, $canCheck);
    }

    /** Bonusi i pozitës: button +0.04, të parët -0.03. */
    private static function positionBonus(array $s, int $seat): float
    {
        $order = [];
        $n = count($s['players']);
        for ($k = 1; $k <= $n; $k++) {
            $i = ($s['button'] + $k) % $n;
            if (!($s['players'][$i]['folded'] ?? true) && !($s['players'][$i]['sittingOut'] ?? true)) $order[] = $i;
        }
        if (empty($order)) return 0.0;
        $pos = array_search($seat, $order);
        if ($pos === false) return 0.0;
        if ($pos >= count($order) - 2) return 0.04; // vonë
        if ($pos <= 1) return -0.03; // herët
        return 0.0;
    }

    private static function preflop(array $s, int $seat, int $toCall, int $pot, float $eq, array $style, bool $canCheck): array
    {
        $p = $s['players'][$seat];
        $chen = self::chen($p['cards']);
        $pair = $p['cards'][0][0] === $p['cards'][1][0];
        $bigPair = $pair && strpos('TJQKA', $p['cards'][0][0]) !== false;

        // hapja (s'ka raise para teje — veç blinds)
        $facingRaise = $toCall > $s['bb'];
        if (!$facingRaise) {
            if ($chen >= 7.5 || $bigPair) {
                return self::openRaise($s, $seat, $style); // premium: raise 3x
            }
            if ($canCheck) {
                // BB falas: nganjëherë squeeze/attack me dorë mesatare
                if ($chen >= 6.5 && (mt_rand(1, 1000) / 1000) < $style['aggr'] * 0.4) {
                    return self::openRaise($s, $seat, $style);
                }
                return ['check', 0];
            }
            // SB kompletimi / call i lirë — luajmë gjerë që dora të mos vdesë direkt
            if ($chen >= 5.5 || ($chen >= 4.5 && (mt_rand(1, 1000) / 1000) < 1 - $style['tight'])) {
                return ['call', 0];
            }
            return ['fold', 0];
        }
        // përballë raise-it
        if ($chen >= 11 || ($pair && strpos('JQKA', $p['cards'][0][0]) !== false)) {
            return self::threeBet($s, $seat, $style); // QQ+, AK: 3-bet
        }
        $potOdds = $toCall / max(1, $pot + $toCall);
        if ($chen >= 8 && ($potOdds < 0.28 || $toCall <= $s['bb'] * 4)) return ['call', 0];
        if ($chen >= 6.5 && $potOdds < 0.18) return ['call', 0];
        if ($toCall <= max($s['bb'], (int)($pot * 0.04)) && $eq > 0.15) return ['call', 0]; // çmim qesharak
        return ['fold', 0];
    }

    private static function openRaise(array $s, int $seat, array $style): array
    {
        $p = $s['players'][$seat];
        $target = $s['currentBet'] + max($s['minRaise'], $s['bb'] * 3);
        if ($target >= $p['bet'] + $p['stack']) return ['allin', 0];
        return ['raise', $target];
    }

    private static function threeBet(array $s, int $seat, array $style): array
    {
        $p = $s['players'][$seat];
        $target = $s['currentBet'] + max($s['minRaise'] * 2, (int)($s['bb'] * 7));
        if ($target >= $p['bet'] + $p['stack']) {
            return $p['stack'] > $s['currentBet'] ? ['allin', 0] : ['call', 0];
        }
        return ['raise', $target];
    }

    private static function postflop(array $s, int $seat, int $toCall, int $pot, float $eq, array $style, bool $canCheck): array
    {
        $potOdds = $toCall > 0 ? $toCall / ($pot + $toCall) : 0;

        // monster: gjithmonë value/agresion
        if ($eq >= 0.72) {
            return self::aggressive($s, $seat, $toCall, $style, $eq);
        }
        // dorë e fortë: bet kur checkohet, call me odds
        if ($eq >= 0.55) {
            if ($canCheck) {
                if ((mt_rand(1, 1000) / 1000) < 0.35 + $style['aggr'] * 0.5) {
                    return self::aggressive($s, $seat, 0, $style, $eq);
                }
                return ['check', 0];
            }
            if ($eq > $potOdds + 0.06 || $toCall <= $s['bb']) return ['call', 0];
            return ['fold', 0];
        }
        // mesatare + draws: call lirë, fold shtrenjtë
        if ($eq >= 0.36) {
            if ($canCheck) {
                // semi-bluff i rrallë me draw
                if ($s['street'] !== 'river' && (mt_rand(1, 1000) / 1000) < $style['aggr'] * 0.25) {
                    return self::aggressive($s, $seat, 0, $style, $eq);
                }
                return ['check', 0];
            }
            if ($potOdds < 0.25 || $toCall <= $s['bb']) return ['call', 0];
            return ['fold', 0];
        }
        // ajër: check/fold, bluff i balancuar veç kur checkohet
        if ($canCheck) {
            $headsUp = count(array_filter($s['players'], fn ($q) => !($q['folded'] ?? true))) <= 2;
            if ($headsUp && $s['street'] !== 'river' && (mt_rand(1, 1000) / 1000) < $style['bluff']) {
                return self::aggressive($s, $seat, 0, $style, $eq);
            }
            return ['check', 0];
        }
        if ($toCall <= max($s['bb'], (int)($pot * 0.04)) && $eq > 0.15) return ['call', 0];
        return ['fold', 0];
    }

    private static function aggressive(array $s, int $seat, int $toCall, array $style, float $eq = 0.7): array
    {
        $p = $s['players'][$seat];
        $pot = TexasHoldem::pot($s);
        if ($toCall > 0) {
            // raise 2.5-3.5x mbi bastin aktual (ose call nëse s'ka stack)
            $target = $s['currentBet'] + max($s['minRaise'], (int)round($pot * (0.5 + $style['aggr'] * 0.5)));
            if ($target >= $p['bet'] + $p['stack']) {
                // all-in vetem me monster ose short-stack; ndryshe call
                if ($eq >= 0.68 || $p['stack'] <= $s['bb'] * 8) return ['allin', 0];
                return ['call', 0];
            }
            if ($target <= $s['currentBet']) return ['call', 0];
            return ['raise', $target];
        }
        $size = max($s['bb'] * 2, (int)round($pot * (0.4 + $style['aggr'] * 0.5)));
        $size = min($size, $p['stack']);
        if ($size >= $p['stack']) {
            if ($eq >= 0.6 || $p['stack'] <= $s['bb'] * 6) return ['allin', 0];
            return ['check', 0];
        }
        if ($size <= 0) return ['check', 0];
        return ['bet', $p['bet'] + $size];
    }

    /** Equity 0..1 e përafërt. */
    public static function equity(array $hole, array $community, string $street): float
    {
        if (count($hole) < 2) return 0.0;
        if ($street === 'preflop' || empty($community)) {
            return self::chen($hole) / 20.0; // ~0..1
        }
        $all = array_merge($hole, $community);
        $rank = HandEvaluator::rank($all);
        $cat = $rank[0];
        // baza nga kategoria e dorës së bërë
        $base = [0 => 0.18, 1 => 0.42, 2 => 0.55, 3 => 0.68, 4 => 0.62, 5 => 0.72, 6 => 0.82, 7 => 0.92, 8 => 0.98][$cat];
        // outs: flush draw +4, OESD +4, overcards +1.5 secila (rregulli 4/2)
        $outs = self::countOuts($hole, $community);
        $streetLeft = $street === 'flop' ? 2 : ($street === 'turn' ? 1 : 0);
        $drawEq = $streetLeft === 2 ? min(0.35, $outs * 0.04) : ($streetLeft === 1 ? min(0.2, $outs * 0.02) : 0);
        return min(0.97, max($base, $base * 0.6 + $drawEq + 0.15));
    }

    private static function chen(array $hole): float
    {
        $v = array_map(fn ($c) => strpos('23456789TJQKA', $c[0]) + 2, $hole);
        rsort($v);
        [$h, $l] = $v;
        $score = [14 => 10, 13 => 8, 12 => 7, 11 => 6][$h] ?? $h / 2;
        if ($hole[0][0] === $hole[1][0]) $score = max(5, $score * 2); // suited x2 (min 5)
        $gap = $h - $l;
        if ($gap === 0) { /* pair: tashmë */ }
        elseif ($gap === 1) $score -= ($h < 12 ? 1 : 0);
        elseif ($gap === 2) $score -= 1;
        elseif ($gap === 3) $score -= 2;
        elseif ($gap === 4) $score -= 4;
        else $score -= 5;
        if ($gap <= 1 && $h < 12) $score += 1; // straight bonus duar të ulëta
        return max(0, $score);
    }

    private static function countOuts(array $hole, array $community): int
    {
        $all = array_merge($hole, $community);
        $suits = [];
        foreach ($all as $c) $suits[$c[1]] = ($suits[$c[1]] ?? 0) + 1;
        $outs = 0;
        if (in_array(4, $suits)) $outs += 9; // flush draw
        // OESD: 4 në radhë
        $vals = array_unique(array_map(fn ($c) => strpos('23456789TJQKA', $c[0]) + 2, $all));
        sort($vals);
        if (count($vals) >= 4) {
            $ext = $vals;
            if (in_array(14, $vals)) $ext[] = 1;
            sort($ext);
            for ($i = 0; $i + 3 < count($ext); $i++) {
                $w = array_slice($ext, $i, 4);
                if ($w[3] - $w[0] === 3 && count(array_unique($w)) === 4) { $outs += 8; break; }
            }
        }
        // overcards ndaj bordit (vetëm me 0-1 çift?)
        $boardMax = 0;
        foreach ($community as $c) $boardMax = max($boardMax, strpos('23456789TJQKA', $c[0]) + 2);
        foreach ($hole as $c) {
            $v = strpos('23456789TJQKA', $c[0]) + 2;
            if ($v > $boardMax) $outs += 3;
        }
        return min(15, $outs);
    }

    public static function randomStyle(): array
    {
        $types = [
            ['tight' => 0.7, 'aggr' => 0.4, 'bluff' => 0.03],
            ['tight' => 0.4, 'aggr' => 0.6, 'bluff' => 0.07],
            ['tight' => 0.55, 'aggr' => 0.75, 'bluff' => 0.1],
            ['tight' => 0.3, 'aggr' => 0.35, 'bluff' => 0.05],
        ];
        return $types[array_rand($types)];
    }

    public static function botNames(int $n): array
    {
        $pool = ['Arben', 'Blerta', 'Dren', 'Edona', 'Fisnik', 'Genta', 'Luan', 'Mimoza', 'Petrit', 'Qëndresa'];
        shuffle($pool);
        return array_slice($pool, 0, $n);
    }
}
