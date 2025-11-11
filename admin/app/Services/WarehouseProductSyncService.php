<?php

namespace App\Services;

use App\Models\Product;
use App\Models\WarehousesProduct;

class WarehouseProductSyncService
{
    /**
     * Sync warehouse product quantity and cost with product
     *
     * @param int $productId
     * @param float|null $quantity Optional quantity. If null, uses product's stock_quantity
     * @param float|null $avgCost Optional avg_cost. If null, uses product's cost_price
     * @return WarehousesProduct
     */
    public static function sync(int $productId, ?float $quantity = null, ?float $avgCost = null): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        $quantity = $quantity ?? $product->stock_quantity;
        $avgCost = $avgCost ?? $product->cost_price ?? 0;

        return WarehousesProduct::updateOrCreate(
            ['product_id' => $productId],
            [
                'quantity' => max(0, $quantity), // Ensure quantity doesn't go below 0
                'avg_cost' => $avgCost
            ]
        );
    }


    /**
     * Adjust warehouse product quantity based on adjustment type
     *
     * @param int $productId
     * @param string $type 'addition' or 'subtraction'
     * @param float $quantity
     * @return WarehousesProduct
     */
    public static function adjustQuantity(int $productId, string $type, float $quantity): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        // Update product stock quantity
        if ($type === 'addition') {
            $product->increment('stock_quantity', $quantity);
        } else {
            $product->decrement('stock_quantity', $quantity);
        }

        // Refresh to get updated value
        $product->refresh();

        // Sync warehouse product with updated product stock
        return self::sync($productId);
    }

    /**
     * Revert warehouse product quantity adjustment
     *
     * @param int $productId
     * @param string $type 'addition' or 'subtraction'
     * @param float $quantity
     * @return WarehousesProduct
     */
    public static function revertQuantity(int $productId, string $type, float $quantity): WarehousesProduct
    {
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }

        // Revert product stock quantity (opposite of adjust)
        if ($type === 'addition') {
            $product->decrement('stock_quantity', $quantity);
        } else {
            $product->increment('stock_quantity', $quantity);
        }

        // Refresh to get updated value
        $product->refresh();

        // Sync warehouse product with reverted product stock
        return self::sync($productId);
    }

    /**
     * Process quantity adjustments in batch
     * Note: Should be called within a transaction context
     *
     * @param array $adjustments Array of ['product_id' => int, 'type' => string, 'quantity' => float]
     * @return void
     */
    public static function processAdjustments(array $adjustments): void
    {
        foreach ($adjustments as $adjustment) {
            self::adjustQuantity(
                $adjustment['product_id'],
                $adjustment['type'],
                $adjustment['quantity']
            );
        }
    }

    /**
     * Revert quantity adjustments in batch
     * Note: Should be called within a transaction context
     *
     * @param array $adjustments Array of ['product_id' => int, 'type' => string, 'quantity' => float]
     * @return void
     */
    public static function revertAdjustments(array $adjustments): void
    {
        foreach ($adjustments as $adjustment) {
            self::revertQuantity(
                $adjustment['product_id'],
                $adjustment['type'],
                $adjustment['quantity']
            );
        }
    }
}

