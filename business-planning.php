<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
<section class="about-hero d-flex align-items-center text-center text-white">
  <div class="container">
    <h1 class="display-4 fw-bold">Business Planning</h1>
    <p class="lead"></p>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center">
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
        <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Accounting & Taxation</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Business Planning</li>
      </ol>
    </nav>
  </div>
</section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/business-planning.jpg" class="img-fluid rounded shadow" alt="business planning">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#f1bf70;">Business Planning</h2>
          <p class="text-start">
            At OGMBC , we understand that a well thought out business plan is the cornerstone of success for any venture. Whether you're a startup looking to
            secure funding or an established business aiming to refine your strategy, our Business Planning Service is designed to guide you through the intricacies
            of strategic planning and model development.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Business Plan Components Section -->
<section class="py-5" style="background:#f9fafb;">
  <div class="container">
    <!-- Header -->
    <div class="text-center mb-5">
      <h2 class="fw-bold" style="color:#f1bf70;">Business Plan Components</h2>
      <p class="lead text-muted">A well-structured business plan is the foundation of success. Explore the essential sections below.</p>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered align-middle shadow-sm">
        <thead class="table-dark">
          <tr>
            <th scope="col">Section</th>
            <th scope="col">Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="fw-bold">Executive Summary</td>
            <td>A concise overview of your business, highlighting key aspects such as mission, vision, and objectives.</td>
          </tr>
          <tr>
            <td class="fw-bold">Company Description</td>
            <td>Detailed information about your company's history, structure, and core values.</td>
          </tr>
          <tr>
            <td class="fw-bold">Market Analysis</td>
            <td>In-depth research on your target market, industry trends, and competitive landscape.</td>
          </tr>
          <tr>
            <td class="fw-bold">Business Model</td>
            <td>A clear outline of your business model, including value proposition, customer segments, and revenue streams.</td>
          </tr>
          <tr>
            <td class="fw-bold">Marketing and Sales Strategy</td>
            <td>Plans for reaching your target audience, promoting your products/services, and driving sales.</td>
          </tr>
          <tr>
            <td class="fw-bold">Organizational Structure</td>
            <td>Details on the hierarchy, roles, and responsibilities within your organization.</td>
          </tr>
          <tr>
            <td class="fw-bold">Financial Projections</td>
            <td>Comprehensive forecasts covering income statements, balance sheets, and cash flow projections.</td>
          </tr>
          <tr>
            <td class="fw-bold">Risk Analysis</td>
            <td>Identification and mitigation strategies for potential risks that could impact your business.</td>
          </tr>
          <tr>
            <td class="fw-bold">Implementation Plan</td>
            <td>Step-by-step guide on how to execute your business plan, including timelines and milestones.</td>
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