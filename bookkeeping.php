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
          <h2 class="fw-bold" style="color:#d0aa4b;">In-House & Remote Bookkeeping</h2>
          <p class="text-start">
            With our In-House and Remote Bookkeeping services, we ensure that distance is no barrier to efficient financial management. Experience the
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
        <!-- Left side: Picture -->
        <div class="col-lg-5">
          <img src="resources/img/in-house-bookkeeping.jpg" class="img-fluid rounded shadow" alt="In-House & Remote Bookkeeping">
        </div>

        <!-- Right side: Text -->
        <div class="col-lg-7">
          <div class="card shadow-sm p-4 h-100" style="font-size: 1.1rem;">
            <p class="text-muted mb-3">
              For those who prefer a <strong>hands-on approach</strong>, our <strong>In-House Bookkeeping services</strong> provide a personalized touch to your financial management. 
              Our team can work directly from your premises, ensuring a direct and immediate understanding of your business dynamics.
            </p>
            <p class="text-muted mb-4">
              At <strong>OGMBC</strong>, we are proficient in a wide range of accounting software, including:
            </p>

            <!-- Software List -->
            <div class="row g-3">
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> QuickBooks</div>
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> Zoho Books</div>
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> Tally</div>
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> Xero</div>
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> Sage</div>
              <div class="col-6 col-md-4"><i class="fas fa-check-circle me-2" style="color:#d0aa4b;"></i> Excel</div>
            </div>

            <p class="mt-4 text-muted">
              Whether you already use a specific tool or need guidance in choosing the right one, our team has the expertise to support your business.
            </p>
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
