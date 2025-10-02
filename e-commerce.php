<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">E-Commerce</h1>
    <p class="lead">Welcome to OGMBC Your Premier Partner in E Commerce Business Formation!</p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Company Formation</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">E-Commerce</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/e-commerce.jpg" class="img-fluid rounded shadow" alt="e-commerce">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">E-Commerce Business Formation</h2>
          <p class="text-start">
            Are you ready to embark on a thrilling journey into the world of online retail? Look no further than OGMBC, your trusted ally in E-Commerce
            business formation. Whether you're eyeing the vast marketplaces of Amazon or planning to set up shop on popular platforms lik e S hopify or dive
            into the lucrative realm of dropshipping , we've got you
          </p>
        </div>
      </div>
    </div>
  </section>

<!-- E-Commerce Business Formation Services -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="color:#f1bf70;">E-Commerce Business Formation Services</h2>
      <p class="lead text-muted">Helping you launch, expand, and optimize your online business worldwide.</p>
    </div>

    <div class="row g-4">
      <!-- Amazon E-Commerce -->
      <div class="col-md-6">
        <div class="card h-100 shadow-sm p-4">
          <i class="fab fa-amazon fa-2x mb-3" style="color:#f1bf70;"></i>
          <h5 class="fw-bold">Amazon E-Commerce Business Registration</h5>
          <p class="text-muted">
            Unlock the potential of the world's largest online marketplace with our comprehensive registration services. 
            From seller account setup to optimizing product listings, we empower you to maximize your presence and sales.
          </p>
        </div>
      </div>

      <!-- Global Expansion -->
      <div class="col-md-6">
        <div class="card h-100 shadow-sm p-4">
          <i class="fas fa-globe fa-2x mb-3" style="color:#f1bf70;"></i>
          <h5 class="fw-bold">Global Expansion Services</h5>
          <p class="text-muted">
            Expand your footprint across the USA, Canada, Australia, UK, Europe, and the Middle East. 
            We help you navigate international regulations seamlessly, so your business thrives globally.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Launching Your First E-Commerce Store -->
<section class="py-5" style="background:#111827;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="color:#f1bf70;">Launching Your First E-Commerce Store</h2>
      <p class="lead text-light">Step confidently into the online marketplace with our tailored support.</p>
    </div>

    <div class="row g-4">
      <!-- Shopify Setup -->
      <div class="col-md-6">
        <div class="card h-100 p-4 shadow-sm">
          <i class="fab fa-shopify fa-2x mb-3" style="color:#f1bf70;"></i>
          <h5 class="fw-bold text-light">Shopify Setup & Optimization</h5>
          <p class="text-light">
            From theme selection to payment gateway configuration, our experts guide you through creating a Shopify store 
            optimized for sales and growth.
          </p>
        </div>
      </div>

      <!-- Dropshipping Guidance -->
      <div class="col-md-6">
        <div class="card h-100 p-4 shadow-sm">
          <i class="fas fa-truck-loading fa-2x mb-3" style="color:#f1bf70;"></i>
          <h5 class="fw-bold text-light">Dropshipping Business Guidance</h5>
          <p class="text-light">
            Explore the world of dropshipping with expert support. From choosing reliable suppliers to efficient order 
            fulfillment strategies, we set you up for long-term success.
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
