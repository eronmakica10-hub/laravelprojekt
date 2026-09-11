<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserStore;

class WalletController extends Controller
{
    private function balance()
    {
        if (session('user_id')) {
            $u = UserStore::findById(session('user_id'));
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
        if (session('user_id')) UserStore::updateBalance(session('user_id'), $v);
        return $v;
    }

    private function pushTx($type, $amount, $note)
    {
        $tx = [
            'id' => 1,
            'type' => $type,
            'amount' => round($amount, 2),
            'balance_after' => $this->balance(),
            'note' => $note,
            'date' => now()->format('d.m.Y H:i'),
        ];
        if (session('user_id')) {
            return UserStore::addTransaction(session('user_id'), $type, $amount, $note);
        }
        $txs = session('transactions', []);
        $tx['id'] = count($txs) + 1;
        $txs[] = $tx;
        session(['transactions' => $txs]);
        return $tx;
    }

    public function history()
    {
        $txs = session('user_id')
            ? UserStore::transactions(session('user_id'))
            : array_reverse(session('transactions', []));
        return response()->json(['transactions' => $txs, 'balance' => $this->balance()]);
    }

    public function deposit(Request $r)
    {
        $amount = (float)$r->input('amount', 0);
        if ($amount < 5) return response()->json(['error' => 'Depozita minimale është 5€.'], 422);
        if ($amount > 10000) return response()->json(['error' => 'Depozita maksimale është 10,000€.'], 422);
        $new = $this->setBalance($this->balance() + $amount);
        $tx = $this->pushTx('deposit', $amount, 'Depozitë me kartë •••• ' . substr(preg_replace('/\D/', '', (string)$r->input('card', '')), -4));
        return response()->json([
            'balance' => $new, 'transaction' => $tx,
            'message' => "U depozituan {$amount}€. Balanca: {$new}€",
        ]);
    }

    public function withdraw(Request $r)
    {
        $amount = (float)$r->input('amount', 0);
        $iban = trim((string)$r->input('iban', ''));
        $balance = $this->balance();
        if ($amount < 10) return response()->json(['error' => 'Tërheqja minimale është 10€.'], 422);
        if ($amount > $balance) return response()->json(['error' => 'Balancë e pamjaftueshme për tërheqje.'], 422);
        if (strlen($iban) < 8) return response()->json(['error' => 'Shkruaj një IBAN valid (min. 8 karaktere).'], 422);
        $new = $this->setBalance($balance - $amount);
        $tx = $this->pushTx('withdraw', $amount, 'Tërheqje në ' . strtoupper(substr($iban, 0, 12)) . '…');
        return response()->json([
            'balance' => $new, 'transaction' => $tx,
            'message' => "U tërhoqën {$amount}€. Arrijnë brenda 24h.",
        ]);
    }
}
