<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Management Accounting & KPIs</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Accounting & Taxation</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Management Accounting & KPIs</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/management-accounting.jpg" class="img-fluid rounded shadow" alt="management accounting">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">Management Accounting & KPIs</h2>
          <p class="text-start">
            We are your trusted partner in navigating the intricate world of Management Accounting. 
            Our expert team is committed to helping your business thrive through strategic financial planning and decision making. 
            Explore the pillars of our services: <strong>Budgeting, Decision Making, Cost Accounting, and Key Performance Indicators (KPIs).</strong>
          </p>
        </div>
      </div>
    </div>
  </section>

<!-- Management Accounting & KPIs Section -->
<section class="py-5" style="background:#f9fafb;">
  <div class="container">

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle shadow-sm bg-white">
        <thead class="table-dark">
          <tr>
            <th scope="col" style="width:20%;">Service</th>
            <th scope="col">Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><i class="fas fa-chart-line me-2" style="color:#f1bf70;"></i> Budgeting</td>
            <td>
              At <strong>OGMBC</strong>, we specialize in crafting comprehensive budgets tailored to your business objectives. 
              Our meticulous approach ensures optimal resource allocation, helping you achieve financial goals with precision. 
              Trust us to guide you on the path to fiscal responsibility and success.
            </td>
          </tr>
          <tr>
            <td><i class="fas fa-lightbulb me-2" style="color:#f1bf70;"></i> Decision Making</td>
            <td>
              In the fast-paced business environment, making informed decisions is paramount. 
              <strong>OGMBC</strong> provides strategic insights backed by accurate data and analysis, 
              empowering you to navigate challenges and seize opportunities confidently. 
              Your success is our priority, and informed decisions are the key.
            </td>
          </tr>
          <tr>
            <td><i class="fas fa-coins me-2" style="color:#f1bf70;"></i> Cost Accounting</td>
            <td>
              Gain control over your finances with our Cost Accounting services. 
              We delve deep into your expenses, offering a granular understanding of where your resources are allocated. 
              Our strategies ensure cost efficiency, allowing you to optimize spending without compromising on quality or growth.
            </td>
          </tr>
          <tr>
            <td><i class="fas fa-tachometer-alt me-2" style="color:#f1bf70;"></i> KPIs</td>
            <td>
              Unlock the potential of your business with Key Performance Indicators (KPIs). 
              <strong>OGMBC</strong>'s experts identify and implement measurable metrics to gauge organizational performance. 
              Stay on course and achieve your objectives with our KPI-driven approach, turning insights into actionable strategies for success.
            </td>
          </tr>
        </tbody>
      </table>
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