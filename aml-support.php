<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">AML Support</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Statutory Compliance</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">AML Support</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/anti-money-laundering-support.jpeg" class="img-fluid rounded shadow" alt="anti money laundering support">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">AML Support</h2>
          <p class="text-start">
            Anti Money Laundering (AML) refers to measures taken to detect and prevent the illegal process of concealing the origins of money obtained through
            criminal activities. Implementing a robust AML policy and procedures is essential for businesses to mitigate risks and comply wi th regulatory
            requirements.
          </p>
        </div>
      </div>
    </div>
  </section>

<!-- AML Services Section -->
<section class="py-5 text-white" style="background-color: #111827;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">What We <span style="color:#f1bf70;">Offer</span></h2>
      <p class="text-light">
        At OGMBC Consultants, we provide robust Anti-Money Laundering (AML) solutions designed to safeguard businesses, ensure compliance, and strengthen governance.
      </p>
    </div>

    <div class="row g-4">
      <!-- Card 1 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 bg-light text-dark">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-shield-alt me-2" style="color:#f1bf70;"></i> Setting AML Policy
            </h5>
            <p class="mb-0">
              AML policies outline guidelines and protocols for identifying and reporting suspicious activities, ensuring adherence to legal standards.
            </p>
          </div>
        </div>
      </div>
      <!-- Card 2 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 bg-light text-dark">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-file-signature me-2" style="color:#f1bf70;"></i> Effective AML Procedures
            </h5>
            <p class="mb-0">
              We help businesses establish effective AML procedures, including customer due diligence, identity verification, and monitoring of transactions.
            </p>
          </div>
        </div>
      </div>
      <!-- Card 3 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 bg-light text-dark">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-search me-2" style="color:#f1bf70;"></i> Business Due Diligence
            </h5>
            <p class="mb-0">
              Investigate company backgrounds, identify potential legal issues, and assess operational efficiency to minimize risks.
            </p>
          </div>
        </div>
      </div>
      <!-- Card 4 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 bg-light text-dark">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-users me-2" style="color:#f1bf70;"></i> Training Staff
            </h5>
            <p class="mb-0">
              Regular staff training on AML practices ensures vigilance, compliance, and adherence to established protocols.
            </p>
          </div>
        </div>
      </div>
      <!-- Card 5 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 bg-light text-dark">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-sync-alt me-2" style="color:#f1bf70;"></i> Review & Update Policies
            </h5>
            <p class="mb-0">
              Continuous review and updating of AML policies ensures adaptability to evolving regulatory landscapes and emerging threats.
            </p>
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