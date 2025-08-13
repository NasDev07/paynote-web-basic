@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
  <div class="row">
    <div class="table-responsive shadow-sm p-3">
      <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <a href="{{ route('incomes.addPage') }}" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i>
            Tambah Pemasukan
          </a>
          
          <!-- Filter Form -->
          <form method="GET" action="{{ route('incomes') }}" class="form-inline">
            <div class="form-group mr-2">
              <select name="category" class="form-control form-control-sm">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id_category }}" 
                      {{ request('category') == $category->id_category ? 'selected' : '' }}>
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
            <button type="submit" class="btn btn-sm btn-outline-success mr-2">Filter</button>
            @if(request()->hasAny(['category', 'start_date', 'end_date', 'search']))
              <a href="{{ route('incomes') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
            <div class="card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Total Pemasukan</h6>
                    <h4>Rp {{ number_format($incomes->sum('amount'), 0, ',', '.') }}</h4>
                  </div>
                  <div class="align-self-center">
                    <i class="fas fa-plus-circle fa-2x"></i>
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
                    <h4>{{ $incomes->count() }}</h4>
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
                    <h4>Rp {{ $incomes->count() > 0 ? number_format($incomes->sum('amount') / $incomes->count(), 0, ',', '.') : '0' }}</h4>
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
            <th scope="col">Bukti</th>
            <th scope="col">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($incomes as $index => $income)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>
              {{ date('d/m/Y', strtotime($income->date)) }}
              <br>
              <small class="text-muted">{{ date('H:i', strtotime($income->created_at)) }}</small>
            </td>
            <td>
              <span class="text-success font-weight-bold">
                Rp {{ number_format($income->amount, 0, ',', '.') }}
              </span>
            </td>
            <td>
              @foreach($categories as $category)
                @if($category->id_category == $income->id_category)
                  <span class="badge badge-success">{{ $category->name_category }}</span>
                @endif
              @endforeach
            </td>
            <td>
              <div title="{{ $income->description }}">
                {{ Str::limit($income->description, 50) }}
              </div>
            </td>
            <td>
              @if($income->proof_image)
                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" 
                        data-target="#imageModal{{ $income->id_income }}">
                  <i class="fas fa-image"></i> Lihat
                </button>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="{{ route('incomes.show', $income->id_income) }}" 
                   class="btn btn-sm btn-info" title="Detail">
                  <i class="fas fa-eye"></i>
                </a>
                
                <!-- Edit dan Delete Button dengan 24 jam limit -->
                <div class="edit-delete-buttons" 
                     data-created="{{ $income->created_at }}" 
                     data-income-id="{{ $income->id_income }}">
                  <a href="{{ route('incomes.editPage', $income->id_income) }}" 
                     class="btn btn-sm btn-warning edit-btn" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-danger delete-btn" 
                          data-toggle="modal" data-target="#deleteIncomeModal{{ $income->id_income }}"
                          title="Hapus">
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
            <td colspan="7" class="text-center">Tidak ada data pemasukan</td>
          </tr>
          @endforelse
        </tbody>
      </table>

      <!-- Pagination -->
      @if(method_exists($incomes, 'links'))
        <div class="d-flex justify-content-center">
          {{ $incomes->links() }}
        </div>
      @endif
    </div>

    <!-- Modal untuk melihat gambar proof -->
    @foreach($incomes as $income)
      @if($income->proof_image)
      <div class="modal fade" id="imageModal{{ $income->id_income }}" tabindex="-1" 
           aria-labelledby="imageModalLabel{{ $income->id_income }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="imageModalLabel{{ $income->id_income }}">
                Bukti Pemasukan - {{ $income->description }}
              </h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body text-center">
              <img src="{{ asset('storage/incomes/' . $income->proof_image) }}" 
                   class="img-fluid" alt="Proof" style="max-height: 500px;">
            </div>
            <div class="modal-footer">
              <a href="{{ route('incomes.download-proof', $income->id_income) }}" 
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
    @foreach($incomes as $income)
    <div class="modal fade" id="deleteIncomeModal{{ $income->id_income }}" tabindex="-1" 
         aria-labelledby="deleteIncomeModalLabel{{ $income->id_income }}" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteIncomeModalLabel{{ $income->id_income }}">
              Hapus Data Pemasukan
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus pemasukan berikut?</p>
            <div class="card">
              <div class="card-body">
                <p><strong>Tanggal:</strong> {{ date('d/m/Y', strtotime($income->date)) }}</p>
                <p><strong>Jumlah:</strong> Rp {{ number_format($income->amount, 0, ',', '.') }}</p>
                <p><strong>Deskripsi:</strong> {{ $income->description }}</p>
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
            <form action="{{ route('incomes.delete', $income->id_income) }}" method="POST" 
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
            const incomeId = buttonGroup.getAttribute('data-income-id');
            const editBtn = buttonGroup.querySelector('.edit-btn');
            const deleteBtn = buttonGroup.querySelector('.delete-btn');
            const expiredMsg = buttonGroup.parentElement.querySelector('.expired-message');
            
            if (isExpired(createdAt)) {
                // Hide edit and delete buttons
                if (editBtn) editBtn.style.display = 'none';
                if (deleteBtn) deleteBtn.style.display = 'none';
                if (expiredMsg) expiredMsg.style.display = 'block';
                
                // Disable modal delete button
                const modal = document.querySelector(`#deleteIncomeModal${incomeId}`);
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
                const modal = document.querySelector(`#deleteIncomeModal${incomeId}`);
                if (modal) {
                    const countdownInfo = modal.querySelector('.countdown-info');
                    const countdownText = modal.querySelector('.countdown-text');
                    const remaining = getRemainingTime(createdAt);
                    
                    if (remaining && countdownInfo && countdownText) {
                        countdownInfo.style.display = 'block';
                        countdownText.textContent = `Waktu edit/hapus: ${formatTimeRemaining(remaining)}`;
                    }
                }
                
                // Add warning style if near expiry
                const remaining = getRemainingTime(createdAt);
                if (remaining && remaining.total <= (2 * 60 * 60 * 1000)) { // Less than 2 hours
                    if (editBtn) {
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
                
                // Very urgent (less than 30 minutes)
                if (remaining && remaining.total <= (30 * 60 * 1000)) {
                    if (editBtn) {
                        editBtn.classList.add('pulse-animation');
                        editBtn.title = `Edit (SEGERA EXPIRED: ${formatTimeRemaining(remaining)})`;
                    }
                    if (deleteBtn) {
                        deleteBtn.classList.add('pulse-animation');
                        deleteBtn.title = `Hapus (SEGERA EXPIRED: ${formatTimeRemaining(remaining)})`;
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
                    const incomeId = modal.id.replace('deleteIncomeModal', '');
                    const buttonGroup = document.querySelector(`[data-income-id="${incomeId}"]`);
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

    // Add notifications for items that will expire soon
    function showExpiryNotifications() {
        const editDeleteButtons = document.querySelectorAll('.edit-delete-buttons');
        let soonToExpire = 0;
        
        editDeleteButtons.forEach(buttonGroup => {
            const createdAt = buttonGroup.getAttribute('data-created');
            const remaining = getRemainingTime(createdAt);
            
            if (remaining && remaining.total <= (30 * 60 * 1000)) { // 30 minutes
                soonToExpire++;
            }
        });
        
        if (soonToExpire > 0) {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = 'alert alert-warning alert-dismissible fade show';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
            `;
            toast.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                ${soonToExpire} pemasukan akan expired dalam 30 menit!
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        }
    }

    // Show notifications every 10 minutes
    setInterval(showExpiryNotifications, 10 * 60 * 1000);
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
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.pulse-animation {
    animation: pulse 2s infinite;
}

.expired-message:hover {
    background-color: #e9ecef;
    cursor: help;
}
</style>

@endsection