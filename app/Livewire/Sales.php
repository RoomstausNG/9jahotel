<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Models\Customer;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class Sales extends Component
{
    public $quantity, $item_id, $booking_id, $sale_id, $discount = 0, $customer_id, $total_price, $agent_id, $mode_of_payment, $type;
    use WithPagination, WithFileUploads;

    public $sale;
    public $perPage = 10;
    public $query = '';

    public $saleForm = false;
    public $orderForm = false;

    public $updateC = false;

    public function search()
    {
        $this->resetPage();
    }

    public $cid;
    public $confirm = false;
    public $update = false;
    public $form = false;

    public ?array $checked = [];
    public $sortField = 'id';
    public $sortAsc = true;

    function showForm()
    {
        $this->form = true;
    }



    public function sortBy($field)
    {

        if ($this->sortField == $field) {
            $this->sortAsc = !$this->sortAsc;
        } else {
            $this->sortAsc = true;
        }
        $this->sortField = $field;
    }

    function updatingSearch()
    {
        $this->resetPage();
    }

    function updatedSearch()
    {
        $this->resetPage();
    }

    function updatedPerPage()
    {
        $this->resetPage();
    }


    function AddSale()
    {
        $data = $this->validate(
            [
                'booking_id' => ['nullable', 'exists:bookings,id'],
                'item_id' => ['required', 'exists:items,id'],
                'quantity' => ['required', 'numeric'],
                'type' => ['required'],
                'discount' => 'nullable|numeric|max:100',
                'mode_of_payment' => ['required']
            ]
        );
        $data['agent_id'] = auth()->id();
        $created = Order::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Sales Added successfully',
                'title' => 'Sales Made'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editSale(Order $sale)
    {
        $this->updateC = true;
        $this->orderForm = true;
        $this->saleForm = true;
        $this->sale = $sale;
        $this->cid = $sale->id;
        $this->type = $sale->type;
        $this->customer_id = $sale->customer_id;
        $this->booking_id = $sale->booking_id;
        $this->item_id = $sale->item_id;
        $this->agent_id = $sale->agent_id;
        $this->quantity = $sale->quantity;
        $this->discount = $sale->discount;
        $this->mode_of_payment = $sale->mode_of_payment;
    }

    function updateSale()
    {
        $this->updateC = true;
        $type = Order::find($this->cid);
        $data = $this->validate(
            [
                'booking_id' => ['nullable', 'exists:bookings,id'],
                'item_id' => ['required', 'exists:items,id'],
                'quantity' => ['required', 'numeric'],
                'type' => ['required'],
                'discount' => 'nullable|numeric|max:100',
                'mode_of_payment' => ['required']
            ],
            ['state_id.required' => 'Business Source is required']
        );

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Sales updated successfully.',
                'title' => 'Sales Saved'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteSale(Order $order)
    {
        $selectedUsers = $order->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Sales successfully deleted',
                'title' => 'Sales Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleSale()
    {
        $selectedUsers = Customer::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Business Type removed successfully',
                'title' => 'Multiple Room Type Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function mount()
    {
    }


    public function render()
    {
        $term = "%" . $this->query . "%";
        $sales = Order::with(['item', 'customer'])
            ->where('code', 'LIKE', trim($term))
            ->orWhere('mode_of_payment', 'LIKE', trim($term))
            ->orWhereHas('item', fn (Builder $query) => $query->where('cost_price', 'LIKE', trim($term)))
            ->orderByDesc('id')
            ->paginate($this->perPage);
        return view('livewire.sales', compact(['sales']));
    }
}