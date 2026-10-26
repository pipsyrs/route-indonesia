<?php

namespace App\Services;

/**
 * Sumber daftar rekening tujuan transfer manual.
 * Satu-satunya titik yang diganti ke DB saat dashboard admin dibuat.
 */
class PaymentAccountService
{
    /**
     * @return list<array{bank: string, number: string, holder: string}>
     */
    public function active(): array
    {
        $accounts = config('payment.bank_accounts', []);

        if (! is_array($accounts)) {
            return [];
        }

        $result = [];

        foreach ($accounts as $account) {
            if (! is_array($account) || ! ($account['active'] ?? true)) {
                continue;
            }

            $bank = trim((string) ($account['bank'] ?? ''));
            $number = trim((string) ($account['number'] ?? ''));
            $holder = trim((string) ($account['holder'] ?? ''));

            if ($bank === '' || $number === '' || $holder === '') {
                continue;
            }

            $result[] = ['bank' => $bank, 'number' => $number, 'holder' => $holder];
        }

        return $result;
    }
}
