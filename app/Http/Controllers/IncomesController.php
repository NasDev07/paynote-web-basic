<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Incomes;
use App\Models\Categories;

class IncomesController extends Controller
{
    // Helper method untuk cek apakah sudah lebih dari 24 jam
    private function isExpired($income)
    {
        $createdAt = Carbon::parse($income->created_at);
        $now = Carbon::now();
        return $now->diffInHours($createdAt) >= 24;
    }

    // Helper method untuk mendapatkan sisa waktu
    private function getRemainingTime($income)
    {
        $createdAt = Carbon::parse($income->created_at);
        $expiry = $createdAt->addHours(24);
        $now = Carbon::now();
        
        if ($now->greaterThan($expiry)) {
            return null;
        }
        
        return $expiry->diff($now);
    }

    // Halaman List Pemasukan
    public function index(Request $request)
    {
        $query = Incomes::with('category');

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

        $incomes = $query->orderBy('date', 'desc')->paginate(10);
        $categories = Categories::getAll();
        
        return view('dashboard.incomes.list', compact('incomes', 'categories'));
    }

    // Halaman Tambah Pemasukan (create method)
    public function create()
    {
        $categories = Categories::getAll();
        return view('dashboard.incomes.create', compact('categories'));
    }

    // Method lama addPage() untuk kompatibilitas backward
    public function addPage()
    {
        return $this->create();
    }

    // Tambah Pemasukan (store method)
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'description' => 'required|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
            'id_category' => 'required|exists:categories,id_category',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120' // Max 5MB
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
            'proof_image.image' => 'File harus berupa gambar',
            'proof_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'proof_image.max' => 'Ukuran gambar maksimal 5MB'
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
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                // Simpan ke storage/app/public/incomes
                $file->storeAs('incomes', $filename, 'public');
                $data['proof_image'] = $filename;
            }

            // Insert data
            $income = Incomes::insert($data);

            if ($income) {
                return redirect()->route('incomes')
                    ->with('success', 'Pemasukan "' . $request->description . '" berhasil ditambahkan');
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

    // Halaman Detail Pemasukan
    public function show($id)
    {
        $income = Incomes::getById($id);
        
        if (!$income) {
            return redirect()->route('incomes')
                ->with('error', 'Data pemasukan tidak ditemukan');
        }

        // Tambahkan info apakah expired atau tidak
        $income->is_expired = $this->isExpired($income);
        $income->remaining_time = $this->getRemainingTime($income);

        return view('dashboard.incomes.show', compact('income'));
    }

    // Halaman Edit Pemasukan dengan validasi 24 jam
    public function edit($id)
    {
        $income = Incomes::getById($id);
        
        if (!$income) {
            return redirect()->route('incomes')
                ->with('error', 'Data pemasukan tidak ditemukan');
        }

        // Cek apakah sudah lebih dari 24 jam
        if ($this->isExpired($income)) {
            return redirect()->route('incomes')
                ->with('error', 'Tidak dapat mengedit data yang sudah lebih dari 24 jam!');
        }

        $categories = Categories::getAll();
        $income->remaining_time = $this->getRemainingTime($income);
        
        return view('dashboard.incomes.edit', compact('income', 'categories'));
    }

    // Method lama editPage() untuk kompatibilitas backward
    public function editPage($id)
    {
        return $this->edit($id);
    }

    // Update Pemasukan dengan validasi 24 jam
    public function update(Request $request, $id)
    {
        $income = Incomes::find($id);
        
        if (!$income) {
            return redirect()->route('incomes')
                ->with('error', 'Data pemasukan tidak ditemukan');
        }

        // Cek apakah sudah lebih dari 24 jam
        if ($this->isExpired($income)) {
            return redirect()->route('incomes')
                ->with('error', 'Tidak dapat mengupdate data yang sudah lebih dari 24 jam!');
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'description' => 'required|string|max:1000',
            'date' => 'required|date|before_or_equal:today',
            'id_category' => 'required|exists:categories,id_category',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
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
            'proof_image.image' => 'File harus berupa gambar',
            'proof_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'proof_image.max' => 'Ukuran gambar maksimal 5MB'
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
            if ($request->hasFile('proof_image')) {
                // Hapus gambar lama jika ada
                if ($income->proof_image) {
                    Storage::disk('public')->delete('incomes/' . $income->proof_image);
                }

                $file = $request->file('proof_image');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                $file->storeAs('incomes', $filename, 'public');
                $data['proof_image'] = $filename;
            }

            // Jika checkbox hapus gambar dicentang
            if ($request->has('remove_image') && $request->remove_image == '1') {
                if ($income->proof_image) {
                    Storage::disk('public')->delete('incomes/' . $income->proof_image);
                    $data['proof_image'] = null;
                }
            }

            $updated = Incomes::updateData($id, $data);

            if ($updated) {
                return redirect()->route('incomes')
                    ->with('success', 'Pemasukan "' . $request->description . '" berhasil diperbarui');
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

    // Hapus Pemasukan dengan validasi 24 jam
    public function destroy($id)
    {
        try {
            $income = Incomes::find($id);
            
            if (!$income) {
                return redirect()->route('incomes')
                    ->with('error', 'Data pemasukan tidak ditemukan');
            }

            // Cek apakah sudah lebih dari 24 jam
            if ($this->isExpired($income)) {
                return redirect()->route('incomes')
                    ->with('error', 'Tidak dapat menghapus data yang sudah lebih dari 24 jam!');
            }

            $description = $income->description;
            $deleted = Incomes::deleteData($id);

            if ($deleted) {
                return redirect()->route('incomes')
                    ->with('success', 'Pemasukan "' . $description . '" berhasil dihapus');
            } else {
                return redirect()->route('incomes')
                    ->with('error', 'Terjadi kesalahan saat menghapus data');
            }

        } catch (\Exception $e) {
            return redirect()->route('incomes')
                ->with('error', 'Terjadi kesalahan sistem');
        }
    }

    // Method lama delete() untuk kompatibilitas backward (GET method)
    public function delete($id)
    {
        return $this->destroy($id);
    }

    // API method untuk cek status 24 jam (AJAX)
    public function checkIncomeStatus($id)
    {
        $income = Incomes::find($id);
        
        if (!$income) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $isExpired = $this->isExpired($income);
        $remainingTime = $this->getRemainingTime($income);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $income->id_income,
                'is_expired' => $isExpired,
                'can_edit' => !$isExpired,
                'can_delete' => !$isExpired,
                'created_at' => $income->created_at,
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
    public function checkAllIncomesStatus(Request $request)
    {
        $incomeIds = $request->input('income_ids', []);
        
        if (empty($incomeIds)) {
            $incomes = Incomes::orderBy('created_at', 'desc')->limit(50)->get();
        } else {
            $incomes = Incomes::whereIn('id_income', $incomeIds)->get();
        }
        
        $result = [];
        
        foreach ($incomes as $income) {
            $isExpired = $this->isExpired($income);
            $remainingTime = $this->getRemainingTime($income);
            
            $result[] = [
                'id' => $income->id_income,
                'is_expired' => $isExpired,
                'can_edit' => !$isExpired,
                'can_delete' => !$isExpired,
                'created_at' => $income->created_at,
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

    // Download Proof Image
    public function downloadProof($id)
    {
        $income = Incomes::find($id);
        
        if (!$income || !$income->proof_image) {
            abort(404, 'Gambar tidak ditemukan');
        }

        $filePath = storage_path('app/public/incomes/' . $income->proof_image);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        return response()->download($filePath);
    }

    // API untuk mendapatkan data pemasukan (untuk AJAX/Chart)
    public function apiData(Request $request)
    {
        $query = Incomes::with('category');

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        if ($request->filled('category')) {
            $query->where('id_category', $request->category);
        }

        $incomes = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $incomes,
            'total' => $incomes->sum('amount')
        ]);
    }
}