<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Toplam Sipariş', $this->getTotalOrders())
                ->description('Tüm siparişler')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Bekleyen Siparişler', $this->getPendingOrders())
                ->description('İşlem bekleyen')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Bu Ayki Gelir', $this->getMonthlyRevenue())
                ->description('Bu ay toplam')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([15, 20, 18, 22, 25, 19, 30]),

            Stat::make('Aktif Ürünler', $this->getActiveProducts())
                ->description('Satışta olan ürünler')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Toplam Müşteri', $this->getTotalCustomers())
                ->description('Kayıtlı müşteriler')
                ->descriptionIcon('heroicon-m-users')
                ->color('secondary'),

            Stat::make('Kategoriler', $this->getTotalCategories())
                ->description('Aktif kategoriler')
                ->descriptionIcon('heroicon-m-tag')
                ->color('gray'),
        ];
    }

    private function getTotalOrders(): int
    {
        return Order::count();
    }

    private function getPendingOrders(): int
    {
        return Order::where('status', OrderStatus::PENDING->value)->count();
    }

    private function getMonthlyRevenue(): string
    {
        $revenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', OrderStatus::CANCELLED->value)
            ->sum('total');

        return '₺' . Number::format($revenue, precision: 2);
    }

    private function getActiveProducts(): int
    {
        return Product::where('is_active', true)->count();
    }

    private function getTotalCustomers(): int
    {
        return User::where('role', 'user')->count();
    }

    private function getTotalCategories(): int
    {
        return Category::where('is_active', true)->count();
    }
}
