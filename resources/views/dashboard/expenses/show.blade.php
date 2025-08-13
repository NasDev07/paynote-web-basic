@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title fw-bold">
                            <i class="fas fa-eye"></i>
                            Detail Pengeluaran
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('expenses.editPage', $expense->id_expense) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteExpenseModal">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            <a href="{{ route('expenses') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <hr>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Informasi Utama -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pengeluaran</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">ID Pengeluaran:</label>
                                                <p class="form-control-plaintext">#{{ str_pad($expense->id_expense, 6,
                                                    '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Tanggal:</label>
                                                <p class="form-control-plaintext">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ date('d F Y', strtotime($expense->date)) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Jumlah:</label>
                                                <p class="form-control-plaintext">
                                                    <span class="text-danger font-weight-bold h5">
                                                        <i class="fas fa-minus-circle"></i>
                                                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Kategori:</label>
                                                <p class="form-control-plaintext">
                                                    @if($expense->category)
                                                    <span class="badge badge-secondary badge-lg">
                                                        {{ $expense->category->name_category }}
                                                    </span>
                                                    @else
                                                    <span class="badge badge-warning">Tidak ada kategori</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Deskripsi:</label>
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                {{ $expense->description }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Dibuat pada:</label>
                                                <p class="form-control-plaintext text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    {{ date('d F Y, H:i', strtotime($expense->created_at)) }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($expense->updated_at && $expense->updated_at != $expense->created_at)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Terakhir diupdate:</label>
                                                <p class="form-control-plaintext text-muted">
                                                    <i class="fas fa-edit"></i>
                                                    {{ date('d F Y, H:i', strtotime($expense->updated_at)) }}
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt/Bukti -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-receipt"></i> Receipt/Bukti</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($expense->receipt_image)
                                    <img src="{{ asset('storage/receipts/' . $expense->receipt_image) }}"
                                        class="img-fluid img-thumbnail mb-3" alt="Receipt"
                                        style="max-height: 300px; cursor: pointer;" data-toggle="modal"
                                        data-target="#receiptModal">

                                    <div class="btn-group d-block">
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#receiptModal">
                                            <i class="fas fa-eye"></i> Lihat Besar
                                        </button>
                                        <a href="{{ route('expenses.download-receipt', $expense->id_expense) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                    @else
                                    <div class="text-muted">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <p>Tidak ada receipt yang diupload</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat receipt dalam ukuran besar -->
@if($expense->receipt_image)
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">
                    Receipt - {{ $expense->description }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/receipts/' . $expense->receipt_image) }}" class="img-fluid" alt="Receipt">
            </div>
            <div class="modal-footer">
                <a href="{{ route('expenses.download-receipt', $expense->id_expense) }}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="deleteExpenseModal" tabindex="-1" aria-labelledby="deleteExpenseModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteExpenseModalLabel">
                    Hapus Data Pengeluaran
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengeluaran berikut?</p>
                <div class="card bg-light">
                    <div class="card-body">
                        <p><strong>Tanggal:</strong> {{ date('d F Y', strtotime($expense->date)) }}</p>
                        <p><strong>Jumlah:</strong> <span class="text-danger">Rp {{ number_format($expense->amount, 0,
                                ',', '.') }}</span></p>
                        <p><strong>Deskripsi:</strong> {{ $expense->description }}</p>
                    </div>
                </div>
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan dan saldo akan otomatis
                    disesuaikan!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="{{ route('expenses.delete', $expense->id_expense) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection