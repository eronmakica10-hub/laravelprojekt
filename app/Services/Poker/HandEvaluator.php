<?php

namespace App\Services\Poker;

/**
 * Vlerësues duarsh poker (7 letra → 5 më të mirat).
 * Letra: 'As','Td','9h'... (rank 23456789TJQKA + suit shdc)
 * rank() kthen [kategori, tiebreak...] — krahasohet leksikografikisht.
 */
class HandEvaluator
{
    public const NAMES = [
        8 => 'Straight Flush', 7 => 'Katër njësoj', 6 => 'Full House',
        5 => 'Flush (Ngjyrë)', 4 => 'Straight (Radhë)', 3 => 'Tre njësoj',
        2 => 'Dy çifte', 1 => 'Çift', 0 => 'Letra e lartë',
    ];

    private static function val(string $rank): int
    {
        return strpos('23456789TJQKA', $rank) + 2;
    }

    /** @return int[] */
    public static function rank(array $seven): array
    {
        $vals = []; $suits = [];
        foreach ($seven as $c) {
            $v = self::val($c[0]);
            $vals[] = $v;
            $suits[$c[1]][] = $v;
        }
        // flush? (5+ letra të mesma ngjyrë)
        $flushVals = [];
        foreach ($suits as $suit => $vs) {
            if (count($vs) >= 5) { $flushVals = $vs; break; }
        }
        $uniq = array_values(array_unique($vals));
        rsort($uniq);
        $straightHigh = self::straightHigh($uniq);
        if ($flushVals) {
            $fu = array_values(array_unique($flushVals));
            rsort($fu);
            $sfHigh = self::straightHigh($fu);
            if ($sfHigh) return [8, $sfHigh];
        }
        // grupet: count → values (desc)
        $byCount = [];
        $counts = array_count_values($vals);
        foreach ($counts as $v => $n) $byCount[$n][] = $v;
        foreach ($byCount as &$g) rsort($g);
        unset($g);
        if (isset($byCount[4])) {
            $kickers = array_diff($uniq, [$byCount[4][0]]);
            rsort($kickers);
            return [7, $byCount[4][0], $kickers[0]];
        }
        if (isset($byCount[3])) {
            $trips = $byCount[3];
            $pairs = array_merge($byCount[2] ?? [], array_slice($trips, 1));
            if ($pairs) {
                rsort($pairs);
                return [6, $trips[0], $pairs[0]];
            }
            $kickers = array_diff($uniq, [$trips[0]]);
            rsort($kickers);
            return [3, $trips[0], $kickers[0], $kickers[1]];
        }
        if ($flushVals) {
            rsort($flushVals);
            return [5, ...array_slice($flushVals, 0, 5)];
        }
        if ($straightHigh) return [4, $straightHigh];
        if (isset($byCount[2])) {
            $pairs = $byCount[2];
            if (count($pairs) >= 2) {
                $kickers = array_diff($uniq, [$pairs[0], $pairs[1]]);
                rsort($kickers);
                return [2, $pairs[0], $pairs[1], $kickers[0]];
            }
            $kickers = array_diff($uniq, [$pairs[0]]);
            rsort($kickers);
            return [1, $pairs[0], ...array_slice($kickers, 0, 3)];
        }
        return [0, ...array_slice($uniq, 0, 5)];
    }

    private static function straightHigh(array $descUniq): ?int
    {
        $set = array_flip($descUniq);
        if (isset($set[14])) $descUniq[] = 1; // wheel A-2-3-4-5
        $run = 1;
        for ($i = 1; $i < count($descUniq); $i++) {
            if ($descUniq[$i] === $descUniq[$i - 1] - 1) {
                if (++$run >= 5) return $descUniq[$i - 4] === 1 ? 5 : $descUniq[$i - 4];
            } else {
                $run = 1;
            }
        }
        return null;
    }

    /** -1|0|1 */
    public static function compare(array $a, array $b): int
    {
        $n = max(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $x = $a[$i] ?? -1; $y = $b[$i] ?? -1;
            if ($x !== $y) return $x < $y ? -1 : 1;
        }
        return 0;
    }

    public static function name(array $rank): string
    {
        return self::NAMES[$rank[0]] ?? '';
    }
}
