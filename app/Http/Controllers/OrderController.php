<?php

namespace App\Http\Controllers;

use App\Models\LaundryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class OrderController extends Controller
{
    // Halaman utama kasir: Pilih layanan dengan filter & buat pesanan (Wajib Login)
    public function index()
    {
        $laundryItems = LaundryItem::orderBy('category')->orderBy('name')->get();
        $categories = LaundryItem::select('category')->distinct()->pluck('category');

        return view('customer.index', compact('laundryItems', 'categories'));
    }

    // Proses pembuatan pesanan oleh kasir langsung tanpa konfirmasi modal
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
        ], [
            'customer_name.required' => 'Nama pelanggan wajib diisi!',
        ]);

        $orderedItems = [];

        if ($request->filled('laundry_item_id') && (int) $request->input('quantity', 0) > 0) {
            $orderedItems[(int) $request->laundry_item_id] = (int) $request->quantity;
        } elseif ($request->has('items') && is_array($request->items)) {
            $orderedItems = array_filter($request->items, fn ($qty) => (int) $qty > 0);
        }

        if (empty($orderedItems)) {
            return back()->withInput()->with('error', 'Silakan pilih minimal satu layanan laundry dan tentukan berat/jumlah lebih dari 0!');
        }

        $customerName = trim($request->input('customer_name'));

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'       => Auth::id(),
                'customer_name' => $customerName,
                'total_price'   => 0,
                'status'        => 'pending',
            ]);

            $totalPrice = 0;
            foreach ($orderedItems as $itemId => $quantity) {
                $laundryItem = LaundryItem::findOrFail($itemId);
                $qty = (int) $quantity;
                $subtotal = $laundryItem->price * $qty;
                $totalPrice += $subtotal;

                OrderDetail::create([
                    'order_id'        => $order->id,
                    'laundry_item_id' => $laundryItem->id,
                    'quantity'        => $qty,
                    'subtotal'        => $subtotal,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);

            // Cetak struk ke backend (format strip/simbol teks keyboard)
            $strukPath = $this->saveStrukToBackend($order);
            $order->update(['receipt_path' => $strukPath]);

            DB::commit();

            return redirect()->route('customer.index')
                ->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    // Dashboard admin: Rekap & kontrol seluruh pesanan laundry dan detailnya
    public function adminDashboard()
    {
        $orders = Order::with(['user', 'orderDetails.laundryItem'])->latest()->get();

        return view('dashboard', compact('orders'));
    }

    // Ubah status pesanan oleh admin (pending / approved / declined)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,declined',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        // Perbarui struk di backend dengan status terbaru
        $this->saveStrukToBackend($order);

        return back()->with('success', 'Status pesanan #' . $order->id . ' berhasil diubah menjadi ' . strtoupper($order->status) . '!');
    }

    // Melihat isi struk yang tersimpan di backend (khusus admin)
    public function viewStrukBackend($id)
    {
        $order = Order::with(['user', 'orderDetails.laundryItem'])->findOrFail($id);

        $strukDir = storage_path('app/struk');
        $fileName = "struk_order_{$order->id}.txt";
        $fullPath = $strukDir . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($fullPath)) {
            $this->saveStrukToBackend($order);
        }

        $content = File::get($fullPath);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    // Helper: Generate dan simpan struk ke backend menggunakan simbol & karakter keyboard
    private function saveStrukToBackend(Order $order): string
    {
        $order->loadMissing(['user', 'orderDetails.laundryItem']);

        $width = 44;
        $sepDouble = str_repeat('=', $width);
        $sepSingle = str_repeat('-', $width);

        $center = function ($text) use ($width) {
            $len = strlen($text);
            if ($len >= $width) return substr($text, 0, $width);
            $pad = (int) floor(($width - $len) / 2);
            return str_repeat(' ', $pad) . $text;
        };

        $lines = [];
        $lines[] = $sepDouble;
        $lines[] = $center('LAUNDRY ASAS');
        $lines[] = $center('Layanan Cuci & Setrika Bersih Wangi');
        $lines[] = $center('Jl. Asas No. 123 | WA: 0812-3456-7890');
        $lines[] = $sepDouble;
        $lines[] = sprintf("%-14s: #ORD-%05d", "No. Transaksi", $order->id);
        $lines[] = sprintf("%-14s: %s", "Waktu Transaksi", $order->created_at ? $order->created_at->format('d/m/Y H:i') : date('d/m/Y H:i'));
        $lines[] = sprintf("%-14s: %s", "Kasir", $order->user->name ?? 'Kasir');
        $lines[] = sprintf("%-14s: %s", "Pelanggan", $order->customer_name);
        $lines[] = $sepSingle;
        $lines[] = sprintf("%-18s %-7s %-8s %-8s", "LAYANAN", "QTY", "HARGA", "SUBTOTAL");
        $lines[] = $sepSingle;

        foreach ($order->orderDetails as $detail) {
            $name = $detail->laundryItem->name ?? 'Layanan';
            if (strlen($name) > 17) {
                $name = substr($name, 0, 16) . '.';
            }
            $qty = $detail->quantity . ' ' . ($detail->laundryItem->unit ?? '');
            $price = number_format($detail->laundryItem->price ?? 0, 0, ',', '.');
            $subtotal = number_format($detail->subtotal, 0, ',', '.');

            $lines[] = sprintf("%-18s %-7s %8s %9s", $name, $qty, $price, $subtotal);
        }

        $lines[] = $sepSingle;
        $lines[] = sprintf("%-24s: Rp %14s", "TOTAL TAGIHAN", number_format($order->total_price, 0, ',', '.'));
        $lines[] = sprintf("%-24s: %17s", "STATUS PESANAN", strtoupper($order->status));
        $lines[] = $sepDouble;
        $lines[] = $center('TERIMA KASIH ATAS KUNJUNGAN ANDA');
        $lines[] = $center('Struk ini adalah bukti resmi');
        $lines[] = $center('pengambilan pakaian laundry.');
        $lines[] = $sepDouble;

        $content = implode("\r\n", $lines) . "\r\n";

        $strukDir = storage_path('app/struk');
        if (!File::isDirectory($strukDir)) {
            File::makeDirectory($strukDir, 0755, true);
        }

        $fileName = "struk_order_{$order->id}.txt";
        $fullPath = $strukDir . DIRECTORY_SEPARATOR . $fileName;
        File::put($fullPath, $content);

        return "storage/app/struk/{$fileName}";
    }
}
