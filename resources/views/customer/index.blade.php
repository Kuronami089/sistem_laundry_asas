<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Laundry - Laundry Asas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

    <!-- Header Minimalis & Responsif -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2 sm:gap-0">
            <div class="flex items-center gap-3">
                <span class="font-bold text-gray-900 text-lg tracking-tight">Laundry Asas</span>
            </div>

            <div class="flex items-center gap-4 text-xs sm:text-sm text-gray-600">
                <span>Halo, <strong class="text-gray-900">{{ Auth::user()->name }}</strong></span>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:underline font-semibold">
                        Dashboard Admin
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-rose-600 hover:underline font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Konten Kasir POS -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" x-data="{ activeFilter: 'all' }">
        
        <!-- Notifikasi Flash -->
        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-md text-xs sm:text-sm font-medium flex items-center justify-between">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-md text-xs sm:text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border border-rose-300 text-rose-800 rounded-md text-xs sm:text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.checkout') }}" method="POST">
            @csrf

            <!-- Bar Kontrol Kasir: Input Pelanggan & Filter Kategori Layanan -->
            <div class="bg-white p-4 sm:p-5 rounded-lg border border-gray-200 mb-6 shadow-xs space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pelanggan <span class="text-rose-500 font-bold">*</span></label>
                        <input
                            type="text"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            placeholder="Contoh: Ibu Rina, Bpk. Ahmad, dll."
                            class="w-full text-xs sm:text-sm border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500"
                            required
                        >
                    </div>

                    <!-- Sistem Filtering per Kategori Layanan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Filter Kategori Layanan</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                type="button"
                                @click="activeFilter = 'all'"
                                :class="activeFilter === 'all' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-1.5 rounded-md text-xs transition cursor-pointer"
                            >
                                Semua ({{ $laundryItems->count() }})
                            </button>

                            @foreach($categories as $cat)
                                <button
                                    type="button"
                                    @click="activeFilter = '{{ $cat }}'"
                                    :class="activeFilter === '{{ $cat }}' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3 py-1.5 rounded-md text-xs transition cursor-pointer"
                                >
                                    {{ $cat }} ({{ $laundryItems->where('category', $cat)->count() }})
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Layanan Laundry (Responsif: 1 Kolom Mobile, 2 Kolom Tablet, 3 Kolom Desktop) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @forelse($laundryItems as $item)
                    <div
                        x-show="activeFilter === 'all' || activeFilter === '{{ $item->category }}'"
                        class="bg-white rounded-lg border border-gray-200 p-4 sm:p-5 flex flex-col justify-between hover:border-indigo-300 transition-colors shadow-xs"
                    >
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                                    {{ $item->category }}
                                </span>
                                <span class="font-bold text-indigo-700 text-sm whitespace-nowrap">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                    <span class="text-xs text-gray-400 font-normal">/{{ $item->unit }}</span>
                                </span>
                            </div>

                            <h3 class="font-bold text-gray-900 text-base mb-1">{{ $item->name }}</h3>
                            <p class="text-xs text-gray-500 leading-relaxed">{{ $item->description ?? 'Layanan cuci bersih dan rapi.' }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">
                                Berat / Jumlah ({{ $item->unit }})
                            </label>
                            <div class="relative rounded-md shadow-xs">
                                <input
                                    type="number"
                                    name="items[{{ $item->id }}]"
                                    min="0"
                                    value="{{ old('items.' . $item->id, 0) }}"
                                    class="w-full text-xs sm:text-sm border-gray-300 rounded-md py-1.5 pl-3 pr-14 text-center font-bold text-gray-800 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-gray-400 font-medium">
                                    {{ $item->unit }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-lg border border-gray-200 p-8 text-center text-gray-400 text-xs sm:text-sm">
                        Belum ada layanan laundry yang terdaftar dalam sistem.
                    </div>
                @endforelse
            </div>

            <!-- Tombol Proses Transaksi Langsung -->
            @if($laundryItems->count() > 0)
                <div class="flex justify-end items-center mt-2">
                    <button
                        type="submit"
                        class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-8 py-2.5 rounded-md text-xs sm:text-sm tracking-wide shadow-xs transition cursor-pointer"
                    >
                        Pesan Sekarang
                    </button>
                </div>
            @endif
        </form>
    </main>

</body>
</html>
