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
                        <button type="submit" class="btn btn-sm btn-outline-success mr-2">Filter</button>
                        @if(request()->hasAny(['category', 'start_date', 'end_date']))
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
                                        <h4>Rp {{ $incomes->count() > 0 ? number_format($incomes->sum('amount') /
                                            $incomes->count(), 0, ',', '.') : '0' }}</h4>
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
                        <td>{{ date('d/m/Y', strtotime($income->date)) }}</td>
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
                        <td>{{ Str::limit($income->description, 50) }}</td>
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
                                <a href="{{ route('incomes.show', $income->id_income) }}" class="btn btn-sm btn-info"
                                    title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('incomes.editPage', $income->id_income) }}"
                                    class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                    data-target="#deleteIncomeModal{{ $income->id_income }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
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
                        <img src="{{ asset('storage/incomes/' . $income->proof_image) }}" class="img-fluid" alt="Proof"
                            style="max-height: 500px;">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route('incomes.delete', $income->id_income) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection