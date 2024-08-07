<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch analytics data (you'll need to adjust these queries based on your actual data structure)
$sql = "SELECT 
            SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as total_deposits,
            SUM(CASE WHEN type = 'withdraw' THEN amount ELSE 0 END) as total_withdrawals,
            COUNT(DISTINCT CASE WHEN type = 'trade' THEN id END) as total_trades
        FROM transactions 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$analytics = $result->fetch_assoc();

// Fetch currency pair distribution (example data - replace with actual data from your database)
$currency_pairs = [
    ['pair' => 'EUR/USD', 'percentage' => 35],
    ['pair' => 'GBP/USD', 'percentage' => 25],
    ['pair' => 'USD/JPY', 'percentage' => 20],
    ['pair' => 'AUD/USD', 'percentage' => 15],
    ['pair' => 'USD/CAD', 'percentage' => 5],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Cypress Markets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .analytics-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="analytics-container">
            <h2 class="text-center mb-4">Analytics Dashboard</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Deposits</h5>
                            <p class="card-text fs-4">$<?php echo number_format($analytics['total_deposits'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Withdrawals</h5>
                            <p class="card-text fs-4">$<?php echo number_format($analytics['total_withdrawals'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Trades</h5>
                            <p class="card-text fs-4"><?php echo $analytics['total_trades']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>Currency Pair Distribution</h4>
                    <div class="chart-container">
                        <canvas id="currencyPairChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Trading Activity</h4>
                    <div class="chart-container">
                        <canvas id="tradingActivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Currency Pair Distribution Chart
        var currencyPairCtx = document.getElementById('currencyPairChart').getContext('2d');
        var currencyPairChart = new Chart(currencyPairCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($currency_pairs, 'pair')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($currency_pairs, 'percentage')); ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Currency Pair Distribution'
                }
            }
        });

        // Trading Activity Chart (example data - replace with actual data)
        var tradingActivityCtx = document.getElementById('tradingActivityChart').getContext('2d');
        var tradingActivityChart = new Chart(tradingActivityCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Number of Trades',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#36A2EB',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>