<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="max-w-4xl mx-auto p-8">
        <div class="bg-white rounded-2xl p-8 shadow-md border border-slate-100 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <p class="text-slate-500 mt-1">Student Portal Dashboard Workspace</p>
            </div>
            <a href="logout.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                Log Out
            </a>
        </div>
    </div>
</body>
</html>