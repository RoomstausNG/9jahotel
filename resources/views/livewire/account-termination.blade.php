<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-center text-red-600">Account Termination</h2>
        <p class="mb-6 text-gray-700 text-center">Request to delete your account. Enter your registered email below. This action is irreversible.</p>
        <form wire:submit.prevent="terminateAccount" class="space-y-4">
            <input type="email" wire:model="email" placeholder="Enter your email" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-400" required>
            <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700 transition">Delete My Account</button>
        </form>
        @if($showConfirmation)
            <div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <strong>Confirmation:</strong> {{ $message }}
            </div>
        @elseif($message)
            <div class="mt-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                <strong>Notice:</strong> {{ $message }}
            </div>
        @endif
    </div>
</div>
<script>
    window.addEventListener('swal:success', event => {
        alert(event.detail.text);
    });
</script>
