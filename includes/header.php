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
  <link rel="stylesheet" href="resources/css/style.css" />
  <!-- Bootstrap CSS (load first so our style.css can override) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Your custom CSS -->
  <link rel="stylesheet" href="resources/css/style.css" />
  <script src="resources/js/main.js" defer></script>

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

  document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll(".num");
    let started = false;

    function animateCount(counter) {
      const target = counter.getAttribute("data-target");
      const isPercentage = target.includes("%");
      const isPlus = target.includes("+");
      const isGrade = target.toUpperCase().includes("A+");

      if (isGrade) {
        // Animate 0 → 100, then replace with "A+"
        let count = 0;
        const duration = 2000;
        const stepTime = 20;
        const increment = Math.ceil(100 / (duration / stepTime));

        const timer = setInterval(() => {
          count += increment;
          if (count >= 100) {
            clearInterval(timer);
            counter.textContent = "A+";
          } else {
            counter.textContent = count;
          }
        }, stepTime);
      } else {
        // Handle numbers with + or %
        const numericTarget = parseInt(target.replace(/\D/g, ""), 10);
        let count = 0;
        const duration = 2000;
        const stepTime = Math.max(Math.floor(duration / numericTarget), 20);

        const timer = setInterval(() => {
          count++;
          if (count >= numericTarget) {
            clearInterval(timer);
            counter.textContent = isPercentage
              ? numericTarget + "%"
              : numericTarget + (isPlus ? "+" : "");
          } else {
            counter.textContent = isPercentage
              ? count + "%"
              : count + (isPlus ? "+" : "");
          }
        }, stepTime);
      }
    }

    function checkScroll() {
      const section = document.querySelector("#stats");
      if (!section) return;
      const rect = section.getBoundingClientRect();

      if (!started && rect.top < window.innerHeight && rect.bottom > 0) {
        counters.forEach(animateCount);
        started = true;
      }
    }

    window.addEventListener("scroll", checkScroll);
    // Initial check in case the section is already in view
    checkScroll();
  });

  </script>

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
      <span>OGMBC Consultants</span>
    </a>

    <nav class="menu navbar navbar-expand-lg" id="menu" style="height: 40px;">
      <div class="container-fluid">
        <ul class="navbar-nav mx-auto">
          <!-- Home -->
          <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
          </li>

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

          <!-- Profile -->
          <li class="nav-item">
              <a class="nav-link" href="ogmbc-profile.php">Profile</a>
          </li>

          <!-- Ratios -->
          <li class="nav-item">
              <a class="nav-link" href="ratios.php">Ratios</a>
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
              <a href="login.php" target="_blank" class="btn btn-ghost">Login</a>
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