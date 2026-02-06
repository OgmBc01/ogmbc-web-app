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
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>OGMBC Consultants — Accounting & Auditing</title>
  <meta name="description" content="OGM Consultants is an accounting and auditing firm offering statutory audit, financial reporting, and advisory services." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap CSS (load first so our style.css can override) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS abd Scripts-->
  <link rel="stylesheet" href="resources/css/style.css?v=<?php echo time(); ?>" />
  <script src="https://widget.senja.io/widget/a95d7d19-660c-4783-9a80-61b67e8d3b43/platform.js" type="text/javascript" async></script>
  <script type="text/javascript" src="https://widget.senja.io/js/iframeResizer.min.js"></script>
</head>

<body>
  <section class="hero position-relative">
  <!-- Background Media -->
    <?php
      // Set this to true to use video, false to use image
      $useVideo = false;
      $heroImage = "resources/img/bookkeeping.jpg"; // Change to your image path
    ?>
    <?php if ($useVideo): ?>
      <video autoplay muted loop playsinline class="hero-bg-video">
        <source src="resources/video/hero.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <?php else: ?>
        <img src="<?php echo $heroImage; ?>" alt="Hero Background" class="hero-bg-image" style="width:100%;height:100%;object-fit:cover;position:absolute;top:0;left:0;z-index:0;">
      <?php endif; ?>

    <!-- Overlay for better text readability -->
    <div class="hero-overlay"></div>
    <!-- Header / Navigation -->
    <header>
      <div class="container nav">
        <a class="brand d-flex align-items-center" href="index.php">
          <img src="resources/img/logo.png" alt="OGM Consultants Logo" class="logo-img me-2" />
          <span>OGM Business Consultants</span>
        </a>

        <nav class="menu navbar navbar-expand-lg" id="menu" style="height: 40px;">
          <div class="container-fluid">
            <ul class="navbar-nav mx-auto">
              <!-- Home -->
              <li class="nav-item">
                  <a class="nav-link" href="index.php">Home</a>
              </li>
              
              <!-- About -->
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  About
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                      <li class="dropdown-submenu">
                          <a class="dropdown-item" href="about.php">About Us</a>
                      </li>

                      <li class="dropdown-submenu">
                          <a class="dropdown-item" href="our-team.php">Our Team</a>
                      </li>

                      <!-- Profile -->
                      <li class="dropdown-submenu">
                          <a class="dropdown-item" href="ogmbc-profile.php">Company Profile</a>
                      </li>
                  </ul>
              </li>

              <!-- Services Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Services
                </a>
                <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                  
                  <!-- Business Setup (nested) -->
                  <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#">Business Setup</a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="uae-bussiness-formation.php">UAE Company Formation</a></li>
                      <li><a class="dropdown-item" href="usa-company-formation.php">USA Company Formation</a></li>
                      <li><a class="dropdown-item" href="uk-company-formation.php">UK Company Formation</a></li>
                      <li><a class="dropdown-item" href="cayman-company-formation.php">Cayman Company Formation</a></li>
                      <li><a class="dropdown-item" href="estonia-company-formation.php">Estonia Company Formation</a></li>
                      <li><a class="dropdown-item" href="e-commerce.php">E-commerce Business Formation</a></li>
                    </ul>
                  </li>

                  <!-- Accounting & Taxation (nested) -->
                  <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#">Accounting & Taxation</a>
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

                  <!-- Support (nested) -->
                  <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#">Statutory Compliance</a>
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

                  <!-- Statutory Compliance (nested) -->
                  <li class="dropdown-submenu">
                    <a class="dropdown-item dropdown-toggle" href="#">Support</a>
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

              <!-- Plan your Business -->
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Business Planning
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                      <li class="dropdown-submenu">
                          <a class="dropdown-item" href="check-business-health.php">Check your Company Health</a>
                      </li>

                      <li class="dropdown-submenu">
                          <a class="dropdown-item" href="uae-business-setup-cost-calculator.php">UAE Business Setup Cost Calculator</a>
                      </li>
                  </ul>
              </li>

              <!-- Contact -->
              <li class="nav-item">
                  <a class="nav-link" href="contact.php">Contact</a>
              </li>

              <?php
              // Check for admin roles
              $show_admin_link = false;
              if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
                  $admin_roles = ['admin', 'super_admin', 'moderator'];
                  $show_admin_link = in_array($_SESSION['user_role'], $admin_roles);
              }

              if ($show_admin_link): ?>
                  <!-- Admin Dashboard -->
                  <li class="nav-item">
                      <a class="nav-link" href="admin/dashboard.php" target="_blank">
                        Admin
                      </a>
                  </li>
              <?php endif; ?>

              <!-- Login / Sign up -->
              <li class="nav-item">
                <?php
                
                if (isset($_SESSION['user_id'])) {
                    // User is logged in - show Sign Out button
                    echo '<a href="logout.php" class="btn btn-ghost">Sign Out</a>';
                } else {
                    // User is not logged in - show Login button
                    echo '<a href="login.php" target="_blank" class="btn btn-ghost">Login</a>';
                }
                ?>
              </li>
            </ul>
          </div>
        </nav>

        <button id="menu-toggle" class="menu-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
        </button>
      </div>
    </header>