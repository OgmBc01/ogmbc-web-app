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
        <h2 class="fw-bold" style="color:#d0aa4b;">Tax Consultancy</h2>
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
                <i class="fas fa-file-invoice-dollar me-2" style="color:#d0aa4b;"></i> UAE VAT & CT Services
              </h5>
              <ul class="list-unstyled lead">
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
                <i class="fas fa-id-card me-2" style="color:#d0aa4b;"></i> USA EIN Services
              </h5>
              <ul class="list-unstyled lead">
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
                <i class="fas fa-landmark me-2" style="color:#d0aa4b;"></i> UK VAT Services
              </h5>
              <ul class="list-unstyled lead">
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
        <h3 class="fw-bold text-center mb-4" style="color:#d0aa4b;">FAQs – UAE Tax</h3>
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

  <!-- CTA / Enquiry Section -->
  <section class="cta-section py-5">
    <div class="container">
      <div class="row align-items-stretch g-0">
        <!-- Left: Info -->
        <div class="col-lg-7 p-5 d-flex flex-column justify-content-center cta-info">
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
              <a href="tel:+971502923136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 292 3136</a>
            </div>
          </div>
        </div>
        <!-- Right: Form -->
        <div class="col-lg-5 bg-cta-gold p-5 d-flex align-items-center">
            <?php
            // Call the function before displaying the form
            handle_enquiry_form();
            ?>
            <form method="POST" class="w-100">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control cta-input" placeholder="Name" required>
                </div>
                <div class="mb-3">
                    <input type="email" name="email" class="form-control cta-input" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="contact" class="form-control cta-input" placeholder="Contact" required>
                </div>
                <div class="mb-3">
                    <select name="service" class="form-control cta-input" required>
                        <option value="" disabled selected>Choose service</option>
                        <?php
                        // Fetch all services from categories table
                        $query = "SELECT cat_id, cat_title FROM categories ORDER BY cat_title ASC";
                        $result = mysqli_query($connection, $query);
                        
                        if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                echo '<option value="' . htmlspecialchars($row['cat_title']) . '">' 
                                    . htmlspecialchars($row['cat_title']) . '</option>';
                            }
                        } else {
                            echo '<option value="" disabled>No services available</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" name="sub_service" class="form-control cta-input" placeholder="Type sub service" required>
                </div>
                <div class="mb-3">
                    <textarea name="message" class="form-control cta-input" rows="3" placeholder="Message" required></textarea>
                </div>
                <button type="submit" class="btn btn-dark w-100 py-2 fw-bold" style="background:#091e3e;">
                    Request A Quote
                </button>
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