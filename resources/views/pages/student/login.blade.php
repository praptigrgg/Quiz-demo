<h2>Student Login</h2>
<form action="{{ route('student.login') }}" method="POST">
    @csrf
    <input name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button>Login</button>
</form>
