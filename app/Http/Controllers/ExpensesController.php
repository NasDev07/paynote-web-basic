<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Expenses;
use App\Models\Categories;

class ExpensesController extends Controller
{
    // Halaman List Pengeluaran (method lama)
    public function index(Request $request)
    {
        $query = Expenses::with('category');

        // Filter berdasarkan kategori
        if ($request->filled('category')) {
            $query->where('id_category', $request->category);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(10);
        $categories = Categories::getAll();
        
        return view('dashboard.expenses.list', compact('expenses', 'categories'));
    }

    // Halaman Tambah Pengeluaran (ubah dari addPage ke create untuk konsistensi)
    public function create()
    {
        $categories = Categories::getAll();
        return view('dashboard.expenses.create', compact('categories'));
    }

    // Method lama addPage() untuk kompatibilitas backward
    public function addPage()
    {
        return $this->create();
    }

    // Tambah Pengeluaran (ubah dari insert ke store)
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'description' => 'required|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
            'id_category' => 'required|exists:categories,id_category',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // Max 5MB
        ], [
            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah minimal 0.01',
            'amount.max' => 'Jumlah terlalu besar',
            'description.required' => 'Deskripsi harus diisi',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
            'id_category.required' => 'Kategori harus dipilih',
            'id_category.exists' => 'Kategori tidak valid',
            'receipt_image.image' => 'File harus berupa gambar',
            'receipt_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'receipt_image.max' => 'Ukuran gambar maksimal 5MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = [
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'id_category' => $request->id_category,
                'created_at' => now()
            ];

            // Handle upload gambar
            if ($request->hasFile('receipt_image')) {
                $file = $request->file('receipt_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                // Simpan ke storage/app/public/receipts
                $file->storeAs('receipts', $filename, 'public');
                $data['receipt_image'] = $filename;
            }

            // Insert data
            $expense = Expenses::insert($data);

            if ($expense) {
                return redirect()->route('expenses')
                    ->with('success', 'Pengeluaran "' . $request->description . '" berhasil ditambahkan');
            } else {
                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan saat menambahkan data')
                    ->withInput();
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    // Method lama insert() untuk kompatibilitas backward
    public function insert(Request $request)
    {
        return $this->store($request);
    }

    // Halaman Detail Pengeluaran (BARU)
    public function show($id)
    {
        $expense = Expenses::getById($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        return view('dashboard.expenses.show', compact('expense'));
    }

    // Halaman Edit Pengeluaran (ubah dari editPage ke edit)
    public function edit($id)
    {
        $expense = Expenses::getById($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        $categories = Categories::getAll();
        return view('dashboard.expenses.edit', compact('expense', 'categories'));
    }

    // Method lama editPage() untuk kompatibilitas backward
    public function editPage($id)
    {
        return $this->edit($id);
    }

    // Update Pengeluaran
    public function update(Request $request, $id)
    {
        $expense = Expenses::find($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'description' => 'required|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
            'id_category' => 'required|exists:categories,id_category',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
        ], [
            'amount.required' => 'Jumlah harus diisi',
            'amount.numeric' => 'Jumlah harus berupa angka',
            'amount.min' => 'Jumlah minimal 0.01',
            'amount.max' => 'Jumlah terlalu besar',
            'description.required' => 'Deskripsi harus diisi',
            'description.max' => 'Deskripsi maksimal 1000 karakter',
            'date.required' => 'Tanggal harus diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',
            'id_category.required' => 'Kategori harus dipilih',
            'id_category.exists' => 'Kategori tidak valid',
            'receipt_image.image' => 'File harus berupa gambar',
            'receipt_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'receipt_image.max' => 'Ukuran gambar maksimal 5MB'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = [
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'id_category' => $request->id_category,
                'updated_at' => now()
            ];

            // Handle upload gambar baru
            if ($request->hasFile('receipt_image')) {
                // Hapus gambar lama jika ada
                if ($expense->receipt_image) {
                    Storage::disk('public')->delete('receipts/' . $expense->receipt_image);
                }

                $file = $request->file('receipt_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                $file->storeAs('receipts', $filename, 'public');
                $data['receipt_image'] = $filename;
            }

            // Jika checkbox hapus gambar dicentang
            if ($request->has('remove_image') && $request->remove_image == '1') {
                if ($expense->receipt_image) {
                    Storage::disk('public')->delete('receipts/' . $expense->receipt_image);
                    $data['receipt_image'] = null;
                }
            }

            $updated = Expenses::updateData($id, $data);

            if ($updated) {
                return redirect()->route('expenses')
                    ->with('success', 'Pengeluaran "' . $request->description . '" berhasil diperbarui');
            } else {
                return redirect()->back()
                    ->with('error', 'Terjadi kesalahan saat memperbarui data')
                    ->withInput();
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem')
                ->withInput();
        }
    }

    // Hapus Pengeluaran (ubah dari delete ke destroy)
    public function destroy($id)
    {
        try {
            $expense = Expenses::find($id);
            
            if (!$expense) {
                return redirect()->route('expenses')
                    ->with('error', 'Data pengeluaran tidak ditemukan');
            }

            $description = $expense->description;
            $deleted = Expenses::deleteData($id);

            if ($deleted) {
                return redirect()->route('expenses')
                    ->with('success', 'Pengeluaran "' . $description . '" berhasil dihapus');
            } else {
                return redirect()->route('expenses')
                    ->with('error', 'Terjadi kesalahan saat menghapus data');
            }

        } catch (\Exception $e) {
            return redirect()->route('expenses')
                ->with('error', 'Terjadi kesalahan sistem');
        }
    }

    // Method lama delete() untuk kompatibilitas backward (GET method)
    public function delete($id)
    {
        return $this->destroy($id);
    }

    // Download Receipt Image (BARU)
    public function downloadReceipt($id)
    {
        $expense = Expenses::find($id);
        
        if (!$expense || !$expense->receipt_image) {
            abort(404, 'Gambar tidak ditemukan');
        }

        $filePath = storage_path('app/public/receipts/' . $expense->receipt_image);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath);
    }

    // API untuk mendapatkan data pengeluaran (BARU - untuk AJAX/Chart)
    public function apiData(Request $request)
    {
        $query = Expenses::with('category');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('category')) {
            $query->where('id_category', $request->category);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $expenses,
            'total' => $expenses->sum('amount')
        ]);
    }
}