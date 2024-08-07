<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $notification_preference = filter_input(INPUT_POST, 'notification_preference', FILTER_SANITIZE_STRING);
    
    // Update user settings
    $sql = "UPDATE users SET email = ?, phone = ?, notification_preference = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $email, $phone, $notification_preference, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Your settings have been updated successfully.";
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error_message = "There was an error updating your settings. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Cypress Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding-top: 56px; /* Height of the navbar */
        }
        .navbar {
        position: fixed;
        top: 0;
        left: 0;
         right: 0;
         z-index: 1030;
        }
        .navbar-dark {
            background-color: #1a1a2e;
        }
        .nav-link {
            color: black;
            font-weight: bold;
        }
        /* Active menu item */
     .nav-link.active {
    background-color: #d1d1d1;
    border-left: 4px solid #1a1a2e; /* Match the color of your navbar */
    }
   /* Hover effect */
   .nav-link:hover {
    border-left: 4px solid #1a1a2e; /* Match the color of your navbar */
}
        .sidebar {
            background-color: #f8f9fa;
            min-height: calc(100vh - 56px);
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .btn-trade {
            background-color: #4CAF50;
            color: white;
        }
        .btn-trade:hover {
            background-color: #45a049;
        }
        #sidebar {
        position: fixed;
        top: 56px;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding-top: 20px;
        overflow-y: auto;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        transition: all 0.3s;
        width: 220px;
        background-color: #f8f9fa;
    }

    #sidebar.active {
        margin-left: -250px;
    }

    #content {
        margin-left: 250px;
        padding-top: 20px;
        transition: all 0.3s;
    }

    #content.active {
        margin-left: 0;
        width: 100%;
    }

    @media (max-width: 768px) {
        #sidebar {
            margin-left: -220px;
        }
        #sidebar.active {
            margin-left: 0;
        }
        #content {
            margin-left: 0;
        }
        #content.active {
        margin-left: 230px;
        }

        #sidebarCollapse {
        display: block;
        }
    }
        .settings-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="settings-container">
            <h2 class="text-center mb-4">Account Settings</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">Profile</button>
                        <button class="nav-link" id="v-pills-security-tab" data-bs-toggle="pill" data-bs-target="#v-pills-security" type="button" role="tab" aria-controls="v-pills-security" aria-selected="false">Security</button>
                        <button class="nav-link" id="v-pills-notifications-tab" data-bs-toggle="pill" data-bs-target="#v-pills-notifications" type="button" role="tab" aria-controls="v-pills-notifications" aria-selected="false">Notifications</button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <h3>Profile Settings</h3>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="v-pills-security" role="tabpanel" aria-labelledby="v-pills-security-tab">
                            <h3>Security Settings</h3>
                            <p>Change your password or enable two-factor authentication here.</p>
                            <!-- Add password change and 2FA options here -->
                        </div>
                        <div class="tab-pane fade" id="v-pills-notifications" role="tabpanel" aria-labelledby="v-pills-notifications-tab">
                            <h3>Notification Preferences</h3>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="notification_preference" class="form-label">Notification Method</label>
                                    <select class="form-select" id="notification_preference" name="notification_preference">
                                        <option value="email" <?php echo $user['notification_preference'] == 'email' ? 'selected' : ''; ?>>Email</option>
                                        <option value="sms" <?php echo $user['notification_preference'] == 'sms' ? 'selected' : ''; ?>>SMS</option>
                                        <option value="both" <?php echo $user['notification_preference'] == 'both' ? 'selected' : ''; ?>>Both Email and SMS</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>