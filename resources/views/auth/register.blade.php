<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { max-width: 400px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
        h2 { text-align:center; margin-bottom:20px; }
        .mb-3 { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; font-weight:bold; }
        input { width:100%; padding:8px; border-radius:4px; border:1px solid #ccc; }
        button { width:100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; }
        button:hover { background:#218838; }
    </style>
</head>
<body>
<div class="container">
    <h2>Student Register</h2>
    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button type="submit">Register</button>
    </form>
    <p style="text-align:center; margin-top:10px;">Already have an account? <a href="{{ route('login') }}">Login</a></p>
</div>
</body>
</html>
