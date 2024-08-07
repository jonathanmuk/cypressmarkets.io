<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['current_page'])) {
    $_SESSION['current_page'] = 'dashboard';
}

if (isset($_GET['page'])) {
    $_SESSION['current_page'] = $_GET['page'];
}

$current_page = $_SESSION['current_page'];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // Handle error - user not found
    exit("User not found");
}

// Calculate account balance
$sql = "SELECT SUM(CASE WHEN type = 'deposit' THEN amount ELSE -amount END) AS balance 
        FROM transactions 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$balance_row = $result->fetch_assoc();
$user['account_balance'] = $balance_row['balance'] ?? 0;

// Calculate profit/loss (you may need to adjust this based on how you're tracking trades)
$sql = "SELECT SUM(amount) AS profit_loss 
        FROM transactions 
        WHERE user_id = ? AND type = 'trade'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profit_loss_row = $result->fetch_assoc();
$user['profit_loss'] = $profit_loss_row['profit_loss'] ?? 0;

// Count open positions (you may need to create a separate table for open positions)
$sql = "SELECT COUNT(*) AS open_positions 
        FROM open_positions 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$open_positions_row = $result->fetch_assoc();
$user['open_positions'] = $open_positions_row['open_positions'] ?? 0;

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trader Dashboard - Cypress Markets</title>
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-info d-md-none">
                <i class="fas fa-align-left"></i>
            </button>
            <a class="navbar-brand" href="#">Cypress Markets</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
    <li><a class="dropdown-item" href="#" data-content="settings" data-section="profile">Profile</a></li>
    <li><a class="dropdown-item" href="#" data-content="settings" data-section="settings">Settings</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
</ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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

            <!-- Main content -->
            <main id = "content "class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div id="dashboard">
                <div class="m-4 p-4 bg-white shadow rounded">
                <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Account Balance</h5>
                                <h2 class="card-text">$<?php echo number_format($user['account_balance'], 2); ?></h2>
                    
                </div>
            </div>
        </div>  
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Profit/Loss</h5>
                                <h2 class="card-text <?php echo $user['profit_loss'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($user['profit_loss'], 2); ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Open Positions</h5>
                                <h2 class="card-text"><?php echo $user['open_positions']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                                <!-- Real-time Stock Prices -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
                <h5 class="card-title">Real-time Stock Prices</h5>
                <div id="stock-ticker" class="d-flex justify-content-between">
                    <div>AAPL: <span class="stock-price">$150.25</span></div>
                    <div>GOOGL: <span class="stock-price">$2,750.50</span></div>
                    <div>MSFT: <span class="stock-price">$305.75</span></div>
                    <div>AMZN: <span class="stock-price">$3,400.00</span></div>
                </div>
            </div>
        </div>
    </div>
</div>


                <div class="row animate__animated animate__fadeIn animate__delay-1s">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Account Performance</h5>
                                <canvas id="performanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Quick Trade</h5>
                                <form>
                                    <div class="mb-3">
                                        <label for="tradePair" class="form-label">Trading Pair</label>
                                        <select class="form-select" id="tradePair">
                                            <option>EUR/USD</option>
                                            <option>GBP/USD</option>
                                            <option>USD/JPY</option>
                                            <option>BTC/USD</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tradeAmount" class="form-label">Amount</label>
                                        <input type="number" class="form-control" id="tradeAmount">
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-trade">Buy</button>
                                        <button type="submit" class="btn btn-danger">Sell</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row animate__animated animate__fadeIn animate__delay-2s">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Open Positions</h5>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Symbol</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Profit/Loss</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>EUR/USD</td>
                                            <td>Buy</td>
                                            <td>1.00</td>
                                            <td class="text-success">+$50.00</td>
                                        </tr>
                                        <!-- Add more rows as needed -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Transactions</h5>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Deposit
                                        <span class="badge bg-primary rounded-pill">$1000</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Withdrawal
                                        <span class="badge bg-danger rounded-pill">$500</span>
                                    </li>
                                    <!-- Add more items as needed -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
<!-- Live Market Data -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Live Market Data</h5>
                <canvas id="liveMarketChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Financial News</h5>
                <ul class="list-group" id="financial-news">
                    <li class="list-group-item">Stock Market Reaches All-Time High</li>
                    <li class="list-group-item">Fed Announces Interest Rate Decision</li>
                    <li class="list-group-item">Tech Giants Report Quarterly Earnings</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
</div>
    <!-- Placeholder for loading content dynamically -->
                <div id="dynamic-content" class="animate__animated animate__fadeIn"></div>
</div>

            </main>
        

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       $(document).ready(function() {
    // Load dashboard by default
    //loadContent('dashboard');
    // Set dashboard link as active
    $('a[data-content="dashboard"]').addClass('active');

    // Sidebar toggle
    $('#sidebarCollapse').on('click', function() {
        $('#sidebar, #content').toggleClass('active');
    });

    // Load content dynamically
    $('a[data-content]').on('click', function(e) {
        e.preventDefault();
        var contentId = $(this).data('content');
        loadContent(contentId);

        // Remove active class from all links and add to the clicked one
        $('a[data-content]').removeClass('active');
        $(this).addClass('active');
    });

     // ADD: Handle dropdown menu items
        $('.dropdown-item[data-content]').on('click', function(e) {
            e.preventDefault();
            var contentId = $(this).data('content');
            var section = $(this).data('section');
            loadContent(contentId, section);

            // Remove active class from sidebar links
            $('a[data-content]').removeClass('active');
        });

    function loadContent(contentId, section) {
            if (contentId === 'dashboard') {
                $('#dynamic-content').hide();
                $('#dashboard').show();
            } else {
                $('#dashboard').hide();
                $('#dynamic-content').show().html('<p>Loading...</p>'); // Show a loading message

                $.ajax({
                    url: contentId + '.php',
                    method: 'GET',
                    data: { section: section }, // Pass the section as a parameter
                    success: function(response) {
                        $('#dynamic-content').html(response);
                        // Scroll to the specific section if provided
                        if (section) {
                            var sectionElement = $('#' + section);
                            if (sectionElement.length) {
                                $('html, body').animate({
                                    scrollTop: sectionElement.offset().top - 70 // Adjust for navbar height
                                }, 500);
                            }
                        }
                    },
                    
                });
            }
        }

    // Performance Chart using Chart.js
    var ctx = document.getElementById('performanceChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Account Balance',
                data: [9000, 9500, 10000, 9800, 10200, 10000],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // Simulated real-time stock price updates
    function updateStockPrices() {
        $('.stock-price').each(function() {
            var price = parseFloat($(this).text().replace('$', ''));
            var change = (Math.random() - 0.5) * 2;
            $(this).text('$' + (price + change).toFixed(2));
            $(this).removeClass('text-success text-danger');
            if (change > 0) {
                $(this).addClass('text-success');
            } else if (change < 0) {
                $(this).addClass('text-danger');
            }
        });
    }

    setInterval(updateStockPrices, 5000);

    // Simulated financial news updates
    function updateFinancialNews() {
        var news = [
            "New Economic Policy Announced",
            "Major Merger in Tech Industry",
            "Global Trade Tensions Ease",
            "Cryptocurrency Market Surges",
            "Oil Prices Fluctuate Amid Geopolitical Tensions"
        ];
        var randomNews = news[Math.floor(Math.random() * news.length)];
        $('#financial-news').prepend('<li class="list-group-item">' + randomNews + '</li>');
        $('#financial-news li:last').remove();
    }

    setInterval(updateFinancialNews, 10000);
});

    </script>
</body>
</html>
