@extends('layouts.app')

@section('content')
<div class="container-fluid px-5">
    <div class="row">
        <div class="table-responsive shadow-sm p-3">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="{{ route('expenses.addPage') }}" class="btn btn-sm btn-primary">
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
                        <button type="submit" class="btn btn-sm btn-outline-primary mr-2">Filter</button>
                        @if(request()->hasAny(['category', 'start_date', 'end_date']))
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
                        <td>{{ date('d/m/Y', strtotime($expense->date)) }}</td>
                        <td>
                            <span class="text-danger font-weight-bold">
                                Rp {{ number_format($expense->amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @foreach($categories as $category)
                            @if($category->id_category == $expense->id_category)
                            <span class="badge badge-secondary">{{ $category->name_category }}</span>
                            @endif
                            @endforeach
                        </td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
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
                                <a href="{{ route('expenses.editPage', $expense->id_expense) }}"
                                    class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal"
                                    data-target="#deleteExpenseModal{{ $expense->id_expense }}" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route('expenses.delete', $expense->id_expense) }}" method="POST"
                            class="d-inline">
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