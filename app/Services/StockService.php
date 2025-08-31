<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function isInStock(ProductVariant $variant, int $quantity = 1): bool
    {
        $inventory = $variant->inventory;
        
        if (!$inventory || !$inventory->track_quantity) {
            return true;
        }
        
        return $inventory->available_quantity >= $quantity;
    }

    public function reserveStock(ProductVariant $variant, int $quantity): bool
    {
        return DB::transaction(function () use ($variant, $quantity) {
            $inventory = $variant->inventory()->lockForUpdate()->first();
            
            if (!$inventory || !$this->isInStock($variant, $quantity)) {
                return false;
            }
            
            $inventory->increment('reserved_quantity', $quantity);
            $inventory->decrement('available_quantity', $quantity);
            
            return true;
        });
    }

    public function releaseStock(ProductVariant $variant, int $quantity): void
    {
        DB::transaction(function () use ($variant, $quantity) {
            $inventory = $variant->inventory()->lockForUpdate()->first();
            
            if ($inventory) {
                $inventory->decrement('reserved_quantity', $quantity);
                $inventory->increment('available_quantity', $quantity);
            }
        });
    }

    public function confirmStock(ProductVariant $variant, int $quantity): void
    {
        DB::transaction(function () use ($variant, $quantity) {
            $inventory = $variant->inventory()->lockForUpdate()->first();
            
            if ($inventory) {
                $inventory->decrement('reserved_quantity', $quantity);
                $inventory->decrement('quantity', $quantity);
            }
        });
    }

    public function getStockStatus(ProductVariant $variant): string
    {
        $inventory = $variant->inventory;
        
        if (!$inventory || !$inventory->track_quantity) {
            return 'in_stock';
        }
        
        if ($inventory->available_quantity <= 0) {
            return 'out_of_stock';
        }
        
        if ($inventory->available_quantity <= $inventory->low_stock_threshold) {
            return 'low_stock';
        }
        
        return 'in_stock';
    }

    public function getAvailableQuantity(ProductVariant $variant): int
    {
        $inventory = $variant->inventory;
        
        if (!$inventory || !$inventory->track_quantity) {
            return 999;
        }
        
        return $inventory->available_quantity;
    }
}
