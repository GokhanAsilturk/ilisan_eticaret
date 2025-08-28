<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Toplam Kullanıcı', User::count())
                ->description('Kayıtlı kullanıcı sayısı')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Toplam Ürün', Product::count())
                ->description('Mağazadaki ürün sayısı')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Toplam Sipariş', Order::count())
                ->description('Verilen sipariş sayısı')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Toplam Kategori', Category::count())
                ->description('Aktif kategori sayısı')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
        ];
    }
}
