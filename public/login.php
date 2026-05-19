<?php
session_start();

// If someone is already logged in, send them straight to their dashboard
if (isset($_SESSION['user_role'])) {
    header("Location: " . ($_SESSION['user_role'] === 'admin' ? "admin_dashboard.php" : "student_dashboard.php"));
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_input = trim($_POST['email']);
    $password_input = trim($_POST['password']);
    
    // Determine which Firebase node to look at
    $user_node = "";
    if ($email_input === "admin@gmail.com") {
        $user_node = "admin01";
    } elseif ($email_input === "rendell@gmail.com") {
        $user_node = "student01";
    } else {
        // For new students created by the admin, convert "test@gmail.com" to "student_test_gmail_com"
        $user_node = "student_" . str_replace(['.', '@'], '_', $email_input);
    }

    // Your exact Firebase URL targeting the user's specific path
    $firebase_url = "https://studentportal01-9ddef-default-rtdb.asia-southeast1.firebasedatabase.app/Users/" . $user_node . ".json";

    // Ask Firebase for this user's data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebase_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    $user_data = json_decode($response, true);

    // If the user exists and the password matches what's in Firebase
    if ($user_data !== null && isset($user_data['password']) && $user_data['password'] === $password_input) {
        $_SESSION['user_email'] = $email_input;
        $_SESSION['user_name'] = $user_data['Full name'] ?? $user_data['name'] ?? "Student";
        
        // If the node name starts with 'admin', they are an admin. Otherwise, they are a student.
        $_SESSION['user_role'] = (strpos($user_node, 'admin') !== false) ? 'admin' : 'student';

        // Redirect to the correct dashboard
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    } else {
        $error_message = "Invalid email or password connection.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Student Portal</h1>
            <p class="text-slate-500 mt-2">Sign in to your account</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm text-center mb-5 border border-red-100">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" required placeholder="e.g., admin@gmail.com" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>
            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition-colors shadow-lg shadow-indigo-100">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>