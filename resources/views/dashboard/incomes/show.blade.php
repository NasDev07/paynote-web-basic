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
                            Detail Pemasukan
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('incomes.editPage', $income->id_income) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                                data-target="#deleteIncomeModal">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            <a href="{{ route('incomes') }}" class="btn btn-secondary btn-sm">
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
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Pemasukan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">ID Pemasukan:</label>
                                                <p class="form-control-plaintext">#{{ str_pad($income->id_income, 6,
                                                    '0', STR_PAD_LEFT) }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Tanggal:</label>
                                                <p class="form-control-plaintext">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ date('d F Y', strtotime($income->date)) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Jumlah:</label>
                                                <p class="form-control-plaintext">
                                                    <span class="text-success font-weight-bold h5">
                                                        <i class="fas fa-plus-circle"></i>
                                                        Rp {{ number_format($income->amount, 0, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Kategori:</label>
                                                <p class="form-control-plaintext">
                                                    @if($income->category)
                                                    <span class="badge badge-success badge-lg">
                                                        {{ $income->category->name_category }}
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
                                                {{ $income->description }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Dibuat pada:</label>
                                                <p class="form-control-plaintext text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    {{ date('d F Y, H:i', strtotime($income->created_at)) }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($income->updated_at && $income->updated_at != $income->created_at)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Terakhir diupdate:</label>
                                                <p class="form-control-plaintext text-muted">
                                                    <i class="fas fa-edit"></i>
                                                    {{ date('d F Y, H:i', strtotime($income->updated_at)) }}
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bukti Pemasukan -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-file-image"></i> Bukti Pemasukan</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($income->proof_image)
                                    <img src="{{ asset('storage/incomes/' . $income->proof_image) }}"
                                        class="img-fluid img-thumbnail mb-3" alt="Proof"
                                        style="max-height: 300px; cursor: pointer;" data-toggle="modal"
                                        data-target="#proofModal">

                                    <div class="btn-group d-block">
                                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                            data-target="#proofModal">
                                            <i class="fas fa-eye"></i> Lihat Besar
                                        </button>
                                        <a href="{{ route('incomes.download-proof', $income->id_income) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                    @else
                                    <div class="text-muted">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <p>Tidak ada bukti yang diupload</p>
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

<!-- Modal untuk melihat bukti dalam ukuran besar -->
@if($income->proof_image)
<div class="modal fade" id="proofModal" tabindex="-1" aria-labelledby="proofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proofModalLabel">
                    Bukti Pemasukan - {{ $income->description }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/incomes/' . $income->proof_image) }}" class="img-fluid" alt="Proof">
            </div>
            <div class="modal-footer">
                <a href="{{ route('incomes.download-proof', $income->id_income) }}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal konfirmasi hapus -->
<div class="modal fade" id="deleteIncomeModal" tabindex="-1" aria-labelledby="deleteIncomeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteIncomeModalLabel">
                    Hapus Data Pemasukan
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pemasukan berikut?</p>
                <div class="card bg-light">
                    <div class="card-body">
                        <p><strong>Tanggal:</strong> {{ date('d F Y', strtotime($income->date)) }}</p>
                        <p><strong>Jumlah:</strong> <span class="text-success">Rp {{ number_format($income->amount, 0,
                                ',', '.') }}</span></p>
                        <p><strong>Deskripsi:</strong> {{ $income->description }}</p>
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
                <form action="{{ route('incomes.delete', $income->id_income) }}" method="POST" class="d-inline">
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