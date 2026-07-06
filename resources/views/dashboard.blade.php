<x-app-layout>
    <x-slot name="header">
        <h4 class="text-white mb-0 fw-bold">
            <i class="fa-solid fa-chart-line text-info me-2"></i>{{ __('Dashboard') }}
        </h4>
    </x-slot>

    <div class="glass-card p-4 p-md-5">
        <h3 class="text-white fw-bold mb-3">Selamat Datang, {{ Auth::user()->name }}!</h3>
        <p class="text-secondary mb-0">
            Anda berhasil masuk ke platform **Global Supply Chain Risk Intelligence**. Halaman ini sedang dikonfigurasi untuk dashboard utama pemantauan risiko logistik rantai pasok global.
        </p>
    </div>
</x-app-layout>
