<?php

namespace App\Livewire\Configuration;

use App\Models\Room;
use App\Models\Type;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class RoomPage extends Component
{
    public $name, $type, $price1, $images, $floor_id, $hotel_id, $price2, $deposit1, $deposit2, $weekend_price, $weekend_start_day, $weekend_end_day, $promo_price, $promo_start_day, $price, $capacity, $description, $image, $is_available = true, $bed_type, $type_id, $number, $status, $promo_end_day, $remarks;
    use WithPagination, WithFileUploads;
    public $room;
    public $perPage = 10;
    public $perPageRoom = 10;
    public $query = '';

    public $typeForm = false;
    public $roomForm = false;

    public $updateType = false;
    public $updateR = false;

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

    public function showForm()
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

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function confirmDelete(Room $room)
    {
        // dd($room);
        $this->room = $room;
        $this->confirm = true;
    }

    public function AddRoomType()
    {
        $data = $this->validate([
            'name' => ['required'],
            'price1' => ['required', 'numeric'],
            'price2' => ['required', 'numeric'],
            'deposit1' => ['required', 'numeric'],
            'deposit2' => ['required', 'numeric'],
            'promo_price' => ['nullable', 'numeric'],
            'weekend_price' => ['nullable', 'numeric'],
            'weekend_start_day' => ['nullable'],
            'weekend_end_day' => ['nullable'],
            'promo_start_day' => ['nullable', 'date'],
            'promo_end_day' => ['nullable', 'date'],
            'remarks' => 'nullable',
        ]);

        $created = Type::create($data);

        // upload more pictures
        if ($created && $this->images) {
            // foreach ($this->images as $key => $image) {
            //     $created->addMedia($image->getRealPath())
            //         ->usingName(str_replace("/", '-', $this->name))
            //         ->usingFileName($this->name . "-" . $key + 1)
            //         ->toMediaCollection('rooms');
            // }
            foreach ($this->images as $key => $image) {
                // Retrieve the original file extension
                $extension = $image->getClientOriginalExtension();

                // Create a file name that includes the original name, a unique identifier, and the extension
                $fileName = Str::slug($this->name . "-" . ($key + 1)) . '.' . $extension;

                // Add the media to the model with the specified file name
                $created->addMedia($image->getRealPath())
                    ->usingName(str_replace("/", '-', $this->name))
                    ->usingFileName($fileName)
                    ->toMediaCollection('rooms');
            }
        }

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Room type/category created successfully, you can now add rooms to it',
                'title' => 'Room Type Created',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function editRoomType(Type $type)
    {
        $this->updateType = true;
        $this->typeForm = true;
        $this->type = $type;
        $this->cid = $type->id;
        $this->name = $type->name;
        $this->price1 = $type->price1;
        $this->price2 = $type->price2;
        $this->deposit2 = $type->deposit2;
        $this->deposit1 = $type->deposit1;
        $this->promo_price = $type->promo_price;
        $this->promo_start_day = $type->promo_start_day;
        $this->promo_end_day = $type->promo_end_day;
        $this->weekend_end_day = $type->weekend_end_day;
        $this->weekend_start_day = $type->weekend_start_day;
        $this->weekend_price = $type->weekend_price;
        $this->remarks = $type->remarks;
    }

    public function updateRoomT()
    {
        $this->updateType = true;
        $type = Type::find($this->cid);
        $data = $this->validate([
            'name' => ['required'],
            'price1' => ['required', 'numeric'],
            'price2' => ['required', 'numeric'],
            'deposit1' => ['required', 'numeric'],
            'deposit2' => ['required', 'numeric'],
            'promo_price' => ['nullable', 'numeric'],
            'weekend_price' => ['nullable', 'numeric'],
            'weekend_start_day' => ['nullable'],
            'weekend_end_day' => ['nullable'],
            'promo_start_day' => ['nullable'],
            'promo_end_day' => ['nullable'],
            'remarks' => 'nullable',
        ]);

        $updated = $type->update($data);

        // upload more pictures
        if ($updated && $this->images) {
            foreach ($this->images as $key => $image) {
                // Retrieve the original file extension
                $extension = $image->getClientOriginalExtension();

                // Create a file name that includes the original name, a unique identifier, and the extension
                $fileName = Str::slug($this->name . "-" . ($key + 1)) . '.' . $extension;

                // Add the media to the model with the specified file name
                $type->addMedia($image->getRealPath())
                    ->usingName(str_replace("/", '-', $this->name))
                    ->usingFileName($fileName)
                    ->toMediaCollection('rooms');
            }
        }

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Room type/category updated successfully.',
                'title' => 'Room Type updated',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function deleteType(Type $type)
    {
        $selectedUsers = $type->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Room type/category removed successfully alongside bookings',
                'title' => 'Category Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function AddRoom()
    {
        $data = $this->validate([
            'number' => ['required'],
            'price' => ['nullable', 'numeric'],
            'type_id' => ['required', 'exists:types,id'],
            'floor_id' => ['required', 'exists:floors,id'],
            'capacity' => ['required', 'numeric'],
            'bed_type' => ['required'],
            'status' => ['required'],
            'description' => ['nullable'],
            'image' => ['nullable', 'image', 'max:512'],
            'is_available' => 'nullable',
        ]);

        $created = Room::create($data);

        if ($created) {
            $this->reset();
            $details = [
                'text' => 'Room created successfully, you can now add bookings to it',
                'title' => 'Room Added',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function editRoom(Room $room)
    {
        $this->updateR = true;
        $this->roomForm = true;
        $this->room = $room;
        $this->cid = $room->id;
        $this->number = $room->number;
        $this->price = $room->price;
        $this->hotel_id = $room->hotel_id;
        $this->type_id = $room->type_id;
        $this->floor_id = $room->floor_id;
        $this->capacity = $room->capacity;
        $this->status = $room->status;
        $this->bed_type = $room->bed_type;
        $this->is_available = $room->is_available;
        $this->description = $room->description;
        $this->remarks = $room->remarks;
    }

    public function updateRoom()
    {
        $this->updateR = true;
        $type = Room::find($this->cid);
        $data = $this->validate([
            'number' => ['required'],
            'price' => ['nullable', 'numeric'],
            'type_id' => ['required', 'exists:types,id'],
            'floor_id' => ['required', 'exists:floors,id'],
            'capacity' => ['required', 'numeric'],
            'bed_type' => ['required'],
            'status' => ['required'],
            'description' => ['nullable'],
            'image' => ['nullable', 'image', 'max:512'],
            'is_available' => 'nullable',
        ]);

        $updated = $type->update($data);

        if ($updated) {
            $this->reset();
            $details = [
                'text' => 'Room updated successfully.',
                'title' => 'Room updated',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function deleteRoom(Room $room)
    {
        $selectedUsers = $room->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Room removed successfully alongside bookings',
                'title' => 'Room Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function deleteMultipleType()
    {
        $selectedUsers = Type::whereKey($this->checked)->delete();
        if ($selectedUsers) {
            $this->reset();
            $details = [
                'text' => 'Room Type removed successfully',
                'title' => 'Multiple Room Type Deleted',
            ];
            return $this->dispatch('swal:success', details: sweetAlert(...$details));
        }
    }

    public function render()
    {
        $term = "%" . $this->query . "%";
        $roomType = Type::where('name', 'LIKE', trim($term))
            ->paginate($this->perPage);
        $rooms = Room::with(['floor', 'type'])
            ->where('number', 'LIKE', trim($term))
            ->orwhere('bed_type', 'LIKE', trim($term))
            ->orwhere('status', 'LIKE', trim($term))
            ->orWhereHas('type', fn (Builder $query) => $query->where('name', 'LIKE', trim($term)))
            ->orWhereHas('floor', fn (Builder $query) => $query->where('name', 'LIKE', trim($term)))
            ->paginate($this->perPageRoom);
        return view('livewire.configuration.room-page', compact(['rooms', 'roomType']));
    }
}
