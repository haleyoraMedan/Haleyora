@extends('layouts.pegawai')

@section('title','Daftar Mobil')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-car"></i> Daftar Mobil</h4>
    </div>
    <div class="card-body">
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div>
                <input type="text" id="search" placeholder="Cari No Polisi atau Merek..." class="form-control" style="min-width:300px;">
            </div>
            <div>
                <a href="{{ route('pemakaian.pilihMobil') }}" class="btn btn-success">Pilih Mobil untuk Pemakaian</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>No Polisi</th>
                        <th>Merek</th>
                        <th>Tipe</th>
                        <th>Penempatan</th>
                    </tr>
                </thead>
                <tbody id="mobilTable">
                    @php $found = false; $i = 0; @endphp
                    @foreach($mobils as $m)
                        @if( ($m->penempatan_id ?? $m->id_penempatan) == Auth::user()->penempatan_id )
                            @php $i++; @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $m->no_polisi }}</td>
                                <td>{{ $m->merek->nama_merek ?? '-' }}</td>
                                <td>{{ $m->tipe ?? '-' }}</td>
                                <td>{{ $m->penempatan->nama_kantor ?? '-' }}</td>
                            </tr>
                            @php $found = true; @endphp
                        @endif
                    @endforeach
                    @if(! $found)
                        <tr><td colspan="5" class="text-center">Tidak ada data mobil</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('search').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#mobilTable tr').forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.indexOf(q) !== -1 ? '' : 'none';
    });
});
</script>
@endpush

@endsection
