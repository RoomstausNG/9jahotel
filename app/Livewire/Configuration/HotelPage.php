<?php

namespace App\Livewire\Configuration;

use App\Models\Amenity;
use App\Models\Department;
use App\Models\Expenditure;
use App\Models\Hotel;
use App\Models\Revenue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Spatie\Permission\Models\Role;

class HotelPage extends Component
{
    use LivewireAlert, WithFileUploads;
    public ?array $checked = [];
    public $search;
    public Hotel $hotel;
    public $form = false;
    public $update = false;
    public $updateD = false;
    public $updateA = false;
    public $updateR = false;
    public $updateE = false;

    public $country_id, $amenity, $amen, $password, $current_password, $password_confirmation, $revenue, $departmentName, $department, $expenditure, $state_id, $source, $checked_out, $surcharge, $city_id, $address, $name, $email, $telephone, $primary_currency, $secondary_currency, $zip_code, $logo, $background_image, $service_charge, $primary_color, $secondary_color;

    public $perPage = 10;
    public $query = '';
    public $vat;

    public function mount()
    {
        $this->hotel = myHotel();
        $this->country_id = $this->hotel->city?->country_id;
        // $this->country_id = auth()->user()->country_id;
        $this->state_id = $this->hotel->city?->state_id;
        $this->city_id = $this->hotel->city_id;
        $this->address = $this->hotel->address;
        $this->name = $this->hotel->name;
        $this->email = $this->hotel->email;
        $this->telephone = $this->hotel->telephone;
        $this->primary_currency = $this->hotel->primary_currency;
        $this->secondary_currency = $this->hotel->secondary_currency;
        $this->zip_code = $this->hotel->zip_code;
        $this->vat = $this->hotel->vat;
        $this->service_charge = $this->hotel->service_charge;
        $this->surcharge = $this->hotel->surcharge;
        $this->checked_out = $this->hotel->checked_out;
        $this->primary_color = $this->hotel->primary_color;
        $this->secondary_color = $this->hotel->secondary_color;
    }

    public function updateHotel()
    {
        $data = $this->validate([
            'name' => ['required'],
            'email' => ['required', 'email', Rule::unique('hotels', 'email')->ignore($this->hotel->id)],
            'country_id' => ['required'],
            'state_id' => ['nullable', 'required_unless:country_id,null'],
            'city_id' => ['nullable', 'required_unless:state_id,null'],
            'address' => ['required'],
            'telephone' => ['required', 'starts_with:090,091,080,091,081,070', 'digits:11', Rule::unique('hotels')->ignore($this->hotel->id)],
            'primary_currency' => ['required', 'not_in:null'],
            'secondary_currency' => ['required', 'not_in:null'],
            'zip_code' => 'nullable',
            'logo' => ['nullable', 'image', 'max:256'],
            'background_image' => ['nullable', 'image', 'max:1024'],
            'service_charge' => ['nullable', 'numeric', 'max:100'],
            'surcharge' => ['nullable', 'numeric', 'max:100'],
            'vat' => ['nullable'],
            'checked_out' => 'nullable',
            'primary_color' => 'nullable',

        ]);

        // dd($data);
        if ($this->logo) {
            $url = $this->logo->store('logo', 'public');
            $data['logo'] = $url;
        } else {
            $data['logo'] = $this->hotel->logo;
        }
        if ($this->background_image) {
            $url = $this->background_image->store('background', 'public');
            $data['background_image'] = $url;
        } else {
            $data['background_image'] = $this->hotel->background_image;
        }

        $submitted = $this->hotel->update($data);

        if ($submitted) {
            $details = [
                'text' => 'Hotel information updated successfully',
                'title' => 'Saved',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function delete()
    {
        $deleted = $this->Department->delete();

        if ($deleted) {
            $this->reset();
            $details = [
                'text' => 'Department deleted successfully',
                'title' => 'Department Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function updatePassword()
    {

        $data = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed'],
        ]);

        $updated = auth()->user()->update([
            'password' => Hash::make($data['password'])
        ]);

        if ($updated) {
            $this->reset('source', 'password', 'current_password', 'password_confirmation', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Password changed successfully!'); // 👈
        }
    }
    public function updateRevenue()
    {
        $revenue = Revenue::find($this->revenue->id);
        $data = $this->validate([
            'source' => ['required'],
        ]);
        $updated = $this->revenue->update($data);

        if ($updated) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Revenue updated successfully!'); // 👈
        }
    }

    public function saveRevenue()
    {
        $data = $this->validate([
            'source' => ['required'],
        ]);
        $saved = Revenue::create($data);

        if ($saved) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source added successfully');
            $this->alert('success', 'Revenue source added successfully!'); // 👈
        }
    }

    public function editRevenue(Revenue $revenue)
    {
        $this->form = true;
        $this->updateR = true;
        $this->revenue = $revenue;
        $this->source = $revenue->source;
    }

    public function deleteRevenue(Revenue $revenue)
    {
        $deleted = $revenue->delete();
        if ($deleted) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source deleted successfully');
            $this->alert('success', 'Revenue source deleted successfully!'); // 👈
        }
    }

    public function updateAmenity()
    {
        $revenue = Revenue::find($this->amenity->id);
        $data = $this->validate([
            'amen' => ['required'],
        ]);
        $updated = $this->amenity->update(['name' => $data['amen']]);

        if ($updated) {
            $this->reset('amen', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Amenity updated successfully!'); // 👈
        }
    }

    public function saveAmenity()
    {
        $data = $this->validate([
            'amen' => ['required'],
        ]);
        $saved = Amenity::create(['name' => $data['amen']]);

        if ($saved) {
            $this->reset('amen', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source added successfully');
            $this->alert('success', 'Amenity added successfully!'); // 👈
        }
    }

    public function editAmenity(Amenity $amenity)
    {
        $this->form = true;
        $this->updateA = true;
        $this->amenity = $amenity;
        $this->amen = $amenity->name;
    }

    public function deleteAmenity(Amenity $amenity)
    {
        $deleted = $amenity->delete();
        if ($deleted) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source deleted successfully');
            $this->alert('success', 'Amenity source deleted successfully!'); // 👈
        }
    }

    public function deleteMultipleRevenue()
    {
        $deleted = Revenue::whereIn('id', $this->checked)->delete();
        if ($deleted) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source deleted successfully');
            $this->alert('success', 'Revenues deleted successfully!'); // 👈
        }
    }

    public function editExpenditure(Expenditure $expenditure)
    {
        $this->form = true;
        $this->updateE = true;
        $this->expenditure = $expenditure;
        $this->source = $expenditure->source;
    }

    public function saveExpenditure()
    {
        $data = $this->validate([
            'source' => ['required'],
        ]);
        $saved = Expenditure::create($data);

        if ($saved) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            // session()->flash('success', 'Revenue source added successfully');
            $this->alert('success', 'Expenditure head added successfully!'); // 👈
        }
    }

    public function updateExpenditure()
    {
        $expenditure = Expenditure::find($this->expenditure->id);
        $data = $this->validate([
            'source' => ['required', Rule::unique('expenditures')->ignore($this->expenditure->id)],
        ]);
        $updated = $expenditure->update($data);

        if ($updated) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Expenditure updated successfully!'); // 👈
        }
    }

    public function deleteExpenditure(Expenditure $expenditure)
    {
        $deleted = $expenditure->delete();

        if ($deleted) {
            $this->reset('source', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Expenditure deleted successfully!'); // 👈
        }
    }

    public function addDepartment()
    {
        $this->form = true;
        $this->name = null;
    }

    public function updateDepartment()
    {

        $this->updateD = true;
        $department = Department::find($this->department->id);
        $data = $this->validate([
            'departmentName' => ['required', Rule::unique('departments', 'name')->ignore($this->department->id)],
        ]);
        $updated = $department->update(['name' => $data['departmentName']]);

        if ($updated) {
            $this->reset('departmentName', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Department updated successfully!'); // 👈
        }
    }

    public function saveDepartment()
    {
        $data = $this->validate([
            'departmentName' => ['required'],
        ]);
        $saved = Department::create(['name' => $this->departmentName]);

        if ($saved) {
            $this->reset('departmentName', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Department added successfully!'); // 👈
        }
    }

    public function editDepartment(Department $department)
    {
        $this->form = true;
        $this->updateD = true;
        $this->departmentName = $department->name;
        $this->department = $department;
    }

    public function deleteDepartment(Department $department)
    {
        $deleted = $department->delete();

        if ($deleted) {
            $this->reset('departmentName', 'query', 'search', 'update', 'updateR', 'updateD', 'updateE', 'checked');
            $this->alert('success', 'Department deleted successfully!');
        }
    }

    public function render()
    {
        $revenues = Revenue::where('source', 'LIKE', "%" . $this->query . "%")->get();
        $expenditures = Expenditure::where('source', 'LIKE', "%" . $this->query . "%")->get();
        $departments = Department::where('name', 'LIKE', "%" . $this->query . "%")->get();
        $amenities = Amenity::where('name', 'LIKE', "%" . $this->query . "%")->get();
        $roles = Role::simplePaginate($this->perPage);
        return view('livewire.configuration.hotel-page', compact(['revenues', 'expenditures', 'departments', 'roles', 'amenities']));
    }
}