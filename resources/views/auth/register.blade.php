<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(120deg, #2c3e50, #3498db);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            background: #fff;
            width: 400px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,.2);
        }

        .card h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
        }

        .error {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 4px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Register User</h2>

    <form method="POST" action="/register">
        @csrf

        <div class="form-group">
            <label>NIP</label>
            <input type="text" name="nip" value="{{ old('nip') }}">
            @error('nip') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username') }}">
            @error('username') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password">
            @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <option value="">-- Pilih Role --</option>
                <option value="admin">Admin</option>
                <option value="pegawai">Pegawai</option>
            </select>
            @error('role') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Penempatan ID</label>
            <input type="number" name="penempatan_id" value="{{ old('penempatan_id') }}">
            @error('penempatan_id') <div class="error">{{ $message }}</div> @enderror
        </div>

        <button type="submit">Daftar</button>
    </form>
</div>

</body>
</html>
