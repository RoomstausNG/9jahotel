<?php

namespace App\Livewire;

use App\Models\Finance;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ExpenditurePage extends Component
{
    public $amount, $status, $code, $financeable_id, $hotel_id, $description, $type = 'expenditure';
    use WithPagination, WithFileUploads;

    public $financeable_type = "App\Models\Expenditure";
    public $expenditure;
    public $perPage = 10;
    public $query = '';

    public $typeForm = false;
    public $expenditureForm = false;

    public $updateE = false;

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

    function confirmDelete(Finance $expenditure)
    {
        $this->expenditure = $expenditure;
        $this->confirm = true;
    }

    function AddExpenditure()
    {
        $data = $this->validate(
            [
                'amount' => ['required', 'numeric'],
                'financeable_id' => ['required', 'exists:expenditures,id'],
                'financeable_type' => ['nullable'],
                'description' => ['nullable'],
                'type' => ['nullable']
            ],
            ['financeable_id.required' => 'Expenditure Source is required']
        );

        $created = Finance::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Expenditure created and added to finance records successfully ',
                'title' => 'Expenditure Added'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function editExpenditure(Finance $expenditure)
    {
        $this->updateE = true;
        $this->expenditureForm = true;
        $this->expenditure = $expenditure;
        $this->cid = $expenditure->id;
        $this->code = $expenditure->code;
        $this->amount = $expenditure->amount;
        $this->hotel_id = $expenditure->hotel_id;
        $this->type = $expenditure->type;
        $this->financeable_id = $expenditure->financeable_id;
        $this->financeable_type = $expenditure->financeable_type;
        $this->description = $expenditure->description;
    }

    function updateExpenditure()
    {
        $this->updateE = true;
        $type = Finance::find($this->cid);
        $data = $this->validate([
            'amount' => ['required', 'numeric'],
            'financeable_id' => ['required', 'exists:expenditures,id'],
            'financeable_type' => ['nullable'],
            'description' => ['nullable'],
            'type' => ['nullable']
        ], ['financeable_id.required' => 'Finance Source is required']);

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Expenditure updated successfully.',
                'title' => 'Expenditure updated'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteExpenditure(Finance $expenditure)
    {
        $selectedUsers = $expenditure->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Expenditure removed successfully alongside bookings',
                'title' => 'Expenditure Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultipleType()
    {
        $selectedUsers = Finance::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Expenditure Type removed successfully',
                'title' => 'Multiple Room Type Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $expenditures = Finance::expenditure()
            ->where('code', 'LIKE', $term)
            ->with(['financeable'])
            ->paginate($this->perPage);
        return view('livewire.expenditure-page', compact(['expenditures']));
    }
}