<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Daftar Layanan Laundry') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola paket dan tarif layanan cuci, setrika, atau satuan.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md font-semibold text-xs uppercase tracking-widest transition w-fit">
                ← Kembali ke Rekap
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <a href="{{ route('laundry-items.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-semibold text-xs uppercase tracking-wider shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Layanan Baru
            </a>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r shadow-sm text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left border-collapse">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="p-4 border-b whitespace-nowrap"># ID</th>
                            <th class="p-4 border-b whitespace-nowrap">Kategori</th>
                            <th class="p-4 border-b whitespace-nowrap">Nama Layanan</th>
                            <th class="p-4 border-b whitespace-nowrap">Satuan</th>
                            <th class="p-4 border-b whitespace-nowrap">Tarif (Rp)</th>
                            <th class="p-4 border-b">Deskripsi</th>
                            <th class="p-4 border-b text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="p-4 font-mono text-gray-500 font-bold whitespace-nowrap">#{{ $item->id }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700">
                                        {{ $item->category }}
                                    </span>
                                </td>
                                <td class="p-4 font-semibold text-gray-900 whitespace-nowrap">{{ $item->name }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        /{{ $item->unit }}
                                    </span>
                                </td>
                                <td class="p-4 font-bold text-indigo-700 whitespace-nowrap">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-gray-600 max-w-md text-xs leading-relaxed">
                                    {{ $item->description }}
                                </td>
                                <td class="p-4 whitespace-nowrap text-center space-x-2">
                                    <a href="{{ route('laundry-items.edit', $item->id) }}" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('laundry-items.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')" class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 text-sm">
                                    Belum ada layanan laundry yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</x-app-layout>
