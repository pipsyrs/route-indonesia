<?php

namespace App\Actions;

use App\Support\DummyShop;
use Carbon\Carbon;

/** Validasi ulang kursi pilihan dari keranjang (localStorage) terhadap DummyShop. */
class ValidateSeatSelection
{
    public const MAX_ITEMS = 20;

    public const MAX_KEY_LENGTH = 40;

    /** Kunci error untuk payload yang rusak secara keseluruhan. */
    public const PAYLOAD_ERROR = '_payload';

    /** @return array{valid: bool, errors: array<string, string>} */
    public function handle(mixed $items): array
    {
        if (! is_array($items) || ! array_is_list($items) || count($items) > self::MAX_ITEMS) {
            return $this->invalidPayload();
        }

        $errors = [];
        foreach ($items as $item) {
            $key = is_array($item) ? ($item['key'] ?? null) : null;
            if (! is_string($key) || $key === '' || mb_strlen($key) > self::MAX_KEY_LENGTH) {
                return $this->invalidPayload();
            }

            if (array_key_exists($key, $errors)) {
                continue;
            }

            $error = $this->itemError($item, $key);
            if ($error !== null) {
                $errors[$key] = $error;
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    /** @param  array<mixed>  $item */
    private function itemError(array $item, string $key): ?string
    {
        $tripId = $item['tripId'] ?? null;
        $date = $item['date'] ?? null;
        $qty = $item['qty'] ?? null;
        $seats = $item['seats'] ?? null;

        $trip = is_int($tripId) ? DummyShop::findTrip($tripId) : null;
        if ($trip === null) {
            return 'Trip tidak ditemukan. Hapus dari keranjang.';
        }

        if (! is_string($date) || ! $this->isValidDate($date) || $key !== "{$tripId}|{$date}") {
            return 'Tanggal keberangkatan tidak valid. Pilih ulang trip.';
        }

        if (! is_int($qty) || $qty < 1 || $qty > min($trip['seats_left'], DummyShop::MAX_QTY)) {
            return 'Jumlah tiket melebihi kursi tersedia. Ubah jumlah tiket.';
        }

        if (! is_array($seats) || ! array_is_list($seats) || count($seats) !== $qty) {
            return "Pilih {$qty} kursi untuk trip ini.";
        }

        foreach ($seats as $seat) {
            if (! is_string($seat)) {
                return 'Data kursi tidak valid. Pilih ulang kursi.';
            }
        }

        if (count(array_unique($seats)) !== count($seats)) {
            return 'Kursi yang sama dipilih lebih dari sekali. Pilih ulang kursi.';
        }

        $codes = DummyShop::seatCodes(DummyShop::seatLayout($trip['vehicle']));
        $occupied = DummyShop::occupiedSeats($tripId, $date);

        foreach ($seats as $seat) {
            if (! in_array($seat, $codes, true)) {
                return 'Kursi tidak ada di denah. Pilih ulang kursi.';
            }

            if (in_array($seat, $occupied, true)) {
                return "Kursi {$seat} sudah terisi. Pilih ulang kursi.";
            }
        }

        return null;
    }

    /** Hanya Y-m-d valid, hari ini atau setelahnya. */
    private function isValidDate(string $value): bool
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
            return false;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return false;
        }

        return $date && $date->format('Y-m-d') === $value && ! $date->lessThan(now()->startOfDay());
    }

    /** @return array{valid: false, errors: array<string, string>} */
    private function invalidPayload(): array
    {
        return ['valid' => false, 'errors' => [self::PAYLOAD_ERROR => 'Data kursi tidak valid. Muat ulang keranjang.']];
    }
}
