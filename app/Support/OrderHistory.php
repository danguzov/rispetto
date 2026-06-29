<?php

namespace App\Support;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

final class OrderHistory
{
    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    public static function availableMonths(): array
    {
        return Order::query()
            ->whereNotNull('created_at')
            ->orderByDesc('created_at')
            ->get(['id', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'))
            ->map(fn (Collection $orders, string $key): array => [
                'key' => $key,
                'label' => self::monthLabel($key),
                'count' => $orders->count(),
            ])
            ->sortByDesc('key')
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, count: int}>
     */
    public static function daysInMonth(string $monthKey): array
    {
        if (! self::isValidMonthKey($monthKey)) {
            return [];
        }

        [$year, $month] = array_map('intval', explode('-', $monthKey));

        return Order::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('created_at')
            ->get(['id', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m-d'))
            ->map(fn (Collection $orders, string $key): array => [
                'key' => $key,
                'label' => self::dayLabel($key),
                'count' => $orders->count(),
            ])
            ->sortByDesc('key')
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Order>
     */
    public static function ordersForDay(string $dayKey): Collection
    {
        if (! self::isValidDayKey($dayKey)) {
            return new Collection;
        }

        return Order::query()
            ->with('items')
            ->whereDate('created_at', $dayKey)
            ->orderByDesc('created_at')
            ->get();
    }

    public static function defaultMonthKey(): string
    {
        $months = self::availableMonths();

        if ($months === []) {
            return now()->format('Y-m');
        }

        $current = now()->format('Y-m');

        foreach ($months as $month) {
            if ($month['key'] === $current) {
                return $current;
            }
        }

        return $months[0]['key'];
    }

    public static function defaultDayKey(string $monthKey): ?string
    {
        $days = self::daysInMonth($monthKey);

        if ($days === []) {
            return null;
        }

        $today = now()->format('Y-m-d');

        foreach ($days as $day) {
            if ($day['key'] === $today) {
                return $today;
            }
        }

        return $days[0]['key'];
    }

    public static function monthLabel(string $monthKey): string
    {
        return Date::createFromFormat('Y-m', $monthKey)
            ->locale('sr_Latn')
            ->translatedFormat('F Y.');
    }

    public static function dayLabel(string $dayKey): string
    {
        return Date::createFromFormat('Y-m-d', $dayKey)
            ->locale('sr_Latn')
            ->translatedFormat('j. F');
    }

    public static function dayHeading(string $dayKey): string
    {
        return Date::createFromFormat('Y-m-d', $dayKey)
            ->locale('sr_Latn')
            ->translatedFormat('l, j. F Y.');
    }

    public static function isValidMonthKey(string $monthKey): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}$/', $monthKey)
            && Carbon::hasFormat($monthKey, 'Y-m');
    }

    public static function isValidDayKey(string $dayKey): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayKey)
            && Carbon::hasFormat($dayKey, 'Y-m-d');
    }
}
