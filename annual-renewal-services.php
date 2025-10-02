<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Annual Renewal Services</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Support</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Annual Renewal Servicesg</li>
      </ol>
    </nav>
  </div>
</section>

<!-- Trade License Renewal Section -->
<section class="py-5 bg-light">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dark">Trade License <span style="color:#f1bf70;">Renewal</span></h2>
      <p class="text-muted">
        Stay compliant and keep your business running smoothly with our efficient license and PRO renewal services.
      </p>
    </div>

    <!-- Trade License Renewal Cards -->
    <h4 class="fw-bold mb-4 text-dark"><i class="fas fa-file-contract me-2"></i> Trade License Renewal</h4>
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-building me-2" style="color:#f1bf70;"></i> UAE</h5>
            <p class="text-muted mb-0">Annual business trade license renewal in the UAE.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-flag-usa me-2" style="color:#f1bf70;"></i> USA</h5>
            <p class="text-muted mb-0">Annual business trade license renewal in the USA.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-flag me-2" style="color:#f1bf70;"></i> UK</h5>
            <p class="text-muted mb-0">Annual business trade license renewal in the UK.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- PRO Renewal Services -->
    <h4 class="fw-bold mb-4 text-dark "><i class="fas fa-user-tie me-2"></i> PRO Renewal Services</h4>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-map-marker-alt me-2" style="color:#f1bf70;"></i> UAE / Dubai</h5>
            <p class="text-muted mb-0">Comprehensive PRO service renewal in the UAE and Dubai.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-city me-2" style="color:#f1bf70;"></i> USA / Delaware & New York</h5>
            <p class="text-muted mb-0">PRO service renewal in Delaware and New York.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold"><i class="fas fa-landmark me-2" style="color:#f1bf70;"></i> UK / England</h5>
            <p class="text-muted mb-0">PRO service renewal in the UK (England).</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section checked py-5" >
  <div class="container">
    <div class="form-wrapper mx-auto p-4 p-md-5">
      <h2 class="fw-bold text-white mb-4 text-center">Get Started With OGMBC</h2>
      <form>
        <div class="row g-3">
          <!-- First Name -->
          <div class="col-md-6">
            <label class="form-label text-white">First Name</label>
            <input type="text" class="form-control" placeholder="Enter first name" required>
          </div>
          <!-- Last Name -->
          <div class="col-md-6">
            <label class="form-label text-white">Last Name</label>
            <input type="text" class="form-control" placeholder="Enter last name" required>
          </div>
          <!-- Email -->
          <div class="col-md-6">
            <label class="form-label text-white">Email ID</label>
            <input type="email" class="form-control" placeholder="example@email.com" required>
          </div>
          <!-- Contact Number -->
          <div class="col-md-6">
            <label class="form-label text-white">Contact Number</label>
            <input type="tel" class="form-control" placeholder="+971 50 123 4567" required>
          </div>
          <!-- Business Activity -->
          <div class="col-12">
            <label class="form-label text-white">What is your business activity?</label>
            <input type="text" class="form-control" placeholder="e.g., Trading, Consulting, IT Services" required>
          </div>
          <!-- Number of Visas -->
          <div class="col-12">
            <label class="form-label text-white">How many visas will be required?</label>
            <input type="number" class="form-control" placeholder="Enter number of visas" required>
          </div>
          <!-- Shareholder Type -->
          <div class="col-12">
            <label class="form-label text-white">Type of shareholder</label>
            <select class="form-select" required>
              <option value="">Select...</option>
              <option value="individual">Individual</option>
              <option value="corporate">Corporate</option>
            </select>
          </div>
          <!-- Submit -->
          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary cta-glow px-4">Submit Details</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Floating Action Buttons -->
<div class="floating-buttons">
    <!-- WhatsApp Button -->
    <a href="https://wa.me/+971509860136" class="floating-btn whatsapp-btn" target="_blank" rel="noopener">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <!-- Back to Top Button -->
    <a href="#" class="floating-btn back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>
</div>

  <!-- Footer (same as home page) -->
<?php
include 'includes/footer.php'
?>
