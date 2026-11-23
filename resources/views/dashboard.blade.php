<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-end items-center gap-2">
            <a href="{{ route('laundry-items.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-xs transition">
                + Kelola Layanan
            </a>
            <a href="{{ route('customer.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-xs transition">
                Halaman Kasir (POS)
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-5 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r shadow-xs text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-700 rounded-r shadow-xs text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Tabel Rekap & Kontrol Pesanan -->
            <div class="bg-white overflow-hidden shadow-xs sm:rounded-lg border border-gray-200">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-sm">Daftar Semua Pesanan</h3>
                    <span class="text-xs text-gray-500">{{ $orders->count() }} data</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse">
                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="p-3 border-b whitespace-nowrap"># ID</th>
                                <th class="p-3 border-b whitespace-nowrap">Pelanggan</th>
                                <th class="p-3 border-b whitespace-nowrap">Kasir</th>
                                <th class="p-3 border-b whitespace-nowrap">Waktu</th>
                                <th class="p-3 border-b min-w-[240px]">Rincian Item</th>
                                <th class="p-3 border-b whitespace-nowrap">Total</th>
                                <th class="p-3 border-b whitespace-nowrap">Status</th>
                                <th class="p-3 border-b text-center whitespace-nowrap">Aksi Status</th>
                                <th class="p-3 border-b text-center whitespace-nowrap">Struk (Backend)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($orders as $order)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="p-3 font-mono font-bold text-gray-700 whitespace-nowrap">#{{ $order->id }}</td>
                                    <td class="p-3 whitespace-nowrap font-medium text-gray-900">
                                        {{ $order->customer_name }}
                                    </td>
                                    <td class="p-3 whitespace-nowrap text-xs text-gray-600">
                                        {{ $order->user->name ?? 'Kasir' }}
                                    </td>
                                    <td class="p-3 text-gray-600 whitespace-nowrap text-xs">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="p-3">
                                        <div class="space-y-1">
                                            @forelse($order->orderDetails as $detail)
                                                <div class="flex items-center justify-between text-xs bg-gray-50 p-1 rounded border border-gray-100">
                                                    <div>
                                                        <span class="font-semibold text-gray-800">{{ $detail->laundryItem->name ?? 'Layanan' }}</span>
                                                        <span class="text-gray-500 ml-1">({{ $detail->quantity }} {{ $detail->laundryItem->unit ?? 'satuan' }})</span>
                                                    </div>
                                                    <span class="font-medium text-gray-700 ml-2 whitespace-nowrap">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                                </div>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">Tidak ada item</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="p-3 font-bold text-indigo-700 whitespace-nowrap text-sm">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        @if(strtolower($order->status) === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                                PENDING
                                            </span>
                                        @elseif(strtolower($order->status) === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                APPROVED
                                            </span>
                                        @elseif(strtolower($order->status) === 'declined')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">
                                                DECLINED
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" class="text-xs font-semibold border-gray-300 rounded-md py-1 px-2 bg-white shadow-xs cursor-pointer hover:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                <option value="pending" {{ strtolower($order->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ strtolower($order->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="declined" {{ strtolower($order->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <a href="{{ route('admin.orders.viewStruk', $order->id) }}" target="_blank" class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded transition">
                                            Buka Struk .txt
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-8 text-center text-gray-400 text-sm">
                                        Belum ada pesanan laundry yang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
