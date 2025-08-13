<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Expenses extends Model
{
    use HasFactory;

    // Inisialisasi Tabel
    protected $table = 'expenses';
    protected $primaryKey = 'id_expense';
    public $timestamps = true;

    // Fill Tabel
    protected $fillable = [
        'amount',
        'description',
        'date',
        'id_category',
        'receipt_image',
        'created_at',
        'updated_at'
    ];

    // Cast attributes
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship dengan Categories
    public function category()
    {
        return $this->belongsTo(Categories::class, 'id_category', 'id_category');
    }

    // Accessor untuk receipt image URL
    public function getReceiptImageUrlAttribute()
    {
        if ($this->receipt_image) {
            return Storage::url('receipts/' . $this->receipt_image);
        }
        return null;
    }

    // Get All Data dengan relasi
    public static function getAllWithCategory()
    {
        return self::with('category')->orderBy('date', 'desc')->get();
    }

    // Get All Data
    public static function getAll()
    {
        return self::orderBy('date', 'desc')->get();
    }

    // Get Data by ID
    public static function getById($id)
    {
        return self::with('category')->where('id_expense', $id)->first();
    }

    // Insert Data
    public static function insert($data)
    {
        try {
            // Ambil amount dari $data
            $amount = $data['amount'];

            // tambahkan data ke tabel balance jika model Balance ada
            if (class_exists('App\Models\Balance')) {
                $balance = Balance::create([
                    'amount' => -1 * $amount,
                    'updated_at' => now()
                ]);

                if (!$balance) {
                    return false;
                }
            }

            // Tambahkan data ke tabel Expenses
            $expense = self::create($data);
            return $expense;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Update Data
    public static function updateData($id, $data)
    {
        try {
            $expense = self::find($id);
            if (!$expense) {
                return false;
            }

            // Jika amount berubah, update balance
            if (class_exists('App\Models\Balance') && isset($data['amount']) && $expense->amount != $data['amount']) {
                $difference = $data['amount'] - $expense->amount;
                Balance::create([
                    'amount' => -1 * $difference,
                    'updated_at' => now()
                ]);
            }

            return $expense->update($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Delete Data
    public static function deleteData($id)
    {
        try {
            $expense = self::find($id);
            if (!$expense) {
                return false;
            }

            // Hapus gambar jika ada
            if ($expense->receipt_image) {
                Storage::delete('receipts/' . $expense->receipt_image);
            }

            // Update balance (kembalikan amount yang sudah dikeluarkan)
            if (class_exists('App\Models\Balance')) {
                Balance::create([
                    'amount' => $expense->amount, // Positif karena mengembalikan
                    'updated_at' => now()
                ]);
            }

            return $expense->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    // Total Pengeluaran
    public static function totalExpenses()
    {
        return self::sum('amount');
    }

    // Total Pengeluaran berdasarkan kategori
    public static function totalByCategory($categoryId = null)
    {
        $query = self::query();

        if ($categoryId) {
            $query->where('id_category', $categoryId);
        }

        return $query->sum('amount');
    }

    // Pengeluaran per bulan
    public static function monthlyExpenses($year = null, $month = null)
    {
        $year = $year ?: date('Y');
        $month = $month ?: date('m');

        return self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('amount');
    }

    // Scope untuk filter berdasarkan tanggal
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Scope untuk filter berdasarkan kategori
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('id_category', $categoryId);
    }
}
