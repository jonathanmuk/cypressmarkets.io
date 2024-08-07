<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch performance data (you'll need to adjust these queries based on your actual data structure)
$sql = "SELECT 
            SUM(CASE WHEN type = 'trade' AND amount > 0 THEN amount ELSE 0 END) as total_profit,
            SUM(CASE WHEN type = 'trade' AND amount < 0 THEN ABS(amount) ELSE 0 END) as total_loss,
            COUNT(DISTINCT CASE WHEN type = 'trade' AND amount > 0 THEN id END) as winning_trades,
            COUNT(DISTINCT CASE WHEN type = 'trade' AND amount < 0 THEN id END) as losing_trades
        FROM transactions 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$performance = $result->fetch_assoc();

// Calculate win rate
$total_trades = $performance['winning_trades'] + $performance['losing_trades'];
$win_rate = $total_trades > 0 ? ($performance['winning_trades'] / $total_trades) * 100 : 0;

// Example monthly performance data (replace with actual data from your database)
$monthly_performance = [
    ['month' => 'Jan', 'profit' => 1200],
    ['month' => 'Feb', 'profit' => -500],
    ['month' => 'Mar', 'profit' => 800],
    ['month' => 'Apr', 'profit' => 1500],
    ['month' => 'May', 'profit' => -200],
    ['month' => 'Jun', 'profit' => 1000],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance - Cypress Markets</title>
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
        .performance-container {
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
        .performance-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .performance-card h3 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="performance-container">
            <h2 class="text-center mb-4">Trading Performance</h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="performance-card">
                        <h3>Profit/Loss Summary</h3>
                        <p>Total Profit: <strong class="text-success">$<?php echo number_format($performance['total_profit'], 2); ?></strong></p>
                        <p>Total Loss: <strong class="text-danger">$<?php echo number_format($performance['total_loss'], 2); ?></strong></p>
                        <p>Net P/L: <strong class="<?php echo ($performance['total_profit'] - $performance['total_loss'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                            $<?php echo number_format($performance['total_profit'] - $performance['total_loss'], 2); ?>
                        </strong></p>
                    </div>
                </div>
                 <div class="col-md-6 mb-4">
                    <div class="performance-card">
                        <h3>Trade Statistics</h3>
                        <p>Winning Trades: <strong><?php echo $performance['winning_trades']; ?></strong></p>
                        <p>Losing Trades: <strong><?php echo $performance['losing_trades']; ?></strong></p>
                        <p>Win Rate: <strong><?php echo number_format($win_rate, 2); ?>%</strong></p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4>Monthly Performance</h4>
                    <div class="chart-container">
                        <canvas id="monthlyPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>Profit/Loss Distribution</h4>
                    <div class="chart-container">
                        <canvas id="profitLossDistributionChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Win/Loss Ratio</h4>
                    <div class="chart-container">
                        <canvas id="winLossRatioChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Performance Chart
        var monthlyPerformanceCtx = document.getElementById('monthlyPerformanceChart').getContext('2d');
        var monthlyPerformanceChart = new Chart(monthlyPerformanceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthly_performance, 'month')); ?>,
                datasets: [{
                    label: 'Monthly Profit/Loss',
                    data: <?php echo json_encode(array_column($monthly_performance, 'profit')); ?>,
                    backgroundColor: function(context) {
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        return value < 0 ? 'rgba(255, 99, 132, 0.5)' : 'rgba(75, 192, 192, 0.5)';
                    },
                    borderColor: function(context) {
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        return value < 0 ? 'rgb(255, 99, 132)' : 'rgb(75, 192, 192)';
                    },
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Profit/Loss ($)'
                        }
                    }
                }
            }
        });

        // Profit/Loss Distribution Chart
        var profitLossDistributionCtx = document.getElementById('profitLossDistributionChart').getContext('2d');
        var profitLossDistributionChart = new Chart(profitLossDistributionCtx, {
            type: 'pie',
            data: {
                labels: ['Profit', 'Loss'],
                datasets: [{
                    data: [<?php echo $performance['total_profit']; ?>, <?php echo $performance['total_loss']; ?>],
                    backgroundColor: ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'],
                    borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Win/Loss Ratio Chart
        var winLossRatioCtx = document.getElementById('winLossRatioChart').getContext('2d');
        var winLossRatioChart = new Chart(winLossRatioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Winning Trades', 'Losing Trades'],
                datasets: [{
                    data: [<?php echo $performance['winning_trades']; ?>, <?php echo $performance['losing_trades']; ?>],
                    backgroundColor: ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'],
                    borderColor: ['rgb(75, 192, 192)', 'rgb(255, 99, 132)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>