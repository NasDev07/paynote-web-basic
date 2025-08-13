<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Incomes extends Model
{
    use HasFactory;

    // Inisialisasi Tabel
    protected $table = 'incomes';
    protected $primaryKey = 'id_income';
    public $timestamps = true;

    // Fill Tabel
    protected $fillable = [
        'amount',
        'description',
        'date',
        'id_category',
        'proof_image',
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

    // Accessor untuk proof image URL
    public function getProofImageUrlAttribute()
    {
        if ($this->proof_image) {
            return Storage::url('incomes/' . $this->proof_image);
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
        return self::with('category')->where('id_income', $id)->first();
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
                    'amount' => $amount, // Positif karena income
                    'updated_at' => now()
                ]);

                if (!$balance) {
                    return false;
                }
            }

            // Tambahkan data ke tabel Incomes
            $income = self::create($data);
            return $income;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Update Data
    public static function updateData($id, $data)
    {
        try {
            $income = self::find($id);
            if (!$income) {
                return false;
            }

            // Jika amount berubah, update balance
            if (class_exists('App\Models\Balance') && isset($data['amount']) && $income->amount != $data['amount']) {
                $difference = $data['amount'] - $income->amount;
                Balance::create([
                    'amount' => $difference, // Positif atau negatif sesuai selisih
                    'updated_at' => now()
                ]);
            }

            return $income->update($data);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Delete Data
    public static function deleteData($id)
    {
        try {
            $income = self::find($id);
            if (!$income) {
                return false;
            }

            // Hapus gambar jika ada
            if ($income->proof_image) {
                Storage::delete('incomes/' . $income->proof_image);
            }

            // Update balance (kurangi amount yang sudah ditambahkan)
            if (class_exists('App\Models\Balance')) {
                Balance::create([
                    'amount' => -1 * $income->amount, // Negatif karena mengurangi
                    'updated_at' => now()
                ]);
            }

            return $income->delete();
        } catch (\Exception $e) {
            return false;
        }
    }

    // Total Pemasukan
    public static function totalIncomes()
    {
        return self::sum('amount');
    }

    // Total Pemasukan berdasarkan kategori
    public static function totalByCategory($categoryId = null)
    {
        $query = self::query();

        if ($categoryId) {
            $query->where('id_category', $categoryId);
        }

        return $query->sum('amount');
    }

    // Pemasukan per bulan
    public static function monthlyIncomes($year = null, $month = null)
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
