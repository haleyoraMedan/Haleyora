<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <style>
        body { 
            font-family: Arial; 
            background: #f4f6f9; 
            padding: 20px; 
        }
        .container { 
            background: #fff; 
            padding: 20px; 
            border-radius: 6px; 
            width: 400px; 
        }
        label { 
            display: block; 
            margin-top: 10px; 
        }
        input, select { 
            width: 100%; 
            padding: 8px; 
            margin-top: 5px; 
        }
        button { 
            margin-top: 15px; 
            padding: 8px; 
            width: 100%; 
            background: #28a745; 
            color: white; 
            border: none; 
            border-radius: 4px; 
        }
        a { 
            display: inline-block; 
            margin-top: 10px; 
            text-decoration: none; 
        }
    </style>
</head>
<body>
<div class="container">
    <h3>Edit User</h3>

    <form action="{{ route('user.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label>NIP</label>
        <input type="text" name="nip" value="{{ $user->nip }}">

        <label>Username</label>
        <input type="text" name="username" value="{{ $user->username }}" required>

        <label>Password (kosongkan jika tidak diganti)</label>
        <input type="text" name="password">

        <label>Role</label>
        <select name="role" required>
            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="pegawai" {{ $user->role == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
        </select>

        <label>Penempatan ID</label>
        <input type="text" name="penempatan_id" value="{{ $user->penempatan_id }}">

        <button type="submit">Update</button>
    </form>

    <a href="{{ route('user.index') }}">← Kembali</a>
</div>
</body>
</html>
