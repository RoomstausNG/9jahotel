<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Revenue;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\ItemResource;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SalesCollection;
use Illuminate\Database\Eloquent\Builder;

class SalesController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $sales = Sale::with(['orders'])->whereRelation('orders', fn (Builder $query) => $query->whereHas('item', fn (Builder $query) => $query->where('name', 'Like', "%{$request->item}%")))->paginate(20);
        // $sales = Sale::where('code', 'Like', "%{$request->item}%")->paginate(20);
        return new SalesCollection($sales);
        // return $sales;
    }
    public function customerType(Request $request)
    {
        return ['guest', 'in-room guest'];
    }

    public function items(Request $request)
    {
        $items = Item::where('name', 'Like', "%{$request->name}%")->paginate(20);
        return ItemResource::collection($items);
    }

    public function category(Request $request)
    {
        $categories = Revenue::where('source', 'Like', "%{$request->name}%")->select('id', 'source')->get();
        return $categories;
    }

    public function item(Item $item)
    {
        return new ItemResource($item);
    }

    public function show($sale)
    {
        $sale = Sale::where('code', $sale)->first();
        if ($sale) {
            return new SaleResource($sale->load('orders'));
        }
        return $this->errorResponse('Sale not found', 404);
    }

    public function addSale(Request $request)
    {

        $data = $request->validate([
            'type' => ['required'],
            'discount' => ['required', 'numeric'],
            'mode_of_payment' => ['required'],
            'booking_id' => ['nullable', Rule::requiredIf($request->type == 'in-room guest'), 'exists:bookings,id'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.item_id' => ['required', 'exists:items,id'],
        ]);

        $data['agent_id'] = $request->user()->id;
        $data['discount'] = $data['discount'] ?? 0;
        $data['booking_id'] = $data['booking_id'] ?? null;

        // create sales
        $sale = Sale::create([
            'agent_id' => $data['agent_id'],
            'type' => $data['type'],
            'discount' => $data['discount'],
            'mode_of_payment' => $data['mode_of_payment'],
            'booking_id' => $data['booking_id'],
        ]);

        // use sale to create order
        foreach ($request->items as $item) {
            $sale->orders()->create([
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'total_price' => $this->getTotalItemPrice($item['item_id'], $item['quantity'])
            ]);
        }
        $sum = $sale->orders()->sum('total_price');
        if ($data['discount'] > 0) {
            $discount = $data['discount'] / 100  * $sum;
            $sum -= $discount;
        }

        // update sales total

        $sale->update(['total' => $sum]);

        // update finance
        $sale->finances()->create([
            'amount' => $sale->total,
            // 'amount' => $sale->quantity * $sale->item->price,
            'description' => 'sales of items',
            'type' => 'income'
        ]);

        return $this->successResponse(new SaleResource($sale->load('orders')), 'Sales made successfully', 201);
    }

    public function update(Request $request, $order)
    {
        $sale = Order::where('code', $order)->first();

        if (!$sale) {
            return $this->errorResponse('sales doesn\'t exist', 404);
        }

        $data = $request->validate([
            'type' => ['required'],
            'discount' => ['nullable', 'numeric'],
            'quantity' => ['required', 'numeric'],
            'mode_of_payment' => ['required'],
            'total_price' => ['nullable', 'numeric'],
            'booking_id' => ['nullable', Rule::requiredIf($request->type == 'in-room guest'), 'exists:bookings,id']
        ]);

        if ($data['type'] == 'guest') {
            $data['booking_id'] = null;
        }
        $data['item_id'] = $sale->item_id;
        $data['agent_id'] = $request->user()->id;
        $data['discount'] = $data['discount'] ?? 0;
        // $data['total_price'] = $data['total_price'] ?? $this->getTotalPrice($data);

        $done = $sale->update($data);

        return $this->successResponse(new SaleResource($sale), 'Sales updated successfully', 201);
    }

    public function updateSale(Request $request, $sale)
    {
        // Find the existing sale
        $sale = Sale::where('code', $sale)->first();

        if (!$sale) {
            return $this->errorResponse('Sale not found', 404);
        }

        // Validate the request data
        $data = $request->validate([
            'type' => ['required'],
            'discount' => ['required', 'numeric'],
            'mode_of_payment' => ['required'],
            'booking_id' => ['nullable', Rule::requiredIf($request->type == 'in-room guest'), 'exists:bookings,id'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.item_id' => ['required', 'exists:items,id'],
        ]);



        // Update sale attributes
        $sale->update([
            'type' => $data['type'],
            'discount' => $data['discount'],
            'mode_of_payment' => $data['mode_of_payment'],
            'booking_id' => $data['booking_id'],
        ]);

        // Delete existing orders and recreate them
        $sale->orders()->delete();
        foreach ($request->items as $item) {
            $sale->orders()->create([
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'total_price' => $this->getTotalItemPrice($item['item_id'], $item['quantity']),
            ]);
        }

        // Calculate the total price after discount
        $sum = $sale->orders()->sum('total_price');
        if ($data['discount'] > 0) {
            $discount = $data['discount'] / 100 * $sum;
            $sum -= $discount;
        }

        // Update the sale total
        $sale->update(['total' => $sum]);

        // Update the related finance record
        $sale->finances()->update(
            [
                'amount' => $sale->total,
                'description' => 'sales of items',
                'type' => 'income',
            ]
        );

        return $this->successResponse(new SaleResource($sale->load('orders')), 'Sales updated successfully', 200);
    }


    public function getTotalItemPrice($item_id, $qty)
    {
        $item = Item::find($item_id);
        return $item->price * $qty;
        // $discount = $data['discount'] / 100  * $item->price;
        // $result = $subtotal - $discount;
        // return $subtotal;
    }

    public function destroy($sale)
    {
        $sale = Sale::where('code', $sale)->first();
        if ($sale) {
            $deleted = $sale->delete();
            return response()->noContent();
        } else {
            return $this->errorResponse('Sales not found', 404);
        }
    }
}
