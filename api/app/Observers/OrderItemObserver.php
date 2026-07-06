<?php
 
namespace App\Observers;
 
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
 
class OrderItemObserver
{
    /**
     * Handle the OrderItem "saved" event.
     */
    public function saved(OrderItem $item): void
    {
        if ($item->order_id) {
            $this->recalculateOrderWeight($item->order_id);
        }
    }
 
    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $item): void
    {
        if ($item->order_id) {
            $this->recalculateOrderWeight($item->order_id);
        }
    }
 
    /**
     * Recalculates the cumulative weight of the order.
     */
    protected function recalculateOrderWeight(int $orderId): void
    {
        $order = Order::find($orderId);
        if (!$order) {
            return;
        }
 
        // Aggregated DB query to calculate total weight without memory overhead
        $totalWeight = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('order_items.order_id', $orderId)
            ->sum(DB::raw('COALESCE(products.weight, 0) * order_items.quantity'));
 
        $order->weight = $totalWeight;
        $order->save();
    }
}
