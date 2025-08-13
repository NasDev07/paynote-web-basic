@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">

        <!-- Form Edit Data Pengeluaran -->
        <div class="col-md-12">
            <div class="card shadow-sm p-3">
                <div class="card-body">
                    <h5 class="card-title fw-bold">
                        <i class="fas fa-edit"></i>
                        Edit Data Pengeluaran
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
                    <form action="{{ route('expenses.update', $expense->id_expense) }}" method="POST"
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
                                        <option value="{{ $category->id_category }}" {{ (old('id_category', $expense->
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
                                        id="date" name="date" value="{{ old('date', $expense->date) }}"
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
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            id="amount" name="amount" value="{{ old('amount', $expense->amount) }}"
                                            placeholder="0" min="0.01" step="0.01" required>
                                    </div>
                                    @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Masukkan jumlah pengeluaran</small>
                                </div>
                            </div>

                            <!-- Upload Receipt -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="receipt_image">Upload Receipt/Struk Baru</label>
                                    <input type="file"
                                        class="form-control-file @error('receipt_image') is-invalid @enderror"
                                        id="receipt_image" name="receipt_image" accept="image/*">
                                    @error('receipt_image')
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

                        <!-- Gambar Receipt yang sudah ada -->
                        @if($expense->receipt_image)
                        <div class="form-group">
                            <label>Receipt Saat Ini:</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <img src="{{ asset('storage/receipts/' . $expense->receipt_image) }}"
                                                class="img-fluid img-thumbnail" alt="Current Receipt"
                                                style="max-height: 150px; cursor: pointer;" data-toggle="modal"
                                                data-target="#currentReceiptModal">
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>File:</strong> {{ $expense->receipt_image }}</p>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                                    data-target="#currentReceiptModal">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </button>
                                                <a href="{{ route('expenses.download-receipt', $expense->id_expense) }}"
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
                                name="description" rows="3" placeholder="Contoh: Beli bahan makanan di supermarket"
                                required>{{ old('description', $expense->description) }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Jelaskan detail pengeluaran (maksimal 1000 karakter)
                            </small>
                        </div>

                        <!-- Informasi Tambahan -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Semua field yang bertanda <span class="text-danger">*</span> wajib diisi</li>
                                <li>Upload receipt/struk baru untuk mengganti yang lama (opsional)</li>
                                <li>Centang "Hapus gambar ini" untuk menghapus receipt yang ada</li>
                                <li>Jika jumlah berubah, saldo akan otomatis disesuaikan</li>
                            </ul>
                        </div>

                        <!-- Buttons Action -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('expenses.show', $expense->id_expense) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('expenses') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk melihat receipt saat ini -->
@if($expense->receipt_image)
<div class="modal fade" id="currentReceiptModal" tabindex="-1" aria-labelledby="currentReceiptModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="currentReceiptModalLabel">
                    Receipt Saat Ini - {{ $expense->description }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/receipts/' . $expense->receipt_image) }}" class="img-fluid"
                    alt="Current Receipt">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('receipt_image');
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
                if (!confirm('Apakah Anda yakin ingin menghapus receipt yang ada?')) {
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