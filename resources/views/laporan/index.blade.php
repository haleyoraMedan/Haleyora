@extends('layouts.pegawai')

@section('title','Riwayat Laporan')

@section('content')
<div class="card">
  <div class="card-body">
    <h4>Riwayat Laporan Kerusakan</h4>
    @if($laporans->isEmpty())
      <div class="alert alert-info">Belum ada laporan.</div>
    @else
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Mobil</th>
              <th>Kondisi</th>
              <th>Catatan</th>
              <th>Status</th>
              <th>Keterangan Admin</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($laporans as $lap)
              <tr>
                <td>{{ $lap->id }}</td>
                <td>{{ optional($lap->mobil)->no_polisi ?? '-' }}</td>
                <td>{{ $lap->kondisi }}</td>
                <td>{{ $lap->catatan ?? '-' }}</td>
                <td>
                  @if($lap->status == \App\Models\LaporanRusak::STATUS_PENDING)
                    <span class="badge badge-pending">Pending</span>
                  @elseif($lap->status == \App\Models\LaporanRusak::STATUS_APPROVED)
                    <span class="badge badge-approved">Approved</span>
                  @elseif($lap->status == \App\Models\LaporanRusak::STATUS_AVAILABLE)
                    <span class="badge badge-available">Selesai</span>
                  @elseif($lap->status == \App\Models\LaporanRusak::STATUS_REJECTED)
                    <span class="badge badge-rejected">Ditolak</span>
                  @endif
                </td>
                <td>{{ $lap->admin_keterangan ?? '-' }}</td>
                <td>{{ $lap->created_at->format('d M Y H:i') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>
@endsection
