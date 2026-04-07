<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Supplier Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script> </head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form action="actions/login_action.php" method="POST" class="bg-white p-8 rounded shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Supplier Tracker</h2>
        <input type="text" name="username" placeholder="Username" class="w-full p-2 mb-4 border rounded" required>
        <input type="password" name="password" placeholder="Password" class="w-full p-2 mb-4 border rounded" required>
        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Login</button>
    </form>
</body>
</html>