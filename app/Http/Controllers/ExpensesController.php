<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Expenses;
use App\Models\Categories;

class ExpensesController extends Controller
{
    // Helper method untuk cek apakah sudah lebih dari 24 jam
    private function isExpired($expense)
    {
        $createdAt = Carbon::parse($expense->created_at);
        $now = Carbon::now();
        return $now->diffInHours($createdAt) >= 24;
    }

    // Helper method untuk mendapatkan sisa waktu
    private function getRemainingTime($expense)
    {
        $createdAt = Carbon::parse($expense->created_at);
        $expiry = $createdAt->addHours(24);
        $now = Carbon::now();
        
        if ($now->greaterThan($expiry)) {
            return null;
        }
        
        return $expiry->diff($now);
    }

    // Halaman List Pengeluaran
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

    // Halaman Tambah Pengeluaran (create method)
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

    // Tambah Pengeluaran (store method)
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

    // Halaman Detail Pengeluaran
    public function show($id)
    {
        $expense = Expenses::getById($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        // Tambahkan info apakah expired atau tidak
        $expense->is_expired = $this->isExpired($expense);
        $expense->remaining_time = $this->getRemainingTime($expense);

        return view('dashboard.expenses.show', compact('expense'));
    }

    // Halaman Edit Pengeluaran dengan validasi 24 jam
    public function edit($id)
    {
        $expense = Expenses::getById($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        // Cek apakah sudah lebih dari 24 jam
        if ($this->isExpired($expense)) {
            return redirect()->route('expenses')
                ->with('error', 'Tidak dapat mengedit data yang sudah lebih dari 24 jam!');
        }

        $categories = Categories::getAll();
        $expense->remaining_time = $this->getRemainingTime($expense);
        
        return view('dashboard.expenses.edit', compact('expense', 'categories'));
    }

    // Method lama editPage() untuk kompatibilitas backward
    public function editPage($id)
    {
        return $this->edit($id);
    }

    // Update Pengeluaran dengan validasi 24 jam
    public function update(Request $request, $id)
    {
        $expense = Expenses::find($id);
        
        if (!$expense) {
            return redirect()->route('expenses')
                ->with('error', 'Data pengeluaran tidak ditemukan');
        }

        // Cek apakah sudah lebih dari 24 jam
        if ($this->isExpired($expense)) {
            return redirect()->route('expenses')
                ->with('error', 'Tidak dapat mengupdate data yang sudah lebih dari 24 jam!');
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

    // Hapus Pengeluaran dengan validasi 24 jam
    public function destroy($id)
    {
        try {
            $expense = Expenses::find($id);
            
            if (!$expense) {
                return redirect()->route('expenses')
                    ->with('error', 'Data pengeluaran tidak ditemukan');
            }

            // Cek apakah sudah lebih dari 24 jam
            if ($this->isExpired($expense)) {
                return redirect()->route('expenses')
                    ->with('error', 'Tidak dapat menghapus data yang sudah lebih dari 24 jam!');
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

    // API method untuk cek status 24 jam (AJAX)
    public function checkExpenseStatus($id)
    {
        $expense = Expenses::find($id);
        
        if (!$expense) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $isExpired = $this->isExpired($expense);
        $remainingTime = $this->getRemainingTime($expense);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $expense->id_expense,
                'is_expired' => $isExpired,
                'can_edit' => !$isExpired,
                'can_delete' => !$isExpired,
                'created_at' => $expense->created_at,
                'remaining_time' => $remainingTime ? [
                    'hours' => $remainingTime->h,
                    'minutes' => $remainingTime->i,
                    'seconds' => $remainingTime->s,
                    'formatted' => $remainingTime->format('%H:%I:%S')
                ] : null
            ]
        ]);
    }

    // API method untuk bulk check status
    public function checkAllExpensesStatus(Request $request)
    {
        $expenseIds = $request->input('expense_ids', []);
        
        if (empty($expenseIds)) {
            $expenses = Expenses::orderBy('created_at', 'desc')->limit(50)->get();
        } else {
            $expenses = Expenses::whereIn('id_expense', $expenseIds)->get();
        }
        
        $result = [];
        
        foreach ($expenses as $expense) {
            $isExpired = $this->isExpired($expense);
            $remainingTime = $this->getRemainingTime($expense);
            
            $result[] = [
                'id' => $expense->id_expense,
                'is_expired' => $isExpired,
                'can_edit' => !$isExpired,
                'can_delete' => !$isExpired,
                'created_at' => $expense->created_at,
                'remaining_time' => $remainingTime ? [
                    'hours' => $remainingTime->h,
                    'minutes' => $remainingTime->i,
                    'seconds' => $remainingTime->s,
                    'total_seconds' => $remainingTime->s + ($remainingTime->i * 60) + ($remainingTime->h * 3600)
                ] : null
            ];
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    // Download Receipt Image
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

    // API untuk mendapatkan data pengeluaran (untuk AJAX/Chart)
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