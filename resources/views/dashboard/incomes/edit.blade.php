@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">

        <!-- Form Edit Data Pemasukan -->
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold">
                        <i class="fas fa-edit"></i>
                        Edit Data Pemasukan
                    </h5>
                    <hr>

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('incomes.update', $income->id_income) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Kategori -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_category">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control @error('id_category') is-invalid @enderror"
                                        id="id_category" name="id_category" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}" {{ (old('id_category', $income->
                                            id_category) == $category->id_category) ? 'selected' : '' }}>
                                            {{ $category->name_category }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('id_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tanggal -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('date') is-invalid @enderror"
                                        id="date" name="date" value="{{ old('date', $income->date) }}"
                                        max="{{ date('Y-m-d') }}" required>
                                    @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Jumlah -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Jumlah (Rp) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-success text-white">Rp</span>
                                        </div>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            id="amount" name="amount" value="{{ old('amount', $income->amount) }}"
                                            placeholder="0" min="0.01" step="0.01" required>
                                    </div>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Masukkan jumlah pemasukan</small>
                                </div>
                            </div>

                            <!-- Upload Bukti -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proof_image">Upload Bukti Pemasukan Baru</label>
                                    <input type="file"
                                        class="form-control-file @error('proof_image') is-invalid @enderror"
                                        id="proof_image" name="proof_image" accept="image/*">
                                    @error('proof_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format: JPG, JPEG, PNG, GIF (Max: 5MB)
                                    </small>

                                    <!-- Preview image baru -->
                                    <div id="image-preview" class="mt-2" style="display: none;">
                                        <img id="preview-img" src="" alt="Preview" class="img-thumbnail"
                                            style="max-width: 200px;">
                                        <button type="button" id="remove-preview" class="btn btn-sm btn-danger ml-2">
                                            <i class="fas fa-times"></i> Hapus Preview
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gambar Bukti yang sudah ada -->
                        @if($income->proof_image)
                        <div class="form-group">
                            <label>Bukti Pemasukan Saat Ini:</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <img src="{{ asset('storage/incomes/' . $income->proof_image) }}"
                                                class="img-fluid img-thumbnail" alt="Current Proof"
                                                style="max-height: 150px; cursor: pointer;" data-toggle="modal"
                                                data-target="#currentProofModal">
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>File:</strong> {{ $income->proof_image }}</p>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                    data-target="#currentProofModal">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </button>
                                                <a href="{{ route('incomes.download-proof', $income->id_income) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="remove_image"
                                                    name="remove_image" value="1">
                                                <label class="form-check-label text-danger" for="remove_image">
                                                    <i class="fas fa-trash"></i> Hapus gambar ini
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label for="description">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="3"
                                placeholder="Contoh: Gaji bulan ini, Bonus penjualan, Freelance project"
                                required>{{ old('description', $income->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Jelaskan sumber pemasukan (maksimal 1000 karakter)
                            </small>
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="alert alert-success">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Semua field yang bertanda <span class="text-danger">*</span> wajib diisi</li>
                                <li>Upload bukti pemasukan baru untuk mengganti yang lama (opsional)</li>
                                <li>Centang "Hapus gambar ini" untuk menghapus bukti yang ada</li>
                                <li>Jika jumlah berubah, saldo akan otomatis disesuaikan</li>
                            </ul>
                        </div>

                        <!-- Buttons Action -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('incomes.show', $income->id_income) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('incomes') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat bukti saat ini -->
@if($income->proof_image)
<div class="modal fade" id="currentProofModal" tabindex="-1" aria-labelledby="currentProofModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="currentProofModalLabel">
                    Bukti Saat Ini - {{ $income->description }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/incomes/' . $income->proof_image) }}" class="img-fluid" alt="Current Proof">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('proof_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeButton = document.getElementById('remove-preview');
    const removeCheckbox = document.getElementById('remove_image');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                // Uncheck remove checkbox jika ada gambar baru
                if (removeCheckbox) {
                    removeCheckbox.checked = false;
                }
            };
            reader.readAsDataURL(file);
        }
    });

    removeButton.addEventListener('click', function() {
        fileInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });

    // Konfirmasi jika ingin menghapus gambar
    if (removeCheckbox) {
        removeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Apakah Anda yakin ingin menghapus bukti yang ada?')) {
                    this.checked = false;
                }
            }
        });
    }

    // Format number input
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function(e) {
        let value = e.target.value;
        // Remove non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        e.target.value = value;
    });
});
</script>

@endsection