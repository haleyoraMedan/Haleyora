<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root{--primary:#2f80ed;--accent-orange:#ff8f00;--accent-purple:#6a1b9a}
        *{
            box-sizing:border-box
        }
        body{
            font-family:Inter,system-ui,Segoe UI,Arial;
            background:linear-gradient(145deg,
                rgba(105, 27, 154, 0.81) 0%,
                rgba(47, 129, 237, 0.77) 25%,
                rgba(255, 145, 0, 0.8) 50%,
                #f4f6f9 100%
            ); 
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0;
            padding:24px
        }
        .card{
            background:#fff;
            width:100%;
            max-width:550px;
            padding:28px;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(2,6,23,0.4)
        }
        h2{
            margin:0 0 14px;
            font-size:20px;
            color:#0f172a;
            text-align:center
        }
        .form-group{
            margin-bottom:12px
        }
        label{
            display:block;
            font-size:13px;
            color:#445; 
            margin-bottom:6px
        }
        input{
            width:100%;
            padding:10px 12px;
            border-radius:8px;
            border:1px solid #e6eef6;
            font-size:14px
        }
        input:focus{
            outline:2px solid rgba(47,128,237,0.12);
            border-color:var(--primary)
        }
        .btn{
            width:100%;
            padding:11px;
            border-radius:8px;
            border:0;
            background:var(--primary);
            color:#fff;
            font-weight:600;
            cursor:pointer
        }
        .btn:hover{
            background:var(--accent-orange)
        }
        .meta{
            margin-top:10px;
            text-align:center;
            font-size:13px;
            color:#556
        }
        .meta a{
            color:var(--primary);
            text-decoration:none
        }
        .error-box{
            background:#fff1f0;
            border:1px solid #ffd6d4;
            color:#9b1c1c;
            padding:10px;
            border-radius:8px;
            margin-bottom:12px
        }
        ul.errors{
            margin:0;
            padding-left:18px
        }

        /*LOGIN STYLE*/
        .login-wrapper{
            display:flex;
            align-items:center;
            gap:24px;
        }

        .logo-box{
            flex:0 0 160px;
            display:flex;
            justify-content:center;
            align-items:center;
            background:#fff;
            padding:20px;
            border-radius:16px;
            box-shadow:0 10px 30px rgba(0,0,0,0.12)
        }

        .logo-box img{
            width:140px;
            height:140px;
            object-fit:contain;
            filter:drop-shadow(0 4px 8px rgba(0,0,0,0.15));
            transition:transform .3s ease;
        }

        .logo-box img:hover{
            transform:scale(1.05);
        }

        .login-form{
            flex:1;
        }

        .login-form h2{
            text-align:left;
            margin-bottom:14px;
        }

        @media (max-width:420px){
            .card{
                padding:18px;
                border-radius:10px
            }
        }

        @media (max-width: 600px){
            .login-wrapper{
                flex-direction:column;
                text-align:center;
            }

            .login-form h2{
                text-align:center;
            }
        }

    </style>
</head>
<body>

<div class="card">
    <div class="login-wrapper">
    <div class="logo-box">
        <img src="/image/hpi-cabangmedan.png" alt="Logo">
    </div>

    <div class="login-form">
        <h2>Masuk ke Sistem</h2>

        @if ($errors->any())
            <div class="error-box">
                <ul class="errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="error-box">{{ session('error') }}</div>
        @endif

        <form action="{{ url('/login') }}" method="POST" autocomplete="on">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Masuk</button>
        </form>
    </div>
</div>

</div>

</body>
</html>
