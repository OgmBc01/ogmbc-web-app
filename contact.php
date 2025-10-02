<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Contact Us</h1>
    <p class="lead"></p>
  </div>
</section>

<section class="py-5 text-white" style="background-color:#111827;">
  <div class="container text-center mb-5">
    <h2 class="fw-bold">We're Jusy a <span style="color:#f1bf70;">Click</span> Away</h2>
    <p class="text-light">Get in touch with us for inquiries, consultations, or more information.</p>
  </div>

  <!-- Contact Form + Map -->
  <div class="container">
    <div class="row g-4 align-items-stretch">
      <!-- Contact Form -->
      <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0" style="background-color:#ffffff;">
          <div class="card-body p-4">
            <h4 class="fw-bold text-dark mb-3">Send Us a Message</h4>
            <form>
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="Your Name" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" placeholder="Your Email" required>
              </div>
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="Subject" required>
              </div>
              <div class="mb-3">
                <textarea class="form-control" rows="5" placeholder="Your Message" required></textarea>
              </div>
              <button type="submit" class="btn w-100 fw-bold" style="background-color:#f1bf70; color:#111827;">Submit</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Map -->
      <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0" style="background-color:#ffffff;">
          <div class="card-body p-0">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d19801.343354363723!2d-75.1449!3d38.7746!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89b8ff5f1cb52f5d%3A0xf7d663f3e1a3bb6f!2sLewes%2C%20Delaware!5e0!3m2!1sen!2sus!4v1691764137000!5m2!1sen!2sus" 
              width="100%" 
              height="100%" 
              style="border:0; min-height:400px;" 
              allowfullscreen="" 
              loading="lazy">
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Office Locations -->
<section class="py-5 text-white" style="background-color:#111827;">
  <div class="container">
    <div class="text-center mb-5">
      <h3 class="fw-bold">Our <span style="color:#f1bf70;">Offices</span></h3>
      <p class="text-light">Visit or reach out to one of our offices worldwide.</p>
    </div>
    <div class="row g-4">
      <!-- Dubai Office -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0" style="background-color:#ffffff;">
          <div class="card-body">
            <h5 class="fw-bold text-dark">Dubai, UAE</h5>
            <p class="text-muted mb-1">OGM Business Consultants FZCO</p>
            <p class="text-muted mb-1">Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai,</p>
            <p class="text-muted mb-1">United Arab Emirates. P.O. Box 33418</p>
            <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmbc.ae" class="text-decoration-none">info@ogmbc.ae</a></p>
            <p class="mb-0"><strong>Tel:</strong> +971509860136</p>
          </div>
        </div>
      </div>
      <!-- Delaware Office -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0" style="background-color:#ffffff;">
          <div class="card-body">
            <h5 class="fw-bold text-dark">Delaware, USA</h5>
            <p class="text-muted mb-1">OGMBC Holding Co. Ltd</p>
            <p class="text-muted mb-1">16192 Coastal Highway, Lewes</p>
            <p class="text-muted mb-1">P.O. Box 19958, Delaware, United States</p>
            <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmholding.com" class="text-decoration-none">info@ogmholding.com</a></p>
            <p class="mb-0"><strong>Tel:</strong> +971509860136</p>
          </div>
        </div>
      </div>

      <!-- London Office -->
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0" style="background-color:#ffffff;">
          <div class="card-body">
            <h5 class="fw-bold text-dark">London, UK</h5>
            <p class="text-muted mb-1">OGMBC UK Ltd</p>
            <p class="text-muted mb-1">128 City Road, EC1V 2NX</p>
            <p class="text-muted mb-1">London, United Kingdom</p>
            <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmconsultants.com" class="text-decoration-none">info@ogmconsultants.com</a></p>
            <p class="mb-0"><strong>Tel:</strong> +971509860136</p>
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