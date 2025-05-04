<div>
    <form wire:submit.prevent="submit">
        {{ $this->form }}
        <button type="submit" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded">Update Profile</button>
    </form>
    @if (session()->has('success'))
        <div class="p-2 mt-4 text-green-700 bg-green-100 rounded">
            {{ session('success') }}
        </div>
    @endif
</div>
