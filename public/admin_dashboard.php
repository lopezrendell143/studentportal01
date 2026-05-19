<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";

$base_url = "https://studentportal01-9ddef-default-rtdb.asia-southeast1.firebasedatabase.app/users.json";

// Create Account Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_student'])) {
    $student_name = trim($_POST['student_name']);
    $student_email = trim($_POST['student_email']);
    $student_password = trim($_POST['student_password']);
    $student_role = $_POST['student_role']; // admin or student
    
    $firebase_key = str_replace('.', '_', $student_email);
    $target_url = "https://studentportal01-9ddef-default-rtdb.asia-southeast1.firebasedatabase.app/users/" . $firebase_key . ".json";

    $new_user_data = [
        "name" => $student_name,
        "password" => $student_password,
        "role" => $student_role
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($new_user_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200) {
        $success_message = "Account registration saved to Firebase successfully!";
    } else {
        $error_message = "Could not register account. Check Firebase connection.";
    }
}

// Fetch Accounts to Show in Table
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$all_users_response = curl_exec($ch);
curl_close($ch);

$all_users = json_decode($all_users_response, true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex">

    <aside class="w-64 bg-slate-900 text-white p-6 flex flex-col justify-between hidden md:flex">
        <div>
            <h2 class="text-xl font-bold tracking-wider mb-8 text-indigo-400">PORTAL ADMIN</h2>
            <nav class="space-y-2">
                <a href="#" class="block bg-slate-800 px-4 py-2.5 rounded-lg font-medium text-white">Overview</a>
            </nav>
        </div>
        <a href="logout.php" class="w-full text-center bg-red-600/25 hover:bg-red-600 text-red-400 hover:text-white font-medium py-2 rounded-lg transition-colors block">
            Log Out
        </a>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <h1 class="text-2xl font-bold text-slate-800">Admin Control Panel</h1>
            <span class="text-sm font-medium text-slate-600">Logged in as: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
        </header>

        <?php if (!empty($success_message)): ?>
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl text-sm mb-6 border border-emerald-100"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Create Portal Account</h3>
                <form action="admin_dashboard.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Full Name</label>
                        <input type="text" name="student_name" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Email Address</label>
                        <input type="email" name="student_email" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase mb-1">Account Password</label>
                        <input type="password" name="student_password" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 uppercase mb-1">System Role</label>
                        <select name="student_role" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm outline-none bg-white focus:border-indigo-500">
                            <option value="student">Student</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" name="create_student" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-lg text-sm transition-colors shadow-md">
                        Save Account to Firebase
                    </button>
                </form>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Live Firebase Accounts</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 text-sm font-medium">
                                <th class="pb-3">Name</th>
                                <th class="pb-3">Email</th>
                                <th class="pb-3">Password View</th>
                                <th class="pb-3">Role</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-600 text-sm divide-y divide-slate-100">
                            <?php if(!empty($all_users)): ?>
                                <?php foreach ($all_users as $encoded_email => $info): ?>
                                    <tr>
                                        <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($info['name']); ?></td>
                                        <td class="py-3"><?php echo htmlspecialchars(str_replace('_', '.', $encoded_email)); ?></td>
                                        <td class="py-3 font-mono text-xs text-slate-400"><?php echo htmlspecialchars($info['password']); ?></td>
                                        <td class="py-3">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $info['role'] === 'admin' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($info['role'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">No accounts found in Firebase. Use the form to create your first admin profile.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>