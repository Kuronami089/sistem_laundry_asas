<?php

namespace Tests\Feature;

use App\Models\LaundryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LaundrySystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_kasir_can_access_catalog_and_is_redirected_from_admin_dashboard()
    {
        $kasir = User::factory()->create([
            'role' => 'kasir',
        ]);

        // Access cashier page
        $response = $this->actingAs($kasir)->get('/');
        $response->assertStatus(200);

        // Access admin dashboard -> redirected to customer.index
        $response = $this->actingAs($kasir)->get('/dashboard');
        $response->assertRedirect(route('customer.index'));
    }

    public function test_admin_can_access_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Daftar Semua Pesanan');
    }

    public function test_admin_can_crud_laundry_items_without_image()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create
        $response = $this->actingAs($admin)->post(route('laundry-items.store'), [
            'name'        => 'Cuci Boneka Jumbo',
            'category'    => 'Komplit',
            'price'       => 20000,
            'unit'        => 'pcs',
            'description' => 'Cuci boneka ukuran jumbo dengan pengeringan maksimal.',
        ]);
        $response->assertRedirect(route('laundry-items.index'));
        $this->assertDatabaseHas('laundry_items', [
            'name'     => 'Cuci Boneka Jumbo',
            'category' => 'Komplit',
            'price'    => 20000,
            'unit'     => 'pcs',
        ]);

        $item = LaundryItem::where('name', 'Cuci Boneka Jumbo')->first();

        // Update
        $response = $this->actingAs($admin)->put(route('laundry-items.update', $item->id), [
            'name'        => 'Cuci Boneka Super Jumbo',
            'category'    => 'Komplit',
            'price'       => 25000,
            'unit'        => 'pcs',
            'description' => 'Cuci boneka super besar.',
        ]);
        $response->assertRedirect(route('laundry-items.index'));
        $this->assertDatabaseHas('laundry_items', [
            'id'       => $item->id,
            'name'     => 'Cuci Boneka Super Jumbo',
            'category' => 'Komplit',
            'price'    => 25000,
        ]);

        // Delete
        $response = $this->actingAs($admin)->delete(route('laundry-items.destroy', $item->id));
        $response->assertRedirect(route('laundry-items.index'));
        $this->assertDatabaseMissing('laundry_items', [
            'id' => $item->id,
        ]);
    }

    public function test_kasir_can_make_order_and_ascii_receipt_is_saved_in_backend()
    {
        $kasir = User::factory()->create([
            'name' => 'Budi Kasir',
            'role' => 'kasir',
        ]);

        $item1 = LaundryItem::create([
            'name'        => 'Cuci Kering',
            'category'    => 'Standar',
            'price'       => 6000,
            'unit'        => 'kg',
            'description' => 'Cuci kering biasa',
        ]);

        $item2 = LaundryItem::create([
            'name'        => 'Cuci Bed Cover',
            'category'    => 'Komplit',
            'price'       => 30000,
            'unit'        => 'pcs',
            'description' => 'Cuci bed cover',
        ]);

        // Kasir submits order directly without modal confirmation
        $response = $this->actingAs($kasir)->post(route('customer.checkout'), [
            'customer_name' => 'Ibu Siti',
            'items' => [
                $item1->id => 3,
                $item2->id => 1,
            ],
        ]);

        $expectedTotal = (6000 * 3) + (30000 * 1); // 18000 + 30000 = 48000
        $response->assertRedirect(route('customer.index'));
        $response->assertSessionHas('success', 'Pesanan berhasil dibuat!');

        $this->assertDatabaseHas('orders', [
            'user_id'       => $kasir->id,
            'customer_name' => 'Ibu Siti',
            'total_price'   => $expectedTotal,
            'status'        => 'pending',
        ]);

        $order = Order::first();
        $this->assertCount(2, $order->orderDetails);

        // Verify receipt is saved in backend file
        $receiptFile = storage_path("app/struk/struk_order_{$order->id}.txt");
        $this->assertTrue(File::exists($receiptFile));

        $receiptContent = File::get($receiptFile);
        $this->assertStringContainsString('LAUNDRY ASAS', $receiptContent);
        $this->assertStringContainsString('============================================', $receiptContent);
        $this->assertStringContainsString('--------------------------------------------', $receiptContent);
        $this->assertStringContainsString('No. Transaksi', $receiptContent);
        $this->assertStringContainsString('Ibu Siti', $receiptContent);
        $this->assertStringContainsString('TOTAL TAGIHAN', $receiptContent);

        // Admin updates status to approved
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order->id), [
            'status' => 'approved',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals('approved', $order->fresh()->status);

        // Admin can view the backend receipt
        $response = $this->actingAs($admin)->get(route('admin.orders.viewStruk', $order->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $this->assertStringContainsString('APPROVED', $response->getContent());
    }

    public function test_customer_name_is_required_when_making_order()
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $item = LaundryItem::create([
            'name'        => 'Cuci Kering',
            'category'    => 'Standar',
            'price'       => 6000,
            'unit'        => 'kg',
            'description' => 'Cuci kering',
        ]);

        $response = $this->actingAs($kasir)->post(route('customer.checkout'), [
            'customer_name' => '',
            'items' => [$item->id => 2],
        ]);

        $response->assertSessionHasErrors('customer_name');
    }
}
