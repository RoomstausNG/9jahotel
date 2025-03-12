<?php

namespace App\Livewire\Configuration;

use App\Models\Item;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class ItemsPage extends Component
{
    public $name, $cost_price, $price, $limit = 0, $quantity, $discount, $image, $revenue_id, $hotel_id;
    use WithPagination, WithFileUploads;
    public $item;
    public $perPage = 10;
    public $query = '';

    public $itemForm = false;

    public $updateI = false;

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

    function confirmDelete(Item $item)
    {
        // dd($room);
        $this->item = $item;
        $this->confirm = true;
    }

    function AddItem()
    {
        $data = $this->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric'],
            'quantity' => ['nullable', 'numeric'],
            'cost_price' => ['nullable', 'numeric'],
            'limit' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'max:512'],
            'revenue_id' => ['required'],
        ]);

        if ($this->image) {
            $url = $this->image->store('items', 'public');
            $data['image'] = $url;
        }

        $created = Item::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Item created successfully',
                'title' => 'Item Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editItem(Item $item)
    {
        $this->updateI = true;
        $this->itemForm = true;
        $this->item = $item;
        $this->cid = $item->id;
        $this->name = $item->name;
        $this->discount = $item->discount;
        $this->quantity = $item->quantity;
        $this->cost_price = $item->cost_price;
        $this->price = $item->price;
        $this->limit = $item->limit;
        $this->revenue_id = $item->revenue_id;
    }

    function updateItem()
    {
        $this->updateI = true;
        $item = Item::find($this->cid);
        $data = $this->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric'],
            'quantity' => ['nullable', 'numeric'],
            'cost_price' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'image' => ['nullable', 'image', 'max:240'],
            'limit' => ['nullable', 'numeric'],
            'revenue_id' => ['required'],
        ]);

        if (gettype(intval($this->cost_price)) != 'integer') {
            $data['cost_price'] = null;
        }

        if ($this->image) {
            $url = $this->image->store('items', 'public');
            $data['image'] = $url;
        }

        $updated = $item->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Item updated successfully.',
                'title' => 'Item updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteItem(Item $item)
    {
        $del = $item->delete();
        if ($del) {
            $this->reset();
            $details = [
                'text' => 'Item removed successfully alongside orders and revenues associated',
                'title' => 'Item Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleItem()
    {
        $selectedItem = Item::whereKey($this->checked)->delete();
        if ($selectedItem) {
            $this->reset();
            $details = [
                'text' => 'Item removed successfully',
                'title' => 'Multiple Item Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }
    public function render()
    {
        $term = "%" . $this->query . "%";
        $items = Item::with(['revenueSource'])
            // ->where('name', 'LIKE', trim($term))
            // ->orwhere('price', 'LIKE', trim($term))
            // ->orwhere('quantity', 'LIKE', trim($term))
            // ->orWhereHas('revenueSource', fn (Builder $query) => $query->where('source', 'LIKE', trim($term)))
            ->paginate($this->perPage);
        return view('livewire.configuration.items-page', compact(['items']));
    }
}