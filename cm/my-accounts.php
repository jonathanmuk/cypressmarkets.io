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
<html>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
        .modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 1rem);
}

.modal-content {
    background-color: #f8f9fa;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
    background-color: #e9ecef;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}

.modal-body {
    padding: 2rem;
}

.account-type-options {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.account-type-option {
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    transition: all 0.3s ease;
}

.account-type-option:hover {
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.account-type-option .badge {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    margin-bottom: 0.5rem;
}

.account-type-option p {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.select-account-type {
    width: 100%;
}
#newAccountForm {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        #newAccountForm h3 {
            margin-bottom: 20px;
        }

        #newAccountForm .form-group {
            margin-bottom: 15px;
        }

        #newAccountForm label {
            font-weight: bold;
        }

        #newAccountForm .btn {
            margin-top: 10px;
        }
        .account-type-carousel {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
        overflow: hidden;
    }

    .account-type-card {
        display: none;
        width: 100%;
    }

    .account-type-card.active {
        display: block;
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

    .card-header {
        background-color: #f8f9fa;
        border-bottom: none;
        padding: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .badge {
        font-size: 0.7rem;
        padding: 0.3em 0.5em;
        margin-top: 0.3rem;
    }

    .carousel-controls {
        text-align: center;
    }

    .carousel-controls .btn {
        margin: 0 0.5rem;
    }
    password-requirements li {
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        .password-requirements .badge {
            width: 16px;
            height: 16px;
            padding: 0;
            line-height: 16px;
            text-align: center;
            border-radius: 50%;
            font-size: 10px;
            margin-right: 5px;
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

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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
                                        <?php if (empty($accounts)): ?>
                                            <li><a class="dropdown-item" href="#" id="openNewAccountBtn">Open New Account</a></li>
                                        <?php else: ?>
                                            <?php foreach ($accounts as $account): ?>
                                                <li><a class="dropdown-item" href="#" data-account-id="<?php echo $account['id']; ?>"><?php echo htmlspecialchars($account['account_name']); ?></a></li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                        <a href="#" class="btn btn-success mb-2 me-2" id="openNewAccountBtnQuick">
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
            </main>
        </div>
    </div>



      <!-- Account Type Selection -->
      <div id="accountTypeSelection" style="display: none;">
                    <h3 class="mb-4">Select Account Type</h3>
                    <div class="account-type-carousel">
                        <div class="account-type-card" id="standardCard">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Standard Account</h5>
                                    <span class="badge bg-warning">Standard</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">Minimum Deposit</p>
                                    <h6 class="mb-2">$10 USD</h6>
                                    <hr>
                                    <p class="card-text small">Spread</p>
                                    <h6 class="mb-2">From 0.20</h6>
                                    <hr>
                                    <p class="card-text small">Commission</p>
                                    <h6>No Commission</h6>
                                </div>
                            </div>
                        </div>
                        <div class="account-type-card" id="proCard">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Pro Account</h5>
                                    <span class="badge bg-success">Pro</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">Minimum Deposit</p>
                                    <h6 class="mb-2">$500 USD</h6>
                                    <hr>
                                    <p class="card-text small">Spread</p>
                                    <h6 class="mb-2">From 0.10</h6>
                                    <hr>
                                    <p class="card-text small">Commission</p>
                                    <h6>No Commission</h6>
                                </div>
                            </div>
                        </div>
                        <div class="account-type-card" id="basicCard">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Basic Account</h5>
                                    <span class="badge bg-dark">Basic</span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text small">Minimum Deposit</p>
                                    <h6 class="mb-2">$10 USD</h6>
                                    <hr>
                                    <p class="card-text small">Spread</p>
                                    <h6 class="mb-2">From 0.30</h6>
                                    <hr>
                                    <p class="card-text small">Commission</p>
                                    <h6>No Commission</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="carousel-controls mt-3">
                        <button class="btn btn-outline-primary btn-sm" id="prevCard"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn btn-outline-primary btn-sm" id="nextCard"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-primary" id="selectAccountType">Select This Account Type</button>
                    </div>
                </div>

                <!-- Account Mode Selection -->
                <div id="accountModeSelection" style="display: none;">
                    <h3 class="mb-4">Select Account Mode</h3>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="accountMode" id="realAccount" value="real" required>
                        <label class="form-check-label" for="realAccount">Real Account</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="accountMode" id="demoAccount" value="demo" required>
                        <label class="form-check-label" for="demoAccount">Demo Account</label>
                    </div>
                    <div id="accountModeMessage" class="mt-3 alert" style="display: none;"></div>
                    <div class="mt-4">
                        <button class="btn btn-secondary" id="backToAccountType">Back</button>
                        <button class="btn btn-primary" id="createAccount">Create Account</button>
                    </div>
                </div>

                <!-- New Account Form -->
                <div id="newAccountForm" style="display: none;">
                    <h3>Open New Account</h3>
                    <form id="accountDetailsForm">
                        <div class="form-group">
                            <label for="startingBalance">Starting Balance</label>
                            <input type="number" class="form-control" id="startingBalance" required min="10">
                        </div>
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <select class="form-select" id="currency" name = "currency" required>
                                 <option value="">Select currency</option>
                            <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="accountNickname">Account Nickname</label>
                            <input type="text" class="form-control" id="accountNickname" required>
                        </div>
                        <div class="form-group">
                            <label for="accountPassword">Account Password</label>
                            <input type="password" class="form-control" id="accountPassword" required>
                            <ul class="list-unstyled password-requirements mt-2">
                                <li><span class="badge bg-danger" id="lengthReq">X</span> 8-15 characters</li>
                                <li><span class="badge bg-danger" id="uppercaseReq">X</span> At least 1 uppercase letter</li>
                                <li><span class="badge bg-danger" id="lowercaseReq">X</span> At least 1 lowercase letter</li>
                                <li><span class="badge bg-danger" id="numberReq">X</span> At least 1 number</li>
                                <li><span class="badge bg-danger" id="specialReq">X</span> At least 1 special character</li>
                            </ul>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3" id="submitAccountDetails">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </main>



    



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
       $(document).ready(function() {
        fetchAccounts();
        populateCurrencies();
    let isSubmitting =false;    
    let accounts = [];
    let currentCardIndex = 0;
    const totalCards = $('.account-type-card').length;
    let selectedAccountType = '';
    let selectedAccountMode = '';

    // Fetch accounts data
    function fetchAccounts() {
    $.ajax({
        url: 'get_accounts.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Fetched accounts:', response);
            if (response.success) {
                updateAccountsDisplay(response.accounts);
            } else {
                console.error('Failed to fetch accounts:', response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching accounts:', textStatus, errorThrown);
        }
    });
}


    // Update accounts display
    function updateAccountsDisplay() {
        let accountListHtml = '';
        let accountCardsHtml = '';
        let totalBalance = 0;

        accounts.forEach(function(account, index) {
            accountListHtml += `<li><a class="dropdown-item" href="#" data-account-id="${account.id}">${account.account_name}</a></li>`;
            
            accountCardsHtml += `
                <div class="col-md-4 mb-4">
                    <div class="card account-card ${index === 0 ? 'active-account' : ''}">
                        <div class="card-body">
                            <h5 class="card-title">${account.account_name}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Account #: ${account.account_number}</h6>
                            <p class="card-text">
                                <i class="fas fa-balance-scale me-2"></i>
                                Balance: $${account.balance.toFixed(2)}
                            </p>
                            <p class="card-text">
                                <i class="fas fa-tag me-2"></i>
                                Type: ${account.account_type}
                            </p>
                            <button class="btn btn-primary btn-sm btn-view-details" data-account-id="${account.id}">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </button>
                        </div>
                    </div>
                </div>
            `;

            totalBalance += parseFloat(account.balance);
        });

        $('#accountList').html(accountListHtml);
        $('#accountCards').html(accountCardsHtml);
        $('#totalBalance').text('$' + totalBalance.toFixed(2));
        $('#activeAccountName').text(accounts.length > 0 ? accounts[0].account_name : 'No Account');
    }

    // Fetch currencies
    function populateCurrencies() {
    const currencies = [
        {code: "AFN", name: "Afghan Afghani", symbol: "؋"},
        {code: "ALL", name: "Albanian Lek", symbol: "Lek"},
        {code: "DZD", name: "Algerian Dinar", symbol: "دج"},
        {code: "AOA", name: "Angolan Kwanza", symbol: "Kz"},
        {code: "ARS", name: "Argentine Peso", symbol: "$"},
        {code: "AMD", name: "Armenian Dram", symbol: "֏"},
        {code: "AWG", name: "Aruban Florin", symbol: "ƒ"},
        {code: "AUD", name: "Australian Dollar", symbol: "$"},
        {code: "AZN", name: "Azerbaijani Manat", symbol: "m"},
        {code: "BSD", name: "Bahamian Dollar", symbol: "B$"},
        {code: "BHD", name: "Bahraini Dinar", symbol: ".د.ب"},
        {code: "BDT", name: "Bangladeshi Taka", symbol: "৳"},
        {code: "BBD", name: "Barbadian Dollar", symbol: "Bds$"},
        {code: "BYR", name: "Belarusian Ruble", symbol: "Br"},
        {code: "BEF", name: "Belgian Franc", symbol: "fr"},
        {code: "BZD", name: "Belize Dollar", symbol: "$"},
        {code: "BMD", name: "Bermudan Dollar", symbol: "$"},
        {code: "BTN", name: "Bhutanese Ngultrum", symbol: "Nu."},
        {code: "BTC", name: "Bitcoin", symbol: "฿"},
        {code: "BOB", name: "Bolivian Boliviano", symbol: "Bs."},
        {code: "BAM", name: "Bosnia-Herzegovina Convertible Mark", symbol: "KM"},
        {code: "BWP", name: "Botswanan Pula", symbol: "P"},
        {code: "BRL", name: "Brazilian Real", symbol: "R$"},
        {code: "GBP", name: "British Pound Sterling", symbol: "£"},
        {code: "BND", name: "Brunei Dollar", symbol: "B$"},
        {code: "BGN", name: "Bulgarian Lev", symbol: "Лв."},
        {code: "BIF", name: "Burundian Franc", symbol: "FBu"},
        {code: "KHR", name: "Cambodian Riel", symbol: "KHR"},
        {code: "CAD", name: "Canadian Dollar", symbol: "$"},
        {code: "CVE", name: "Cape Verdean Escudo", symbol: "$"},
        {code: "KYD", name: "Cayman Islands Dollar", symbol: "$"},
        {code: "XOF", name: "CFA Franc BCEAO", symbol: "CFA"},
        {code: "XAF", name: "CFA Franc BEAC", symbol: "FCFA"},
        {code: "XPF", name: "CFP Franc", symbol: "₣"},
        {code: "CLP", name: "Chilean Peso", symbol: "$"},
        {code: "CNY", name: "Chinese Yuan", symbol: "¥"},
        {code: "COP", name: "Colombian Peso", symbol: "$"},
        {code: "KMF", name: "Comorian Franc", symbol: "CF"},
        {code: "CDF", name: "Congolese Franc", symbol: "FC"},
        {code: "CRC", name: "Costa Rican Colón", symbol: "₡"},
        {code: "HRK", name: "Croatian Kuna", symbol: "kn"},
        {code: "CUC", name: "Cuban Convertible Peso", symbol: "$, CUC"},
        {code: "CZK", name: "Czech Republic Koruna", symbol: "Kč"},
        {code: "DKK", name: "Danish Krone", symbol: "Kr."},
        {code: "DJF", name: "Djiboutian Franc", symbol: "Fdj"},
        {code: "DOP", name: "Dominican Peso", symbol: "$"},
        {code: "XCD", name: "East Caribbean Dollar", symbol: "$"},
        {code: "EGP", name: "Egyptian Pound", symbol: "ج.م"},
        {code: "ERN", name: "Eritrean Nakfa", symbol: "Nfk"},
        {code: "EEK", name: "Estonian Kroon", symbol: "kr"},
        {code: "ETB", name: "Ethiopian Birr", symbol: "Nkf"},
        {code: "EUR", name: "Euro", symbol: "€"},
        {code: "FKP", name: "Falkland Islands Pound", symbol: "£"},
        {code: "FJD", name: "Fijian Dollar", symbol: "FJ$"},
        {code: "GMD", name: "Gambian Dalasi", symbol: "D"},
        {code: "GEL", name: "Georgian Lari", symbol: "ლ"},
        {code: "DEM", name: "German Mark", symbol: "DM"},
        {code: "GHS", name: "Ghanaian Cedi", symbol: "GH₵"},
        {code: "GIP", name: "Gibraltar Pound", symbol: "£"},
        {code: "GRD", name: "Greek Drachma", symbol: "₯, Δρχ, Δρ"},
        {code: "GTQ", name: "Guatemalan Quetzal", symbol: "Q"},
        {code: "GNF", name: "Guinean Franc", symbol: "FG"},
        {code: "GYD", name: "Guyanaese Dollar", symbol: "$"},
        {code: "HTG", name: "Haitian Gourde", symbol: "G"},
        {code: "HNL", name: "Honduran Lempira", symbol: "L"},
        {code: "HKD", name: "Hong Kong Dollar", symbol: "$"},
        {code: "HUF", name: "Hungarian Forint", symbol: "Ft"},
        {code: "ISK", name: "Icelandic Króna", symbol: "kr"},
        {code: "INR", name: "Indian Rupee", symbol: "₹"},
        {code: "IDR", name: "Indonesian Rupiah", symbol: "Rp"},
        {code: "IRR", name: "Iranian Rial", symbol: "﷼"},
        {code: "IQD", name: "Iraqi Dinar", symbol: "د.ع"},
        {code: "ILS", name: "Israeli New Sheqel", symbol: "₪"},
        {code: "ITL", name: "Italian Lira", symbol: "L,£"},
        {code: "JMD", name: "Jamaican Dollar", symbol: "J$"},
        {code: "JPY", name: "Japanese Yen", symbol: "¥"},
        {code: "JOD", name: "Jordanian Dinar", symbol: "ا.د"},
        {code: "KZT", name: "Kazakhstani Tenge", symbol: "лв"},
        {code: "KES", name: "Kenyan Shilling", symbol: "KSh"},
        {code: "KWD", name: "Kuwaiti Dinar", symbol: "ك.د"},
        {code: "KGS", name: "Kyrgystani Som", symbol: "лв"},
        {code: "LAK", name: "Laotian Kip", symbol: "₭"},
        {code: "LVL", name: "Latvian Lats", symbol: "Ls"},
        {code: "LBP", name: "Lebanese Pound", symbol: "£"},
        {code: "LSL", name: "Lesotho Loti", symbol: "L"},
        {code: "LRD", name: "Liberian Dollar", symbol: "$"},
        {code: "LYD", name: "Libyan Dinar", symbol: "د.ل"},
        {code: "LTL", name: "Lithuanian Litas", symbol: "Lt"},
        {code: "MOP", name: "Macanese Pataca", symbol: "$"},
        {code: "MKD", name: "Macedonian Denar", symbol: "ден"},
        {code: "MGA", name: "Malagasy Ariary", symbol: "Ar"},
        {code: "MWK", name: "Malawian Kwacha", symbol: "MK"},
        {code: "MYR", name: "Malaysian Ringgit", symbol: "RM"},
        {code: "MVR", name: "Maldivian Rufiyaa", symbol: "Rf"},
        {code: "MRO", name: "Mauritanian Ouguiya", symbol: "MRU"},
        {code: "MUR", name: "Mauritian Rupee", symbol: "₨"},
        {code: "MXN", name: "Mexican Peso", symbol: "$"},
        {code: "MDL", name: "Moldovan Leu", symbol: "L"},
        {code: "MNT", name: "Mongolian Tugrik", symbol: "₮"},
        {code: "MAD", name: "Moroccan Dirham", symbol: "MAD"},
        {code: "MZM", name: "Mozambican Metical", symbol: "MT"},
        {code: "MMK", name: "Myanmar Kyat", symbol: "K"},
        {code: "NAD", name: "Namibian Dollar", symbol: "$"},
        {code: "NPR", name: "Nepalese Rupee", symbol: "₨"},
        {code: "ANG", name: "Netherlands Antillean Guilder", symbol: "ƒ"},
        {code: "TWD", name: "New Taiwan Dollar", symbol: "$"},
        {code: "NZD", name: "New Zealand Dollar", symbol: "$"},
        {code: "NIO", name: "Nicaraguan Córdoba", symbol: "C$"},
        {code: "NGN", name: "Nigerian Naira", symbol: "₦"},
        {code: "KPW", name: "North Korean Won", symbol: "₩"},
        {code: "NOK", name: "Norwegian Krone", symbol: "kr"},
        {code: "OMR", name: "Omani Rial", symbol: ".ع.ر"},
        {code: "PKR", name: "Pakistani Rupee", symbol: "₨"},
        {code: "PAB", name: "Panamanian Balboa", symbol: "B/."},
        {code: "PGK", name: "Papua New Guinean Kina", symbol: "K"},
        {code: "PYG", name: "Paraguayan Guarani", symbol: "₲"},
        {code: "PEN", name: "Peruvian Nuevo Sol", symbol: "S/."},
        {code: "PHP", name: "Philippine Peso", symbol: "₱"},
        {code: "PLN", name: "Polish Zloty", symbol: "zł"},
        {code: "QAR", name: "Qatari Rial", symbol: "ق.ر"},
        {code: "RON", name: "Romanian Leu", symbol: "lei"},
        {code: "RUB", name: "Russian Ruble", symbol: "₽"},
        {code: "RWF", name: "Rwandan Franc", symbol: "FRw"},
        {code: "SVC", name: "Salvadoran Colón", symbol: "₡"},
        {code: "WST", name: "Samoan Tala", symbol: "SAT"},
        {code: "SAR", name: "Saudi Riyal", symbol: "﷼"},
        {code: "RSD", name: "Serbian Dinar", symbol: "din"},
        {code: "SCR", name: "Seychellois Rupee", symbol: "SRe"},
        {code: "SLL", name: "Sierra Leonean Leone", symbol: "Le"},
        {code: "SGD", name: "Singapore Dollar", symbol: "$"},
        {code: "SKK", name: "Slovak Koruna", symbol: "Sk"},
        {code: "SBD", name: "Solomon Islands Dollar", symbol: "Si$"},
        {code: "SOS", name: "Somali Shilling", symbol: "Sh.so."},
        {code: "ZAR", name: "South African Rand", symbol: "R"},
        {code: "KRW", name: "South Korean Won", symbol: "₩"},
        {code: "XDR", name: "Special Drawing Rights", symbol: "SDR"},
        {code: "LKR", name: "Sri Lankan Rupee", symbol: "Rs"},
        {code: "SHP", name: "St. Helena Pound", symbol: "£"},
        {code: "SDG", name: "Sudanese Pound", symbol: ".س.ج"},
        {code: "SRD", name: "Surinamese Dollar", symbol: "$"},
        {code: "SZL", name: "Swazi Lilangeni", symbol: "E"},
        {code: "SEK", name: "Swedish Krona", symbol: "kr"},
        {code: "CHF", name: "Swiss Franc", symbol: "CHf"},
        {code: "SYP", name: "Syrian Pound", symbol: "LS"},
        {code: "STD", name: "São Tomé and Príncipe Dobra", symbol: "Db"},
        {code: "TJS", name: "Tajikistani Somoni", symbol: "SM"},
        {code: "TZS", name: "Tanzanian Shilling", symbol: "TSh"},
        {code: "THB", name: "Thai Baht", symbol: "฿"},
        {code: "TOP", name: "Tongan pa'anga", symbol: "$"},
        {code: "TTD", name: "Trinidad & Tobago Dollar", symbol: "$"},
        {code: "TND", name: "Tunisian Dinar", symbol: "ت.د"},
        {code: "TRY", name: "Turkish Lira", symbol: "₺"},
        {code: "TMT", name: "Turkmenistani Manat", symbol: "T"},
        {code: "UGX", name: "Ugandan Shilling", symbol: "USh"},
        {code: "UAH", name: "Ukrainian Hryvnia", symbol: "₴"},
        {code: "AED", name: "United Arab Emirates Dirham", symbol: "إ.د"},
        {code: "UYU", name: "Uruguayan Peso", symbol: "$"},
        {code: "USD", name: "US Dollar", symbol: "$"},
        {code: "UZS", name: "Uzbekistan Som", symbol: "лв"},
        {code: "VUV", name: "Vanuatu Vatu", symbol: "VT"},
        {code: "VEF", name: "Venezuelan Bolívar", symbol: "Bs"},
        {code: "VND", name: "Vietnamese Dong", symbol: "₫"},
        {code: "YER", name: "Yemeni Rial", symbol: "﷼"},
        {code: "ZMK", name: "Zambian Kwacha", symbol: "ZK"}
    ];

    let currencyOptions = '<option value="">Select currency</option>';
    currencies.forEach(currency => {
        currencyOptions += `<option value="${currency.code}">${currency.code} - ${currency.name} - ${currency.symbol}</option>`;
    });
    $('#currency').html(currencyOptions);
}

    // Open new account buttons
    $('#openNewAccountBtnQuick').on('click', function(e) {
        e.preventDefault();
        console.log('Open new account button clicked');
        $('#accountTypeSelection').show();
        $('#newAccountForm').hide();
        showAccountTypeCard(0);
    });

    // Account type carousel navigation
    function showAccountTypeCard(index) {
        $('.account-type-card').removeClass('active');
        $('.account-type-card').eq(index).addClass('active');
    }

    $('#prevCard').on('click', function() {
        currentCardIndex = (currentCardIndex - 1 + totalCards) % totalCards;
        showAccountTypeCard(currentCardIndex);
    });

    $('#nextCard').on('click', function() {
        currentCardIndex = (currentCardIndex + 1) % totalCards;
        showAccountTypeCard(currentCardIndex);
    });

    // Select account type
    $('#selectAccountType').on('click', function() {
        selectedAccountType = $('.account-type-card.active').attr('id').replace('Card', '');
        $('#accountTypeSelection').hide();
        $('#accountModeSelection').show();
    });

    // Back to account type selection
    $('#backToAccountType').on('click', function() {
        $('#accountModeSelection').hide();
        $('#accountTypeSelection').show();
    });
     // Account mode selection
     $('input[name="accountMode"]').on('change', function() {
        selectedAccountMode = $('input[name="accountMode"]:checked').val();
        const messageElement = $('#accountModeMessage');
        
        if (selectedAccountMode === 'real') {
            messageElement.text('Trade with real money and withdraw any profit you may make').removeClass('alert-info').addClass('alert-success').show();
        } else if (selectedAccountMode === 'demo') {
            messageElement.text('Risk-free account. Trade with virtual money').removeClass('alert-success').addClass('alert-info').show();
        }
    });

    // Create account button
    $('#createAccount').on('click', function() {
        if (!selectedAccountMode) {
            alert('Please select an account mode');
            return;
        }
        $('#accountModeSelection').hide();
        $('#newAccountForm').show();
    });

      // Set default account nickname
    $('#createAccount').on('click', function() {
        if (!selectedAccountMode) {
            alert('Please select an account mode');
            return;
        }
        $('#accountModeSelection').hide();
        $('#newAccountForm').show();
        
        // Set default account nickname
        $('#accountNickname').val(selectedAccountType.charAt(0).toUpperCase() + selectedAccountType.slice(1));
    });

    // Submit new account form
    $('#accountDetailsForm').on('submit', function(e) {
        e.preventDefault();
        const startingBalance = $('#startingBalance').val();
        const currency = $('#currency').val();
        const accountNickname = $('#accountNickname').val();
        const accountPassword = $('#accountPassword').val();

         // Password validation
    $('#accountPassword').on('input', function() {
        const password = $(this).val();
        const lengthValid = password.length >= 8 && password.length <= 15;
        const uppercaseValid = /[A-Z]/.test(password);
        const lowercaseValid = /[a-z]/.test(password);
        const numberValid = /[0-9]/.test(password);
        const specialValid = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        updatePasswordRequirement('lengthReq', lengthValid);
        updatePasswordRequirement('uppercaseReq', uppercaseValid);
        updatePasswordRequirement('lowercaseReq', lowercaseValid);
        updatePasswordRequirement('numberReq', numberValid);
        updatePasswordRequirement('specialReq', specialValid);
    });

    function updatePasswordRequirement(id, isValid) {
        const element = $(`#${id}`);
        if (isValid) {
            element.removeClass('bg-danger').addClass('bg-success').html('✓');
        } else {
            element.removeClass('bg-success').addClass('bg-danger').html('✕');
        }
    }

    // Submit new account form
    $('#accountDetailsForm').on('submit', function(e) {
        e.preventDefault();
        const startingBalance = $('#startingBalance').val();
        const currency = $('#currency').val();
        const accountNickname = $('#accountNickname').val();
        const accountPassword = $('#accountPassword').val();

        // Validate password
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,15}$/;
        if (!passwordRegex.test(accountPassword)) {
            alert('Password does not meet all requirements.');
            return;
        }

        // Validate starting balance based on account type
        const minBalance = {
            'standard': 10,
            'pro': 500,
            'basic': 10
        };
        if (parseFloat(startingBalance) < minBalance[selectedAccountType]) {
            alert(`Minimum starting balance for ${selectedAccountType} account is $${minBalance[selectedAccountType]}`);
            return;
        }

        // Send data to server to create account
        $.ajax({
            url: 'create_account.php',
            method: 'POST',
            data: {
                accountType: selectedAccountType,
                accountMode: selectedAccountMode,
                startingBalance: startingBalance,
                currency: currency,
                accountNickname: accountNickname,
                accountPassword: accountPassword
            },
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response);
    if (response.success) {
        alert('Account created successfully!');
        $('#newAccountForm').hide();
        $('#accountDetailsForm')[0].reset();
        fetchAccounts(); // Refresh the accounts list
    } else {
        alert('Error: ' + response.message);
    }
},
error: function(jqXHR, textStatus, errorThrown) {
    console.log('AJAX error:', textStatus, errorThrown);
    console.log('Response:', jqXHR.responseText);
    alert('An error occurred while creating the account. Check the console for details.');
}
});

    // Switch active account
    function updateAccountsDropdown(accounts) {
    let dropdownHtml = '';
    accounts.forEach(account => {
        dropdownHtml += `<li><a class="dropdown-item" href="#" data-account-id="${account.id}">${account.account_name}</a></li>`;
    });
    $('#accountDropdown').next('.dropdown-menu').html(dropdownHtml);
}

$(document).on('click', '.dropdown-item[data-account-id]', function(e) {
    e.preventDefault();
    const accountId = $(this).data('account-id');
    
    $.ajax({
        url: 'update_active_account.php',
        method: 'POST',
        data: { account_id: accountId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update the active account in the UI
                $('#activeAccountName').text(response.account.account_name);
                fetchAccounts(); // Refresh the accounts list
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error updating active account:', textStatus, errorThrown);
            alert('An error occurred while switching accounts.');
        }
    });
});

    // View account details
    $(document).on('click', '.btn-view-details', function(e) {
    e.preventDefault();
    const accountId = $(this).data('account-id');
    
    
    $.ajax({
        url: 'get_account_details.php',
        method: 'GET',
        data: { account_id: accountId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayAccountDetails(response.account);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error fetching account details:', textStatus, errorThrown);
            alert('An error occurred while fetching account details.');
        }
    });
});

    // Create a modal to display account details
    function displayAccountDetails(account) {
    const modalHtml = `
        <div class="modal fade" id="accountDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Account Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Account Name:</strong> ${account.account_name}</p>
                        <p><strong>Account Number:</strong> ${account.account_number}</p>
                        <p><strong>Account Type:</strong> ${account.account_type}</p>
                        <p><strong>Balance:</strong> ${account.currency} ${account.balance.toFixed(2)}</p>
                        <p><strong>Currency:</strong> ${account.currency}</p>
                    </div>
                </div>
            </div>
        </div>
    `;

        // Append modal to body, show it, and remove it after hiding
        $('body').append(modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('accountDetailsModal'));
    modal.show();
    $('#accountDetailsModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}

    // Transfer between accounts
    $('#transferBetweenAccounts').on('click', function(e) {
        e.preventDefault();
        // Implement transfer functionality here
        alert('Transfer functionality to be implemented.');
    })   

});
    });
});
</script>
</body>
</html>
