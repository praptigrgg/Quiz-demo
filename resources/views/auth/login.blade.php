<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { max-width: 400px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        h2 { text-align:center; margin-bottom:20px; }
        .mb-3 { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; font-weight:bold; }
        input { width:100%; padding:8px; border-radius:4px; border:1px solid #ccc; }
        button { width:100%; padding:10px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        button:hover { background:#0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Login</h2>
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" required>
            @error('email')<span style="color:red">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" required>
            @error('password')<span style="color:red">{{ $message }}</span>@enderror
        </div>
        <button type="submit">Login</button>
    </form>
    <p style="text-align:center; margin-top:10px;">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
</div>
</body>
</html>
