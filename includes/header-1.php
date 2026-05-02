<?php
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();

// Detect error type from URL parameter
$error_type = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!-- Error Alert Container -->
<?php if ($error_type): ?>
    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); max-width: 600px; z-index: 9999; margin: 0;">
        <?php if ($error_type === 'session'): ?>
            <strong>Session Expired!</strong> Your session has expired. Please <a href="login.php" class="alert-link">login again</a> to continue.
        <?php elseif ($error_type === 'permission'): ?>
            <strong>Access Denied!</strong> You do not have the required permissions to access the admin area. Please <a href="contact.php" class="alert-link">contact an administrator</a> for assistance.
        <?php endif; ?>
    </div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const errorAlert = document.getElementById('errorAlert');
      if (errorAlert) {
          // Auto-dismiss after 4 seconds
          setTimeout(function() {
              const bsAlert = new bootstrap.Alert(errorAlert);
              bsAlert.close();
          }, 7000);
      }
  });
</script>
<?php endif; ?>

<?php
include 'functions.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OGMBC Consultants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="resources/css/style.css?v=<?php echo time(); ?>">
  <!-- jsPDF (for PDF downloads) and html2canvas (optional) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

</head>
<body>

<header>
  <div class="container nav">
    <a class="brand d-flex align-items-center me-lg-5 me-3" href="index.php" style="min-width:220px;">
      <img src="resources/img/logo.png" alt="OGM Consultants Logo" class="logo-img me-2" />
      <span style="font-size:0.85rem; line-height:1.05; white-space:normal; text-align:left; display:block;">
        OGM Business<br>Consultants
      </span>
    </a>

    <nav class="menu navbar navbar-expand-lg flex-grow-1" id="menu" style="height: 40px;">
      <ul class="navbar-nav flex-grow-1 justify-content-lg-end ms-lg-auto">
        <!-- Home -->
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        
        <!-- About -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            About
          </a>
          <ul class="dropdown-menu" aria-labelledby="aboutDropdown">
            <li><a class="dropdown-item" href="about.php">About Us</a></li>
            <li><a class="dropdown-item" href="our-team.php">Our Team</a></li>
            <li><a class="dropdown-item" href="ogmbc-profile.php">Company Profile</a></li>
          </ul>
        </li>

        <!-- Services Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Services
          </a>
          <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
            
            <!-- Business Setup (has submenu) -->
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="javascript:void(0)">Business Setup</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="uae-bussiness-formation.php">UAE Company Formation</a></li>
                <li><a class="dropdown-item" href="usa-company-formation.php">USA Company Formation</a></li>
                <li><a class="dropdown-item" href="uk-company-formation.php">UK Company Formation</a></li>
                <li><a class="dropdown-item" href="cayman-company-formation.php">Cayman Company Formation</a></li>
                <li><a class="dropdown-item" href="estonia-company-formation.php">Estonia Company Formation</a></li>
                <li><a class="dropdown-item" href="e-commerce.php">E-commerce Business Formation</a></li>
              </ul>
            </li>

            <!-- Accounting & Taxation (has submenu) -->
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="javascript:void(0)">Accounting & Taxation</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="bookkeeping.php">Bookkeeping</a></li>
                <li><a class="dropdown-item" href="cfo-services.php">CFO Services</a></li>
                <li><a class="dropdown-item" href="management-accounting-&-kpi.php">Management Accounting & KPIs</a></li>
                <li><a class="dropdown-item" href="tax-consultancy.php">Tax Consultancy</a></li>
                <li><a class="dropdown-item" href="business-planning.php">Business Planning</a></li>
                <li><a class="dropdown-item" href="business-valuation.php">Business Valuation</a></li>
                <li><a class="dropdown-item" href="transfer-pricing.php">Transfer Pricing</a></li>
                <li><a class="dropdown-item" href="supply-chain.php">Supply Chain</a></li>
              </ul>
            </li>

            <!-- Statutory Compliance (has submenu) -->
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="javascript:void(0)">Statutory Compliance</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="corporate-governance.php">Corporate Governance</a></li>
                <li><a class="dropdown-item" href="internal-controlling.php">Internal Controlling</a></li>
                <li><a class="dropdown-item" href="audit-&-audit-support.php">Audit & Audit Support</a></li>
                <li><a class="dropdown-item" href="financial-statement-reporting.php">Financial Statement Reporting</a></li>
                <li><a class="dropdown-item" href="ifrs-advisory.php">IFRS Advisory</a></li>
                <li><a class="dropdown-item" href="due-diligence.php">Due Diligence</a></li>
                <li><a class="dropdown-item" href="aml-support.php">AML Support</a></li>
              </ul>
            </li>

            <!-- Support (has submenu) -->
            <li class="dropdown-submenu">
              <a class="dropdown-item dropdown-toggle" href="javascript:void(0)">Support</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="bank-account-opening.php">Bank Account Opening</a></li>
                <li><a class="dropdown-item" href="annual-renewal-services.php">Annual Renewal Services</a></li>
                <li><a class="dropdown-item" href="office-space.php">Office Space Provision</a></li>
              </ul>
            </li>
          </ul>
        </li>
        
        <!-- Wall of Love -->
        <li class="nav-item position-relative">
          <a class="nav-link Wall-of-love-link position-relative" href="our-wall-of-love.php">
            <span class="Wall-of-love-text">Our Wall Of Love</span>
            <span class="love-stars">
              <i class="bi bi-star-fill love-star star-1"></i>
              <i class="bi bi-star-fill love-star star-2"></i>
              <i class="bi bi-heart-fill love-heart"></i>
            </span>
            <span class="link-border"></span>
          </a>
        </li>

        <!-- Business Planning -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="planningDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Business Planning
          </a>
          <ul class="dropdown-menu" aria-labelledby="planningDropdown">
            <li><a class="dropdown-item" href="check-business-health.php">Check your Company Health</a></li>
            <li><a class="dropdown-item" href="uae-business-setup-cost-calculator.php">UAE Business Setup Cost Calculator</a></li>
          </ul>
        </li>

        <!-- Contact -->
        <li class="nav-item me-3">
          <a class="nav-link" href="contact.php">Contact</a>
        </li>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Login Dropdown -->
        <li class="nav-item dropdown ms-lg-auto">
          <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-box-arrow-in-right me-1"></i>Login
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginDropdown">
            <li>
              <a class="dropdown-item" href="client_login.php">
                <i class="bi bi-person-badge me-2" style="color: #f1bf70;"></i>
                Login as Client
                <span class="badge bg-info ms-2">Client Portal</span>
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item" href="employee_login.php">
                <i class="bi bi-shield-lock me-2" style="color: #667eea;"></i>
                Login as Employee
                <span class="badge bg-primary ms-2">Staff Portal</span>
              </a>
            </li>
          </ul>
        </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- If logged in, show logout instead -->
        <li class="nav-item ms-2">
          <a href="logout.php" class="btn btn-ghost">
            <i class="bi bi-box-arrow-right me-1"></i>Sign Out
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>

    <button id="menu-toggle" class="menu-toggle" aria-label="Toggle menu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</header>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Target only submenu toggles
    document.querySelectorAll(".dropdown-submenu > .dropdown-toggle").forEach(function (el) {
      el.addEventListener("click", function (e) {
        e.preventDefault();  // stop link navigation
        e.stopPropagation(); // stop Bootstrap from closing parent

        // close any other open submenus inside the same parent
        let parentMenu = this.closest(".dropdown-menu");
        parentMenu.querySelectorAll(".dropdown-menu.show").forEach(function (submenu) {
          if (submenu !== el.nextElementSibling) {
            submenu.classList.remove("show");
          }
        });

        // toggle the submenu
        let submenu = this.nextElementSibling;
        if (submenu) {
          submenu.classList.toggle("show");
        }
      });
    });

    // Close submenus when main dropdown closes
    document.querySelectorAll(".dropdown").forEach(function (dd) {
      dd.addEventListener("hide.bs.dropdown", function () {
        this.querySelectorAll(".dropdown-menu.show").forEach(function (submenu) {
          submenu.classList.remove("show");
        });
      });
    });
  });
</script>