<?php

namespace App\Livewire\Configuration;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use App\Mail\RegistrationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;

class UsersPage extends Component
{
    use WithPagination;
    public $user;
    public $perPage = 10;
    public $query = '';

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

    function confirmDelete(User $user)
    {
        // dd($User);
        $this->user = $user;
        $this->confirm = true;
    }

    function changeStatus(User $user)
    {
        // dd($user);

        if ($user->can_login) {
            $user->update(['can_login' => false]);
            $details = [
                'text' => 'User account disabled successfully',
                'title' => 'Account Disabled'
            ];
        } else {
            $done =  $user->update(['can_login' => true]);

            if ($done) {
                // send email to people
                Mail::to($user)->later(now()->addSeconds(2), new RegistrationEmail($user));
            }

            $details = [
                'text' => 'User account has been enable successfully',
                'title' => 'Account Enabled'
            ];
        }


        return $this->dispatch('swal:success', details: sweetAlert(...$details));
    }

    function delete(User $user)
    {
        $deleted = $user->delete();

        if ($deleted) {
            $this->reset();
            $details = [
                'text' => 'User removed successfully',
                'title' => 'Staff Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    function deleteMultiple()
    {
        $selectedUsers = User::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Users removed successfully',
                'title' => 'Multiple Users Deleted'
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $users = User::myStaff($term)->with(['department:id,name'])
            ->paginate($this->perPage);
        return view('livewire.configuration.users-page', compact(['users']));
    }
}
