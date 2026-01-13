<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
  <section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
      <h1 class="display-4 fw-bold">Bank Account Opening</h1>
      <p class="lead"></p>

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Services</a></li>
          <li class="breadcrumb-item"><a href="#" class="text-white text-decoration-none">Support</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">Bank Account Opening</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- UAE Bank Account Opening Section -->
  <section class="py-5 bg-light">
    <div class="container">

      <!-- Section Header -->
      <div class="text-center mb-5">
        <h2 class="fw-bold text-dark">
          UAE <span style="color:#d0aa4b;">Bank Account Opening</span>
        </h2>
        <p class="lead">
          Establish your banking relationship in one of the world’s leading business hubs with ease and efficiency.
        </p>
      </div>

      <!-- Content Row -->
      <div class="row align-items-center g-5">

        <!-- Image Column -->
        <div class="col-md-6">
          <img 
            src="resources/img/uae-bank-account.jpg" 
            class="img-fluid rounded shadow" 
            alt="UAE Bank Account Opening">
        </div>

        <!-- Text Column -->
        <div class="col-md-6">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3">
                <i class="fas fa-university me-2" style="color:#d0aa4b;"></i>
                Banking in the UAE
              </h5>
              <p class="mb-0 lead">
                Opening a bank account in the UAE is a streamlined process, typically requiring standard documents such as a passport and proof of address. 
                With a wide range of account options and efficient service, establishing banking relationships in the UAE provides access to a robust financial ecosystem in one of the world's leading business hubs.
              </p>
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
              <a href="tel:+971509860136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 986 0136</a>
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

<!-- Footer (same as home page) -->
<?php
include 'includes/footer.php'
?>