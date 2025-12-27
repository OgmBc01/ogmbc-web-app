<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
  <section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
      <h1 class="display-4 fw-bold">Business Valuation</h1>
      <p class="lead">Welcome to OGMBC, your trusted partner in Business Valuation.</p>

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Accounting & Taxation</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">Business Valuation</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/business-valuation.jpg" class="img-fluid rounded shadow" alt="business valuation">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#d0aa4b;">Business Valuation</h2>
          <p class="text-start">
            Understanding the true worth of your business is essential for making informed decisions, whether you're planning to sell, seeking funding , or
            strategizing for the future Conducting a business valuation with OGMBC provides you with a clear and accurate assessment of your company's value, enabling you to showcase its
            strengths, attract investors, and identify areas for improvement.
          </p>

          <p class="text-start">Our experienced consultants utilize a variety of business valuation methods, including market
            based, income based, and asset bas ed approaches, ensuring a comprehensive evaluation that aligns with your industry and business model.
          </p> 

          <p class="text-start">
          Trust OGMBC to guide you through the valuation process, empowering you with the insights needed to propel your business forward.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta-section py-5">
    <div class="container">
      <div class="row align-items-stretch g-0">
        <div class="col-lg-7 bg-white p-5 d-flex flex-column justify-content-center cta-info">
          <h5 class="fw-bold mb-2" style="color:#d0aa4b;">REQUEST A QUOTE</h5>
          <h2 class="fw-bold mb-3" style="color:#091e3e;">Need A Free Quote? Please Feel Free to Contact Us</h2>
          <div class="cta-divider mb-4" style="height:4px; width:120px; background:#d0aa4b; border-radius:2px;"></div>
            <div class="d-flex gap-4 mb-3 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-arrow-return-left" style="color:#d0aa4b; font-size:1.5rem;"></i>
                <span class="fw-semibold" style="color:#091e3e;">Reply within 24 hours</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-telephone" style="color:#d0aa4b; font-size:1.5rem;"></i>
                <span class="fw-semibold" style="color:#091e3e;">24 hrs telephone support</span>
              </div>
            </div>
            <p class="lead mb-4" style="color:#747576;">
              We promptly respond to customer inquiries for quotations, providing a free and transparent request process. Our commitment is to deliver accurate and detailed information, ensuring clients receive the best insights and options tailored to their needs. Your satisfaction is our priority, and we strive to make the quotation process seamless, informative, and cost-free for a hassle-free experience.
            </p>
          <div class="d-flex align-items-center gap-3 mt-2">
            <div class="cta-phone-icon d-flex align-items-center justify-content-center" style="background:#d0aa4b; width:48px; height:48px; border-radius:8px;">
              <i class="bi bi-telephone-fill" style="color:#fff; font-size:1.5rem;"></i>
            </div>
            <div>
              <span class="fw-semibold" style="color:#091e3e;">Call to ask any question</span><br>
              <span class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971502923136</span>
            </div>
          </div>
        </div>
        <div class="col-lg-5 bg-cta-gold p-5 d-flex align-items-center">
          <form class="w-100">
            <div class="mb-3">
              <input type="text" class="form-control cta-input" placeholder="Your Name">
            </div>
            <div class="mb-3">
              <input type="email" class="form-control cta-input" placeholder="Your Email">
            </div>
            <div class="mb-3">
              <select class="form-select cta-input">
                <option selected>Select A Service</option>
                <option value="audit">Audit & Assurance</option>
                <option value="reporting">Financial Reporting</option>
                <option value="advisory">Advisory</option>
                <option value="tax">Tax Consulting</option>
                <option value="outsourcing">Outsourced Finance</option>
                <option value="training">Training</option>
              </select>
            </div>
            <div class="mb-3">
              <input type="text" class="form-control cta-input" placeholder="Sub-Service">
          </div>
          <div class="mb-3">
            <textarea class="form-control cta-input" rows="3" placeholder="Message"></textarea>
          </div>
          <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">Request A Quote</button>
          </form>
        </div>
      </div>
    </div>
  </section>
  
<!-- Footer (same as home page) -->
<?php
include 'includes/footer.php'
?>