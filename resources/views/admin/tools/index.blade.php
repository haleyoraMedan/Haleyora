@extends('layouts.admin')
@section('title', 'Import / Export Data')

@section('content')
<div class="admin-card">
    <h3>Import / Export (XLSX)</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card p-3 mb-3">
                <h5>Download Template</h5>
                <p>Pilih model untuk mendownload file template XLSX.</p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.tools.template', ['model'=>'user']) }}" class="btn btn-outline-primary">Template User</a>
                    <a href="{{ route('admin.tools.template', ['model'=>'jenis']) }}" class="btn btn-outline-primary">Template Jenis Mobil</a>
                    <a href="{{ route('admin.tools.template', ['model'=>'merek']) }}" class="btn btn-outline-primary">Template Merek</a>
                    <a href="{{ route('admin.tools.template', ['model'=>'mobil']) }}" class="btn btn-outline-primary">Template Mobil</a>
                    <a href="{{ route('admin.tools.template', ['model'=>'penempatan']) }}" class="btn btn-outline-primary">Template Penempatan</a>
                </div>
            </div>

            <div class="card p-3">
                <h5>Export Data</h5>
                <p>Pilih model lalu klik Export untuk mendownload file XLSX berisi semua data (atau gunakan parameter ids[] untuk export terpilih).</p>
                <form method="POST" action="{{ route('admin.tools.export') }}">
                    @csrf
                    <div class="mb-2">
                        <select name="model" class="form-select">
                            <option value="user">User</option>
                            <option value="jenis">Jenis Mobil</option>
                            <option value="merek">Merek Mobil</option>
                            <option value="mobil">Mobil</option>
                            <option value="penempatan">Penempatan</option>
                        </select>
                    </div>
                    <button class="btn btn-primary">Export Semua</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3">
                <h5>Import Data</h5>
                <p>Unggah file XLSX yang sesuai dengan template. Sistem akan menambahkan atau memperbarui data berdasarkan kolom <strong>id</strong> atau <strong>email/username</strong> (untuk user).</p>
                <form method="POST" action="{{ route('admin.tools.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-2">
                        <select name="model" class="form-select">
                            <option value="user">User</option>
                            <option value="jenis">Jenis Mobil</option>
                            <option value="merek">Merek Mobil</option>
                            <option value="mobil">Mobil</option>
                            <option value="penempatan">Penempatan</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="file" name="file" accept=".xlsx,.xls" required class="form-control">
                    </div>
                    <button class="btn btn-success">Import</button>
                </form>

                <hr>
                <p class="small text-muted">Note: Untuk mengaktifkan fungsi XLSX, install paket <code>phpoffice/phpspreadsheet</code> via Composer. Contoh:</p>
                <pre><code>composer require phpoffice/phpspreadsheet</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection
