<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OGMBC Consultants</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="resources/css/style.css">
</head>
<body>

<header>
    <div class="container nav">
    <a class="brand d-flex align-items-center" href="index.php">
        <img src="resources/img/logo.png" alt="OGM Consultants Logo" class="logo-img me-2" />
        <span>OGMBC Consultants</span>
    </a>

<nav class="menu navbar navbar-expand-lg" id="menu" style="height: 40px;">
    <div class="container-fluid">
        <ul class="navbar-nav mx-auto">
        <!-- About -->
        <li class="nav-item">
            <a class="nav-link" href="about.php">About</a>
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
                <li><a class="dropdown-item" href="residency-service.php">Residency Services</a></li>
                <li><a class="dropdown-item" href="office-space.php">Office Space Provision</a></li>
                </ul>
            </li>
            </ul>
        </li>

        <!-- Blog -->
        <li class="nav-item">
            <a class="nav-link" href="blog.php">Blog</a>
        </li>

        <!-- Contact -->
        <li class="nav-item">
            <a class="nav-link" href="contact.php">Contact</a>
        </li>

        <!-- CTA -->
        <li class="nav-item">
            <a href="sign_up.php" target="_blank" class="btn btn-ghost">Sign Up</a>
            <a href="login.php" class="btn">login</a>
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