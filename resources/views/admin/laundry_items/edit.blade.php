<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Layanan Laundry') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui rincian tarif dan deskripsi layanan laundry.</p>
            </div>
            <a href="{{ route('laundry-items.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md font-semibold text-xs uppercase tracking-widest transition">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($errors->any())
            <div class="mb-5 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded-r text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('laundry-items.update', $laundryItem->id) }}" method="POST" class="bg-white p-6 sm:p-8 rounded-lg shadow-sm border border-gray-200 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Nama Layanan</label>
                    <input type="text" name="name" value="{{ old('name', $laundryItem->name) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Kategori</label>
                    <select name="category" class="w-full border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $laundryItem->category) == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Satuan</label>
                    <select name="unit" class="w-full border-gray-300 rounded-md shadow-xs focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                        @foreach($units as $u)
                            <option value="{{ $u }}" {{ old('unit', $laundryItem->unit) == $u ? 'selected' : '' }}>
                                {{ $u }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-gray-700 mb-1">Tarif / Harga (Rp)</label>
                    <input type="number" name="price" value="{{ old('price', $laundryItem->price) }}" min="0" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                    <span class="text-xs text-gray-400 mt-0.5 block">Harga per satuan</span>
                </div>
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700 mb-1">Deskripsi Layanan (Opsional)</label>
                <textarea name="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" rows="3">{{ old('description', $laundryItem->description) }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('laundry-items.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-xs font-semibold text-gray-700 hover:bg-gray-50 uppercase tracking-widest transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold uppercase tracking-widest shadow-sm transition cursor-pointer">
                    Perbarui Layanan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
