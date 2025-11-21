<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Join Zoom Meeting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .mb-3 {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Join Zoom Meeting</h2>
        <form method="POST" action="{{ route('zoom.handleJoin') }}">
            @csrf
            <div class="mb-3">
                <label for="meeting_number">Meeting Number</label>
                <input type="text" name="meeting_number" id="meeting_number" required>
            </div>
            <div class="mb-3">
                <label for="user_name">Your Name</label>
                <input type="text" name="user_name" value="{{ $userName }}" readonly>
            </div>
            <div class="mb-3">
                <label for="passcode">Meeting Passcode (optional)</label>
                <input type="text" name="passcode" id="passcode">
            </div>
            <div class="mb-3">
                <label for="role">Role</label>
                <select name="role" id="role">
                    <option value="0" selected>Participant</option>
                    <option value="1">Host</option>
                </select>
            </div>
            <button type="submit">Join Meeting</button>
        </form>
    </div>
</body>

</html>
