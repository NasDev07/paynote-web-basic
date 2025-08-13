@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">

        <!-- Form Tambah Data Pemasukan -->
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold">
                        <i class="fas fa-plus"></i>
                        Tambah Data Pemasukan
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
                    <form action="{{ route('incomes.insert') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Kategori -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_category">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-control @error('id_category') is-invalid @enderror"
                                        id="id_category" name="id_category" required>
                                        <option value="">Pilih Kategori</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id_category }}" {{ old('id_category')==$category->
                                            id_category ? 'selected' : '' }}>
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
                                        id="date" name="date" value="{{ old('date', date('Y-m-d')) }}"
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
                                            id="amount" name="amount" value="{{ old('amount') }}" placeholder="0"
                                            min="0.01" step="0.01" required>
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
                                    <label for="proof_image">Upload Bukti Pemasukan</label>
                                    <input type="file"
                                        class="form-control-file @error('proof_image') is-invalid @enderror"
                                        id="proof_image" name="proof_image" accept="image/*">
                                    @error('proof_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Format: JPG, JPEG, PNG, GIF (Max: 5MB)
                                    </small>

                                    <!-- Preview image -->
                                    <div id="image-preview" class="mt-2" style="display: none;">
                                        <img id="preview-img" src="" alt="Preview" class="img-thumbnail"
                                            style="max-width: 200px;">
                                        <button type="button" id="remove-preview" class="btn btn-sm btn-danger ml-2">
                                            <i class="fas fa-times"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label for="description">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="3"
                                placeholder="Contoh: Gaji bulan ini, Bonus penjualan, Freelance project"
                                required>{{ old('description') }}</textarea>
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
                                <li>Upload bukti pemasukan seperti slip gaji, transfer, dll (opsional)</li>
                                <li>Tanggal tidak boleh lebih dari hari ini</li>
                                <li>Saldo akan otomatis bertambah sesuai jumlah pemasukan</li>
                            </ul>
                        </div>

                        <!-- Buttons Action -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Simpan
                            </button>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('proof_image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeButton = document.getElementById('remove-preview');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    removeButton.addEventListener('click', function() {
        fileInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });

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