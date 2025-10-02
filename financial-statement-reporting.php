<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Financial Statement Reporting</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Statutory Compliance</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Financial Statement Reporting</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/financial-reporting.jpg" class="img-fluid rounded shadow" alt="financial reporting">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">Financial Statement Reporting</h2>
          <p class="text-start">
            Welcome to OGMBC, your trusted partner in navigating the complex landscape of financial statement reporting in accordance with International
            Financial Reporting Standards (IFRSs). As experts in the field, we understand the importance of accurate and transparent fina nci al reporting for
            businesses operating in today's global economy. Whether you're a multinational corporation or a small to medium enterprise, adhe ring to IFRSs is
            essential for ensuring credibility, transparency, and compliance with regulatory requirements.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- IFRS Services Section -->
<section class="py-5 bg-light">
  <div class="container">
    <!-- Section Header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold">IFRS <span style="color:#f1bf70;">Advisory & Financial Reporting Services</span></h2>
      <p class="text-muted">
        At OGMBC Consultants, we offer comprehensive services to assist you in preparing and presenting 
        your financial statements in accordance with IFRSs. Our expert team ensures accuracy, compliance, 
        and transparency in financial reporting.
      </p>
    </div>

    <!-- Cards Row -->
    <div class="row g-4">
      <!-- Card 1 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold">
              <i class="fas fa-project-diagram me-2" style="color:#f1bf70;"></i> IFRS Implementation
            </h5>
            <p class="text-muted">
              Guidance and support in implementing IFRSs within your organization, ensuring seamless 
              integration and compliance with the latest standards.
            </p>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold">
              <i class="fas fa-file-invoice-dollar me-2" style="color:#f1bf70;"></i> Financial Statement Preparation
            </h5>
            <p class="text-muted">
              Preparation of financial statements in line with IFRSs, including balance sheet, income 
              statement, statement of changes in equity, and cash flow statement.
            </p>
          </div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold">
              <i class="fas fa-lightbulb me-2" style="color:#f1bf70;"></i> Technical Accounting Advice
            </h5>
            <p class="text-muted">
              Expert guidance on complex IFRS issues, helping you navigate intricate reporting 
              requirements and ensure accuracy in financial disclosures.
            </p>
          </div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold">
              <i class="fas fa-chalkboard-teacher me-2" style="color:#f1bf70;"></i> Training & Workshops
            </h5>
            <p class="text-muted">
              Customized training programs and workshops to equip your team with the knowledge and 
              skills to understand and apply IFRSs effectively.
            </p>
          </div>
        </div>
      </div>

      <!-- Card 5 -->
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold">
              <i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> IFRS Compliance Review
            </h5>
            <p class="text-muted">
              Comprehensive reviews of financial statements to ensure full compliance with IFRSs 
              and identify opportunities for improvement.
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