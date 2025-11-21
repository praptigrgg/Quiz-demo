<h2>Student Registration</h2>
<form action="{{ route('student.register') }}" method="POST">
    @csrf
    <input name="name" placeholder="Name" required><br>
    <input name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button>Register</button>
</form>
