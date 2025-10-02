<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Bookkeeping</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Accounting & Taxation</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Bookkeeping</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/bookkeeping.jpg" class="img-fluid rounded shadow" alt="bookkeeping">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">Remote Bookkeeping</h2>
          <p class="text-start">
            With our Remote Bookkeeping services, we ensure that distance is no barrier to efficient financial management. Experience the
            convenience of accessing your financial records and reports from anywhere in the world, while our skilled professionals handl e
            the intricacies of your accounts.
          </p>
        </div>
      </div>
    </div>
  </section>

<!-- In-house Bookkeeping Section -->
<section class="py-5" style="background:#f9fafb;">
  <div class="container">
    <div class="row align-items-center g-4">
      <!-- Left side: Illustration/Icon -->
      <div class="col-lg-5 text-center">
        <div class="p-4">
          <i class="fas fa-book-open fa-4x mb-3" style="color:#f1bf70;"></i>
          <h4 class="fw-bold" style="color:#0b1224;">In-House Bookkeeping</h4>
        </div>
      </div>

      <!-- Right side: Text -->
      <div class="col-lg-7">
        <div class="card shadow-sm p-4 h-100">
          <p class="text-muted mb-3">
            For those who prefer a <strong>hands-on approach</strong>, our <strong>In-House Bookkeeping services</strong> provide a personalized touch to your financial management. 
            Our team can work directly from your premises, ensuring a direct and immediate understanding of your business dynamics.
          </p>
          <p class="text-muted mb-4">
            At <strong>OGMBC</strong>, we are proficient in a wide range of accounting software, including:
          </p>

          <!-- Software List -->
          <div class="row g-3">
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> QuickBooks</div>
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> Zoho Books</div>
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> Tally</div>
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> Xero</div>
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> Sage</div>
            <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#f1bf70;"></i> Excel</div>
          </div>

          <p class="mt-4 text-muted">
            Whether you already use a specific tool or need guidance in choosing the right one, our team has the expertise to support your business.
          </p>
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
