<?php
 
namespace App\Observers;
 
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;
 
class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Get original and current status values
        $originalStatus = $order->getOriginal('status');
        $originalPaymentStatus = $order->getOriginal('payment_status');
 
        // Cast enums to their raw string values for reliable comparison
        $originalStatusStr = $originalStatus instanceof OrderStatus ? $originalStatus->value : $originalStatus;
        $originalPaymentStatusStr = $originalPaymentStatus instanceof PaymentStatus ? $originalPaymentStatus->value : $originalPaymentStatus;
 
        $currentStatusStr = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
        $currentPaymentStatusStr = $order->payment_status instanceof PaymentStatus ? $order->payment_status->value : $order->payment_status;
 
        // A status is paid or processing if:
        // - status is processing, shipped, or delivered
        // - OR payment_status is paid
        $wasPaidOrProcessing = in_array($originalStatusStr, ['processing', 'shipped', 'delivered']) 
            || $originalPaymentStatusStr === 'paid';
 
        $isPaidOrProcessing = in_array($currentStatusStr, ['processing', 'shipped', 'delivered']) 
            || $currentPaymentStatusStr === 'paid';
 
        // Only trigger decrement if transitioning into paid/processing for the first time
        if ($isPaidOrProcessing && !$wasPaidOrProcessing) {
            $this->decrementStock($order);
        }

        // If stock had already been deducted (order was paid/processing) and the order
        // is now being cancelled or refunded, restore the stock that was deducted.
        $isCancelledOrRefunded = in_array($currentStatusStr, ['cancelled', 'refunded']);
        $wasCancelledOrRefunded = in_array($originalStatusStr, ['cancelled', 'refunded']);

        if ($wasPaidOrProcessing && $isCancelledOrRefunded && !$wasCancelledOrRefunded) {
            $this->restockItems($order);
        }
    }
 
    /**
     * Decrements product stock in a concurrency-safe manner.
     */
    protected function decrementStock(Order $order): void
    {
        $items = $order->orderItems()->whereNotNull('product_id')->get();
 
        if ($items->isEmpty()) {
            return;
        }
 
        // Deadlock Prevention: get unique product IDs and sort them ASCENDING
        $productIds = $items->pluck('product_id')->unique()->sort()->values()->toArray();
 
        DB::transaction(function () use ($items, $productIds) {
            // Lock products in consistent ascending order
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
 
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
 
                if (!$product) {
                    throw new Exception("Produto com ID {$item->product_id} não encontrado.");
                }
 
                if ($product->stock < $item->quantity) {
                    throw new Exception("Stock insuficiente para o produto: {$product->name}. Disponível: {$product->stock}, Solicitado: {$item->quantity}");
                }
 
                $product->stock -= $item->quantity;
                $product->save();
            }
        });
    }

    /**
     * Restores product stock in a concurrency-safe manner when an order
     * that had already been paid/processing is cancelled or refunded.
     */
    protected function restockItems(Order $order): void
    {
        $items = $order->orderItems()->whereNotNull('product_id')->get();

        if ($items->isEmpty()) {
            return;
        }

        // Deadlock Prevention: get unique product IDs and sort them ASCENDING
        $productIds = $items->pluck('product_id')->unique()->sort()->values()->toArray();

        DB::transaction(function () use ($items, $productIds) {
            // Lock products in consistent ascending order
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (!$product) {
                    continue;
                }

                $product->stock += $item->quantity;
                $product->save();
            }
        });
    }
}
