<?php
include 'includes/database.php';
include 'includes/header-1.php'
?> 

  <!-- Hero Section -->
  <section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
      <h1 class="display-4 fw-bold">IFRS Advisory</h1>
      <p class="lead"></p>

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Statutory Compliance</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">IFRS Advisory</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- About Story -->
  <section class="section bg-light text-dark">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="resources/img/ifrs-advisory.jpeg" class="img-fluid rounded shadow" alt="ifrs advisory">
        </div>
        <div class="col-lg-6">
          <h2 class="fw-bold" style="color:#d0aa4b;">IFRS Advisory</h2>
          <p class="text-start">
            At OGMBC Consultants, we understand the complexities and challenges that businesses face in navigating the ever evolving lands cape of International
            Financial Reporting Standards (IFRSs). With our dedicated team of experts, we offer comprehensive IFRS advisory services tail ore d to meet the unique
            needs of your organization. Whether you're a multinational corporation or a growing enterprise, we're here to provide you wit h t he guidance and
            support necessary to ensure compliance and optimize financial reporting practices.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- IFRS Advisory Services Section -->
  <section class="py-5 bg-light">
    <div class="container">
      <!-- Section Header -->
      <div class="text-center mb-5">
        <h2 class="fw-bold text-dark">Our <span style="color:#d0aa4b;">IFRS Advisory Services</span> Include</h2>
        <p class="text-muted">
          At OGMBC Consultants, we provide end-to-end IFRS advisory services tailored to your business needs. 
          From implementation and training to compliance reviews and advisory support, our team ensures accuracy, 
          transparency, and global compliance in your financial reporting.
        </p>
      </div>

      <!-- Cards Row -->
      <div class="row g-4">
        <!-- Card 1 -->
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body service-card-body">
              <span class="service-icon"><i class="fas fa-cogs"></i></span>
              <h5 class="fw-bold">IFRS Implementation Assistance</h5>
              <p class="text-muted">
                Hands-on support from assessment to final adoption, helping you seamlessly 
                integrate IFRS into your reporting framework.
              </p>
            </div>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body service-card-body">
              <span class="service-icon"><i class="fas fa-chalkboard-teacher"></i></span>
              <h5 class="fw-bold">IFRS Training & Workshops</h5>
              <p class="text-muted">
                Interactive sessions designed for finance professionals at all levels, covering 
                IFRS principles, updates, and best practices.
              </p>
            </div>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body service-card-body">
              <span class="service-icon"><i class="fas fa-check-circle"></i></span>
              <h5 class="fw-bold">IFRS Compliance Reviews</h5>
              <p class="text-muted">
                Thorough reviews to ensure accuracy and consistency in financial statements, 
                with actionable recommendations for improvement.
              </p>
            </div>
          </div>
        </div>

        <!-- Card 4 -->
        <div class="col-md-6 col-lg-3">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body service-card-body">
              <span class="service-icon"><i class="fas fa-lightbulb"></i></span>
              <h5 class="fw-bold">IFRS Advisory Support</h5>
              <p class="text-muted">
                Personalized guidance on complex IFRS issues, transactions, and reporting, 
                ensuring compliance and strategic decision-making.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="py-5">
    <div class="container">
      <div class="mt-5">
        <h3 class="fw-bold text-center mb-4"  style="color: #d0aa4b;">Frequently Asked Questions (FAQs)</h3>
        <div class="accordion" id="ifrsFAQ">
          <!-- FAQ 1 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq1">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                What are International Financial Reporting Standards (IFRSs)?
              </button>
            </h2>
            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#ifrsFAQ">
              <div class="accordion-body text-muted">
                IFRSs are global accounting standards developed by IASB to enhance transparency, comparability, 
                and reliability in financial reporting.
              </div>
            </div>
          </div>
          <!-- FAQ 2 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq2">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                Why is compliance with IFRS important for my business?
              </button>
            </h2>
            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#ifrsFAQ">
              <div class="accordion-body text-muted">
                Compliance boosts credibility, improves transparency, fosters investor confidence, and ensures 
                comparability for global stakeholders.
              </div>
            </div>
          </div>
          <!-- FAQ 3 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq3">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                How can OGMBC Consultants help with IFRS compliance?
              </button>
            </h2>
            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#ifrsFAQ">
              <div class="accordion-body text-muted">
                We offer implementation, training, compliance reviews, and advisory services tailored to your 
                unique reporting needs and challenges.
              </div>
            </div>
          </div>
          <!-- FAQ 4 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq4">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                What sets OGMBC Consultants apart?
              </button>
            </h2>
            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#ifrsFAQ">
              <div class="accordion-body text-muted">
                Our deep IFRS expertise, client-focused approach, and commitment to excellence set us apart. 
                We deliver practical, results-oriented solutions.
              </div>
            </div>
          </div>
          <!-- FAQ 5 -->
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq5">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                How can I get started with OGMBC?
              </button>
            </h2>
            <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#ifrsFAQ">
              <div class="accordion-body text-muted">
                Simply contact us for a consultation. We’ll assess your needs and tailor our services 
                to ensure smooth IFRS compliance and reporting success.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Closing CTA -->
      <div class="text-center mt-5">
        <p class="fw-bold">
          For expert guidance in IFRS compliance, trust <span style="color:#d0aa4b;">OGMBC Consultants</span> as your dedicated partner.  
          Contact us today to learn how we can help your organization thrive in the global marketplace.
        </p>
        <a href="#contact" class="btn px-4 py-2" style="background:#d0aa4b; color:#111827; font-weight:600; border:none;">
          Contact Us Today
        </a>
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
            <p class="mb-4" style="color:#747576;">
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

  <!-- Floating Action Buttons -->
  <div class="floating-buttons">
      <!-- WhatsApp Button -->
      <a href="https://wa.me/+971502923136" class="floating-btn whatsapp-btn" target="_blank" rel="noopener">
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

