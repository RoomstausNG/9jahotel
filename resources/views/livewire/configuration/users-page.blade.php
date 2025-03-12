<div class="w-full p-4 space-y-6 overflow-y-scroll bg-white rounded-lg bb-20" x-data="{ active: 'hotel' }">
    <div class="flex items-center justify-between space-x-4 text-2xl ">
        <h1 class="font-medium">
            Users Account
        </h1>
        <a href="{{ route('configuration') }}" class="px-6 py-2 text-sm text-gray-100 bg-gray-700 rounded-md">Back</a>
    </div>
    <div class="w-full">
        <div class="flex items-center justify-between space-x-4 text-2xl font-medium">
            <a href="{{ route('users.create') }}" class="px-6 py-2 text-sm text-white rounded-md bg-primary">Add Staff</a>

            <x-form.search />
        </div>
    </div>

    <div class="w-full pb-8 overflow-y-auto">
        <div
            class="mb-4 overflow-x-auto overflow-y-scroll bg-white border border-gray-200 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <table id="" class="w-full overflow-x-auto overflow-y-auto rounded bord">
                <thead class="w-full overflow-x-auto rounded">
                    <tr class="w-full font-medium bg-gray-100 rounded">
                        {{-- <th class="p-4 font-medium text-left">
                            <input type="checkbox" value="" wire:model="all" class="select-input">
                        </th> --}}
                        {{-- <th class="p-4 font-medium text-left">No</th> --}}
                        <th class="p-4 font-medium text-left">Username</th>
                        <th class="p-4 font-medium text-left">FirstName</th>
                        <th class="p-4 font-medium text-left">Lastname</th>
                        <th class="p-4 font-medium text-left">Department</th>
                        <th class="p-4 font-medium text-left">Designation</th>
                        <th class="p-4 font-medium">System Role</th>
                        <th class="p-4 font-medium text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @if ($user->current_role == 'super-admin')
                            @continue
                        @endif
                        <tr wire:key='{{ $user->id }}' class="border-b even:bg-gray-50">

                            {{-- <td class="px-4 py-2">{{ $loop->iteration }}</td> --}}
                            <td class="px-4 py-2 text-sm uppercase break-keep nowrap">
                                {{ $user->username }}
                            </td>
                            <td class="px-4 py-2">
                                <span href="#">{{ $user->firstname }}</span>
                            </td>
                            <td class="px-4 py-2 ">
                                {{ $user->lastname }}
                            </td>
                            <td class="px-4 py-2 text-sm font-semibold uppercase">
                                {{ $user->department?->name }}
                            </td>
                            <td class="px-4 py-2 ">
                                {{ $user->designation }}
                            </td>
                            <td class="px-4 py-2 text-sm text-center capitalize">
                                {{ str_replace('-', ' ', $user->current_role) }}</td>
                            <td class="px-4 py-2">
                                <p class="flex items-center space-x-2 text-sm">

                                    <a href="{{ route('salary', $user->username) }}"
                                        wire:click="editUser({{ $user->id }})" data-tooltip-target="edit"
                                        data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-green-500 border border-green-500 rounded-md">
                                        <i class="mr-1 fa-solid fa-money-bill-1"></i> Salary
                                    </a>

                                    <a href="{{ route('users.profile', $user->username) }}"
                                        wire:click="editUser({{ $user->id }})" data-tooltip-target="edit"
                                        data-tooltip-placement="left"
                                        class="flex items-center justify-center px-2 py-1 space-x-2 text-blue-600 border border-blue-600 rounded-md">
                                        <i class="mr-1 fa-solid fa-pen-to-square"></i> Edit
                                    </a>

                                    @if (!$user->hasRole(['admin', 'super-admin']))
                                        <button wire:click="changeStatus({{ $user->id }})"
                                            wire:confirm="Are you sure you want to perform this action?"
                                            data-tooltip-target="status" data-tooltip-placement="left"
                                            class="border flex items-center justify-center space-x-2 {{ $user->can_login ? 'text-red-600 border-red-600' : 'text-green-600 border-green-600' }} rounded-md px-2 py-1">
                                            @if ($user->can_login)
                                                <i class="fa-solid fa-times"></i>
                                            @else
                                                <i class="fa-solid fa-check"></i>
                                            @endif
                                            <span>{{ $user->can_login ? 'Disable' : 'Enable' }}</span>
                                        </button>
                                        <button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this staff?"
                                            data-tooltip-target="delete" data-tooltip-placement="left"
                                            class="flex items-center justify-center px-2 py-1 space-x-2 text-red-600 border border-red-600 rounded-md">
                                            <i class="fa-solid fa-trash"></i>
                                            <span>Delete</span>
                                        </button>
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center" colspan="8">No data...</td>
                        </tr>
                    @endforelse
                    {{-- edit tooltip --}}
                    <x-tooltip id="edit" content="Edit user data" />

                    {{-- edit tooltip --}}
                    <x-tooltip id="status" content="Change Status" />
                    <x-tooltip id="delete" content="Delete Staff" />
                </tbody>
            </table>
            <div class="px-4">
                <p class="my-2">
                    {{ $users->links() }}
                </p>
            </div>
        </div>
    </div>


</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('swal:success', (event) => {
                Swal.fire(event.details)
                setTimeout(() => {
                    location.reload();
                }, 5000);
            });
        });
    </script>
@endpush
