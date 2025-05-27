<x-filament::page>
    <div class="min-h-screen flex flex-col items-center justify-center bg-white p-4">
        <img src="{{ asset('storage/logo-ssp-167x168.png') }}" class="h-20 w-20 mb-4" alt="Logo">
        <h1 class="text-2xl font-bold text-center mb-6">Portal PIBG SK Sri Petaling</h1>

        {{ $this->form }}

        <x-filament::button type="submit" form="authenticate" class="w-full mt-4">
            Log Masuk
        </x-filament::button>

        <p class="text-sm text-center mt-6 text-gray-500">
            Khas untuk Guru Kelas dan Pentadbir PIBG sahaja.
        </p>
    </div>
</x-filament::page>