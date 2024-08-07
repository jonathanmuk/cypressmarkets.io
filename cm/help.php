<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Cypress Markets</title>
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
        .help-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
        }
        .faq-item {
            margin-bottom: 20px;
        }
        .faq-question {
            cursor: pointer;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .faq-question:hover {
            background-color: #e9ecef;
        }
        .faq-answer {
            display: none;
            padding: 10px;
            background-color: #ffffff;
            border-radius: 0 0 5px 5px;
        }
        .search-container {
            margin-bottom: 30px;
        }
        .category-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .category-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="help-container">
            <h2 class="text-center mb-4">Help Center</h2>
            
            <div class="search-container">
                <form>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search for help topics..." aria-label="Search for help topics">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="category-card">
                        <h4><i class="fas fa-user-circle"></i> Account</h4>
                        <p>Manage your account settings and profile</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card">
                        <h4><i class="fas fa-exchange-alt"></i> Trading</h4>
                        <p>Learn about trading and market analysis</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="category-card">
                        <h4><i class="fas fa-wallet"></i> Deposits & Withdrawals</h4>
                        <p>Information about managing your funds</p>
                    </div>
                </div>
            </div>

            <h3>Frequently Asked Questions</h3>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <h5><i class="fas fa-plus-circle"></i> How do I create an account?</h5>
                    </div>
                    <div class="faq-answer">
                        <p>To create an account, click on the "Sign Up" button on the homepage. Fill in the required information, including your email address and a strong password. Follow the verification process, and you'll be ready to start trading.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h5><i class="fas fa-plus-circle"></i> What payment methods are accepted for deposits?</h5>
                    </div>
                    <div class="faq-answer">
                        <p>We accept various payment methods, including credit/debit cards, bank transfers, and popular e-wallets. For a full list of available options, please visit our Deposits page.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h5><i class="fas fa-plus-circle"></i> How long does it take to process a withdrawal?</h5>
                    </div>
                    <div class="faq-answer">
                        <p>Withdrawal processing times vary depending on the method chosen. E-wallet withdrawals are typically processed within 24 hours, while bank transfers may take 3-5 business days. Please note that additional verification may be required for security purposes.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">
                        <h5><i class="fas fa-plus-circle"></i> What trading platforms do you offer?</h5>
                    </div>
                    <div class="faq-answer">
                        <p>We offer the popular MetaTrader 4 (MT4) and MetaTrader 5 (MT5) platforms, as well as our proprietary web-based trading platform. Each platform is designed to cater to different trading styles and preferences.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <h4>Still need help?</h4>
                <p>Our support team is available 24/7 to assist you.</p>
                <a href="support-hub.php" class="btn btn-primary">Contact Support</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.faq-question').forEach(item => {
            item.addEventListener('click', event => {
                const answer = item.nextElementSibling;
                const icon = item.querySelector('i');
                if (answer.style.display === 'block') {
                    answer.style.display = 'none';
                    icon.classList.remove('fa-minus-circle');
                    icon.classList.add('fa-plus-circle');
                } else {
                    answer.style.display = 'block';
                    icon.classList.remove('fa-plus-circle');
                    icon.classList.add('fa-minus-circle');
                }
            });
        });
    </script>
</body>
</html>