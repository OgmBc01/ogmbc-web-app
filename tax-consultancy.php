<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Tax Consultancy</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Accounting & Taxation</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Tax Consultancy</li>
      </ol>
    </nav>
  </div>
</section>

<!-- Tax Consultancy Section -->
<section class="py-5" style="background:#f9fafb;">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="color:#f1bf70;">Tax Consultancy</h2>
      <p class="lead text-muted">
        Expert tax consultancy services tailored to your business needs across the UAE, USA, and UK. 
        Explore our specialized services below.
      </p>
    </div>

    <!-- Cards for Services -->
    <div class="row g-4">
      <!-- UAE VAT & CT -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-file-invoice-dollar me-2" style="color:#f1bf70;"></i> UAE VAT & CT Services
            </h5>
            <ul class="list-unstyled text-muted">
              <li>VAT Registration</li>
              <li>VAT Return Filing</li>
              <li>VAT Refund</li>
              <li>VAT Reconsideration</li>
              <li>VAT De-registration</li>
              <li>Corporate Tax Registration</li>
              <li>Corporate Tax Return Filing</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- USA EIN -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-id-card me-2" style="color:#f1bf70;"></i> USA EIN Services
            </h5>
            <ul class="list-unstyled text-muted">
              <li>EIN Registration</li>
              <li>Tax Return Filing</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- UK VAT -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <h5 class="fw-bold mb-3">
              <i class="fas fa-landmark me-2" style="color:#f1bf70;"></i> UK VAT Services
            </h5>
            <ul class="list-unstyled text-muted">
              <li>VAT Registration</li>
              <li>Corporate Tax</li>
              <li>CIS Return Filing</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="mt-5">
      <h3 class="fw-bold text-center mb-4" style="color:#f1bf70;">FAQs – UAE Tax</h3>
      <div class="accordion" id="faqAccordion">
        <!-- Q1 -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faq1">
            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true">
              What is VAT, and who needs to register for it in the UAE?
            </button>
          </h2>
          <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted">
              VAT (Value Added Tax) is a consumption tax. In the UAE, businesses meeting certain criteria are required to register for VAT.
              This includes businesses with annual taxable supplies exceeding the mandatory registration threshold.
            </div>
          </div>
        </div>

        <!-- Q2 -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faq2">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
              How often do I need to file VAT returns in the UAE?
            </button>
          </h2>
          <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted">
              VAT returns in the UAE are typically filed quarterly. The exact frequency may depend on the nature and size of your business.
            </div>
          </div>
        </div>

        <!-- Q3 -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faq3">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
              What is an EIN, and why do I need one in the United States?
            </button>
          </h2>
          <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted">
              An Employer Identification Number (EIN) is a unique identifier for businesses. 
              It is required for various purposes, including opening a business bank account, hiring employees, and filing taxes.
            </div>
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