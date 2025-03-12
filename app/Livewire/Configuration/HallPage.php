<?php

namespace App\Livewire\Configuration;

use App\Models\Hall;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;

class HallPage extends Component
{
    public $name, $price, $capacity, $status, $remarks, $description, $image, $is_available, $promo_price, $reference, $promo_start_day, $promo_end_day, $deposit;
    use WithPagination, WithFileUploads;
    public $hall;
    public $perPage = 10;
    public $perPageRoom = 10;
    public $query = '';

    public $hallForm = false;

    public $updateType = false;
    public $updateH = false;

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

    function confirmDelete(Hall $hall)
    {
        // dd($room);
        $this->hall = $hall;
        $this->confirm = true;
    }

    function AddHall()
    {
        $data = $this->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric'],
            'deposit' => ['nullable', 'numeric'],
            'capacity' => ['nullable', 'numeric'],
            'status' => ['required'],
            'description' => ['nullable'],
            'promo_price' => ['nullable', 'numeric'],
            'promo_start_day' => ['nullable'],
            'promo_end_day' => ['nullable'],
            'remarks' => 'nullable'
        ]);

        $created = Hall::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Hall created successfully, you can now book it now',
                'title' => 'Hall Created'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editHall(Hall $hall)
    {
        $this->updateH = true;
        $this->hallForm = true;
        $this->hall = $hall;
        $this->cid = $hall->id;
        $this->name = $hall->name;
        $this->description = $hall->description;
        $this->capacity = $hall->capacity;
        $this->status = $hall->status;
        $this->price = $hall->price;
        $this->deposit = $hall->deposit;
        $this->promo_price = $hall->promo_price;
        $this->promo_start_day = $hall->promo_start_day;
        $this->promo_end_day = $hall->promo_end_day;
        $this->remarks = $hall->remarks;
    }

    function updateHall()
    {
        $this->updateType = true;
        $hall = Hall::find($this->cid);
        $data = $this->validate([
            'name' => ['required'],
            'price' => ['required', 'numeric'],
            'deposit' => ['nullable', 'numeric'],
            'capacity' => ['nullable', 'numeric'],
            'status' => ['required'],
            'description' => ['nullable'],
            'promo_price' => ['nullable', 'numeric'],
            'promo_start_day' => ['nullable'],
            'promo_end_day' => ['nullable'],
            'remarks' => 'nullable'
        ]);

        $updated = $hall->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Hall updated successfully.',
                'title' => 'Hall updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteHall(Hall $hall)
    {
        $del = $hall->delete();
        if ($del) {
            $this->reset();
            $details = [
                'text' => 'Hall removed successfully alongside bookings',
                'title' => 'Hall Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleType()
    {
        $selectedHall = Hall::whereKey($this->checked)->delete();
        if ($selectedHall) {
            $this->reset();
            $details = [
                'text' => 'Hall removed successfully',
                'title' => 'Multiple Hall Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }
    public function render()
    {
        $term = "%" . $this->query . "%";
        $halls = Hall::with(['type'])
            ->where('name', 'LIKE', trim($term))
            ->orwhere('price', 'LIKE', trim($term))
            ->orwhere('capacity', 'LIKE', trim($term))
            ->orWhereHas('type', fn (Builder $query) => $query->where('name', 'LIKE', trim($term)))
            ->paginate($this->perPage);
        return view('livewire.configuration.hall-page', compact(['halls']));
    }
}