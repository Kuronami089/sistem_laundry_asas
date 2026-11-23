<?php

namespace App\Http\Controllers;

use App\Models\LaundryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaundryItemController extends Controller
{
    public function index()
    {
        $items = LaundryItem::latest()->paginate(10);
        return view('admin.laundry_items.index', compact('items'));
    }

    public function create()
    {
        $categories = LaundryItem::CATEGORIES;
        $units = LaundryItem::UNITS;
        return view('admin.laundry_items.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => ['required', 'string', Rule::in(LaundryItem::CATEGORIES)],
            'price'       => 'required|integer|min:0',
            'unit'        => ['required', 'string', Rule::in(LaundryItem::UNITS)],
            'description' => 'nullable|string',
        ]);

        LaundryItem::create([
            'name'        => $request->name,
            'category'    => $request->category,
            'price'       => $request->price,
            'unit'        => $request->unit,
            'description' => $request->description,
        ]);

        return redirect()->route('laundry-items.index')
            ->with('success', 'Layanan laundry berhasil ditambahkan!');
    }

    public function show(LaundryItem $laundryItem)
    {
        return redirect()->route('laundry-items.index');
    }

    public function edit(LaundryItem $laundryItem)
    {
        $categories = LaundryItem::CATEGORIES;
        $units = LaundryItem::UNITS;
        return view('admin.laundry_items.edit', compact('laundryItem', 'categories', 'units'));
    }

    public function update(Request $request, LaundryItem $laundryItem)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => ['required', 'string', Rule::in(LaundryItem::CATEGORIES)],
            'price'       => 'required|integer|min:0',
            'unit'        => ['required', 'string', Rule::in(LaundryItem::UNITS)],
            'description' => 'nullable|string',
        ]);

        $laundryItem->update([
            'name'        => $request->name,
            'category'    => $request->category,
            'price'       => $request->price,
            'unit'        => $request->unit,
            'description' => $request->description,
        ]);

        return redirect()->route('laundry-items.index')
            ->with('success', 'Layanan laundry berhasil diperbarui!');
    }

    public function destroy(LaundryItem $laundryItem)
    {
        $laundryItem->delete();
        return redirect()->route('laundry-items.index')
            ->with('success', 'Layanan laundry berhasil dihapus!');
    }
}
