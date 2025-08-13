@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">
        <div class="table-responsive shadow-sm p-3">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('expenses.addPage') }}" class="btn btn-sm btn-danger">
                        <i class="fas fa-plus"></i>
                        Tambah Pengeluaran
                    </a>

                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('expenses') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <select name="category" class="form-control form-control-sm">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id_category }}" {{ request('category')==$category->
                                    id_category ? 'selected' : '' }}>
                                    {{ $category->name_category }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                placeholder="Dari Tanggal" value="{{ request('start_date') }}">
                        </div>
                        <div class="form-group mr-2">
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                        </div>
                        <div class="form-group mr-2">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Cari deskripsi..." value="{{ request('search') }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-danger mr-2">Filter</button>
                        @if(request()->hasAny(['category', 'start_date', 'end_date', 'search']))
                        <a href="{{ route('expenses') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </form>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Pengeluaran</h6>
                                        <h4>Rp {{ number_format($expenses->sum('amount'), 0, ',', '.') }}</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-minus-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Jumlah Transaksi</h6>
                                        <h4>{{ $expenses->count() }}</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-list-ol fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Rata-rata/Transaksi</h6>
                                        <h4>Rp {{ $expenses->count() > 0 ? number_format($expenses->sum('amount') /
                                            $expenses->count(), 0, ',', '.') : '0' }}</h4>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-calculator fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Receipt</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $index => $expense)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ date('d/m/Y', strtotime($expense->date)) }}
                            <br>
                            <small class="text-muted">{{ date('H:i', strtotime($expense->created_at)) }}</small>
                        </td>
                        <td>
                            <span class="text-danger font-weight-bold">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @foreach($categories as $category)
                            @if($category->id_category == $expense->id_category)
                            <span class="badge badge-danger">{{ $category->name_category }}</span>
                            @endif
                            @endforeach
                        </td>
                        <td>
                            <div title="{{ $expense->description }}">
                                {{ Str::limit($expense->description, 50) }}
                            </div>
                        </td>
                        <td>
                            @if($expense->receipt_image)
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                data-target="#imageModal{{ $expense->id_expense }}">
                                <i class="fas fa-image"></i> Lihat
                            </button>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('expenses.show', $expense->id_expense) }}" class="btn btn-sm btn-info"
                                    title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Edit dan Delete Button dengan 24 jam limit -->
                                <div class="edit-delete-buttons" data-created="{{ $expense->created_at }}"
                                    data-expense-id="{{ $expense->id_expense }}">
                                    <a href="{{ route('expenses.editPage', $expense->id_expense) }}"
                                        class="btn btn-sm btn-warning edit-btn" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-btn" data-toggle="modal"
                                        data-target="#deleteExpenseModal{{ $expense->id_expense }}" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Pesan jika sudah lebih dari 24 jam -->
                                <div class="expired-message" style="display: none;">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Expired
                                    </small>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data pengeluaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if(method_exists($expenses, 'links'))
            <div class="d-flex justify-content-center">
                {{ $expenses->links() }}
            </div>
            @endif
        </div>

        <!-- Modal untuk melihat gambar receipt -->
        @foreach($expenses as $expense)
        @if($expense->receipt_image)
        <div class="modal fade" id="imageModal{{ $expense->id_expense }}" tabindex="-1"
            aria-labelledby="imageModalLabel{{ $expense->id_expense }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="imageModalLabel{{ $expense->id_expense }}">
                            Receipt - {{ $expense->description }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="{{ asset('storage/receipts/' . $expense->receipt_image) }}" class="img-fluid"
                            alt="Receipt" style="max-height: 500px;">
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('expenses.download-receipt', $expense->id_expense) }}"
                            class="btn btn-primary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach

        <!-- Modal konfirmasi hapus -->
        @foreach($expenses as $expense)
        <div class="modal fade" id="deleteExpenseModal{{ $expense->id_expense }}" tabindex="-1"
            aria-labelledby="deleteExpenseModalLabel{{ $expense->id_expense }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteExpenseModalLabel{{ $expense->id_expense }}">
                            Hapus Data Pengeluaran
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus pengeluaran berikut?</p>
                        <div class="card">
                            <div class="card-body">
                                <p><strong>Tanggal:</strong> {{ date('d/m/Y', strtotime($expense->date)) }}</p>
                                <p><strong>Jumlah:</strong> Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                <p><strong>Deskripsi:</strong> {{ $expense->description }}</p>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            Data yang dihapus tidak dapat dikembalikan!
                        </div>

                        <!-- Countdown timer untuk expired -->
                        <div class="alert alert-info countdown-info" style="display: none;">
                            <i class="fas fa-clock"></i>
                            <span class="countdown-text"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route('expenses.delete', $expense->id_expense) }}" method="POST"
                            class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger modal-delete-btn">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Function to check if 24 hours have passed
    function isExpired(createdAt) {
        const created = new Date(createdAt);
        const now = new Date();
        const diffInHours = (now - created) / (1000 * 60 * 60);
        return diffInHours >= 24;
    }

    // Function to get remaining time
    function getRemainingTime(createdAt) {
        const created = new Date(createdAt);
        const expiry = new Date(created.getTime() + (24 * 60 * 60 * 1000));
        const now = new Date();
        const remaining = expiry - now;
        
        if (remaining <= 0) return null;
        
        const hours = Math.floor(remaining / (1000 * 60 * 60));
        const minutes = Math.floor((remaining % (1000 * 60 * 60)) / (1000 * 60));
        
        return { hours, minutes, total: remaining };
    }

    // Function to format time
    function formatTimeRemaining(timeObj) {
        if (!timeObj) return '';
        return `${timeObj.hours}j ${timeObj.minutes}m tersisa`;
    }

    // Function to update buttons
    function updateButtons() {
        const editDeleteButtons = document.querySelectorAll('.edit-delete-buttons');
        
        editDeleteButtons.forEach(buttonGroup => {
            const createdAt = buttonGroup.getAttribute('data-created');
            const expenseId = buttonGroup.getAttribute('data-expense-id');
            const editBtn = buttonGroup.querySelector('.edit-btn');
            const deleteBtn = buttonGroup.querySelector('.delete-btn');
            const expiredMsg = buttonGroup.parentElement.querySelector('.expired-message');
            
            if (isExpired(createdAt)) {
                // Hide edit and delete buttons
                if (editBtn) editBtn.style.display = 'none';
                if (deleteBtn) deleteBtn.style.display = 'none';
                if (expiredMsg) expiredMsg.style.display = 'block';
                
                // Disable modal delete button
                const modal = document.querySelector(`#deleteExpenseModal${expenseId}`);
                if (modal) {
                    const modalDeleteBtn = modal.querySelector('.modal-delete-btn');
                    const deleteForm = modal.querySelector('.delete-form');
                    if (modalDeleteBtn) {
                        modalDeleteBtn.disabled = true;
                        modalDeleteBtn.textContent = 'Expired';
                        modalDeleteBtn.classList.remove('btn-danger');
                        modalDeleteBtn.classList.add('btn-secondary');
                    }
                    if (deleteForm) {
                        deleteForm.addEventListener('submit', function(e) {
                            e.preventDefault();
                            alert('Tidak dapat menghapus data yang sudah lebih dari 24 jam!');
                        });
                    }
                }
            } else {
                // Show countdown in modal
                const modal = document.querySelector(`#deleteExpenseModal${expenseId}`);
                if (modal) {
                    const countdownInfo = modal.querySelector('.countdown-info');
                    const countdownText = modal.querySelector('.countdown-text');
                    const remaining = getRemainingTime(createdAt);
                    
                    if (remaining && countdownInfo && countdownText) {
                        countdownInfo.style.display = 'block';
                        countdownText.textContent = `Waktu edit/hapus: ${formatTimeRemaining(remaining)}`;
                    }
                }
            }
        });
    }

    // Initial update
    updateButtons();

    // Update every minute
    setInterval(updateButtons, 60000);

    // Real-time countdown update
    function startCountdown() {
        setInterval(function() {
            const countdownTexts = document.querySelectorAll('.countdown-text');
            countdownTexts.forEach(text => {
                const modal = text.closest('.modal');
                if (modal) {
                    const expenseId = modal.id.replace('deleteExpenseModal', '');
                    const buttonGroup = document.querySelector(`[data-expense-id="${expenseId}"]`);
                    if (buttonGroup) {
                        const createdAt = buttonGroup.getAttribute('data-created');
                        const remaining = getRemainingTime(createdAt);
                        if (remaining) {
                            text.textContent = `Waktu edit/hapus: ${formatTimeRemaining(remaining)}`;
                        } else {
                            text.textContent = 'Waktu edit/hapus telah habis!';
                            text.parentElement.classList.remove('alert-info');
                            text.parentElement.classList.add('alert-warning');
                        }
                    }
                }
            });
        }, 60000); // Update every minute
    }

    startCountdown();

    // Add visual indicator for near expiry (less than 2 hours)
    function addExpiryWarning() {
        const editDeleteButtons = document.querySelectorAll('.edit-delete-buttons');
        
        editDeleteButtons.forEach(buttonGroup => {
            const createdAt = buttonGroup.getAttribute('data-created');
            const remaining = getRemainingTime(createdAt);
            
            if (remaining && remaining.total <= (2 * 60 * 60 * 1000)) { // Less than 2 hours
                const editBtn = buttonGroup.querySelector('.edit-btn');
                const deleteBtn = buttonGroup.querySelector('.delete-btn');
                
                if (editBtn) {
                    editBtn.classList.add('btn-warning');
                    editBtn.classList.remove('btn-warning');
                    editBtn.classList.add('btn-outline-warning');
                    editBtn.title = `Edit (${formatTimeRemaining(remaining)})`;
                }
                
                if (deleteBtn) {
                    deleteBtn.classList.remove('btn-danger');
                    deleteBtn.classList.add('btn-outline-danger');
                    deleteBtn.title = `Hapus (${formatTimeRemaining(remaining)})`;
                }
            }
        });
    }

    addExpiryWarning();
    setInterval(addExpiryWarning, 60000);
});
</script>

<style>
    .edit-delete-buttons {
        transition: all 0.3s ease;
    }

    .expired-message {
        padding: 2px 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        transform: scale(1.05);
    }

    .countdown-info {
        font-size: 0.9em;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }

        100% {
            opacity: 1;
        }
    }

    .btn-outline-warning,
    .btn-outline-danger {
        animation: pulse 2s infinite;
    }
</style>

@endsection