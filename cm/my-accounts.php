<?php
// Debugging information
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's accounts
$sql = "SELECT * FROM user_accounts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$accounts = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
// Fetch the active account
$active_account_id = $_SESSION['active_account_id'] ?? null;
$active_account = null;

foreach ($accounts as $account) {
    if ($account['id'] == $active_account_id) {
        $active_account = $account;
        break;
    }
}

// If no active account is set or the active account wasn't found, default to the first account
if (!$active_account && !empty($accounts)) {
    $active_account = $accounts[0];
    $_SESSION['active_account_id'] = $active_account['id'];
}

// If there are no accounts at all, set a default value
if (!$active_account) {
    $active_account = [
        'id' => 0,
        'account_name' => 'No Account',
        'account_number' => 'N/A',
        'balance' => 0,
        'account_type' => 'N/A'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Accounts - Cypress Markets</title>
    <link rel="stylesheet" href="https://unpkg.com/magic-ui/dist/magic-ui.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .account-card {
            transition: transform 0.3s;
        }
        .account-card:hover {
            transform: translateY(-5px);
        }
        .active-account {
            border: 2px solid #007bff;
        }
    </style>
</head>
<body>
<div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#Dashboard" data-content="dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Accounts" data-content="my-accounts">
                                <i class="fas fa-user-circle"></i> My Accounts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Deposit" data-content="deposit">
                                <i class="fas fa-money-bill-wave"></i> Deposit
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Withdraw" data-content="withdraw">
                                <i class="fas fa-hand-holding-usd"></i> Withdraw
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Transactions" data-content="transaction-history">
                                <i class="fas fa-history"></i> Transaction History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Analytics" data-content="analytics">
                                <i class="fas fa-chart-bar"></i> Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Performance" data-content="performance">
                                <i class="fas fa-chart-line"></i> Performance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Support" data-content="support-hub">
                                <i class="fas fa-headset"></i> Support Hub
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Settings" data-content="settings">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Help" data-content="help">
                                <i class="fas fa-question-circle"></i> Help
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

    <div class="container-fluid py-5">
        <div id="accountsContent">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h1 class="display-4">My Accounts</h1>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($active_account['account_name'] ?? 'No Account'); ?>
</button>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <?php foreach ($accounts as $account): ?>
                            <li><a class="dropdown-item" href="#" data-account-id="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['account_name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach ($accounts as $account): ?>
                <div class="col-md-4 mb-4">
                    <div class="card account-card <?php echo ($account['id'] == $active_account['id']) ? 'active-account' : ''; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($account['account_name'] ?? 'N/A'); ?></h5>
<h6 class="card-subtitle mb-2 text-muted">Account #: <?php echo htmlspecialchars($account['account_number'] ?? 'N/A'); ?></h6>
<p class="card-text">
    <i class="fas fa-balance-scale me-2"></i>
    Balance: $<?php echo number_format($account['balance'] ?? 0, 2); ?>
</p>
<p class="card-text">
    <i class="fas fa-tag me-2"></i>
    Type: <?php echo htmlspecialchars($account['account_type'] ?? 'N/A'); ?>
</p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#accountDetailsModal<?php echo $account['id']; ?>">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Account Details Modal (Keep your existing modal code here) -->
            <?php endforeach; ?>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Balance</h5>
                        <h2 class="card-text">$<?php echo number_format(array_sum(array_column($accounts, 'balance')), 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <a href="#" class="btn btn-success mb-2 me-2">
                            <i class="fas fa-plus-circle me-2"></i>Open New Account
                        </a>
                        <a href="#" class="btn btn-info">
                            <i class="fas fa-exchange-alt me-2"></i>Transfer Between Accounts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
    // Account switching functionality
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        var accountId = $(this).data('account-id');
        
        $.ajax({
            url: 'update_active_account.php',
            method: 'POST',
            data: { account_id: accountId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Refresh the page content
                    refreshAccountsContent();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while switching accounts.');
            }
        });
    });

    function refreshAccountsContent() {
        $.ajax({
            url: 'my_accounts.php',
            method: 'GET',
            success: function(response) {
                $('#accountsContent').html($(response).find('#accountsContent').html());
                
                // Reinitialize any necessary event listeners or plugins
                initializeEventListeners();
            },
            error: function() {
                alert('An error occurred while refreshing the content.');
            }
        });
    }

    function initializeEventListeners() {
        // Reinitialize the account switching functionality
        $('.dropdown-item').on('click', function(e) {
            // ... (same code as above)
        });

        // Reinitialize any other event listeners or plugins here
    }
});
    </script>
</body>
</html>
