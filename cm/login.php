<?php
require_once 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Debug: Print out the submitted values
    echo "Submitted username/email: " . htmlspecialchars($username_or_email) . "<br>";

    // Check if the input is an email
    if (filter_var($username_or_email, FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
    } else {
        $sql = "SELECT id, username, email, password FROM users WHERE username = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Debug: Print out the number of rows returned
    echo "Number of rows found: " . $result->num_rows . "<br>";

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Compare passwords directly
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            
            // Debug: Print out success message
            echo "Login successful. Redirecting...";
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username/email or password";
        }
    } else {
        $error = "Invalid username/email or password";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cypress Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" action="login.php" method="post">
            <h2>Cypress Markets</h2>
            <div class="mb-3">
                <label>Username or Email</label>
                <input type="text" class="form-control" name="username_or_email" placeholder="Username or Email" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-50 text-center">Login</button>
            <div class="mt-3 text-center">
                <p>Or login with Google:</p>
                <div class="g-signin2" data-onsuccess="onSignIn"></div>
            </div>
            <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign up</a></p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function onSignIn(googleUser) {
            var id_token = googleUser.getAuthResponse().id_token;
            // Send the token to your server
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'google_auth.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                console.log('Signed in as: ' + xhr.responseText);
            };
            xhr.send('idtoken=' + id_token);
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
