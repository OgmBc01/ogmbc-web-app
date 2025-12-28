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

  <section class="py-5">
    <div class="container text-center mb-5">
      <h2 class="fw-bold">We're Just a <span style="color:#d0aa4b;">Click</span> Away</h2>
      <p class="lead">Get in touch with us for inquiries, consultations, or more information.</p>
    </div>

    <!-- Contact Form + Map -->
    <div class="container">
      <div class="row g-4 align-items-stretch">
        <!-- Contact Form -->
        <div class="col-lg-6">
          <div class="card h-100 shadow-sm border-0" style="background-color:#f7f0d9;">
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
                <button type="submit" class="btn w-100 fw-bold" style="background-color:#d0aa4b; color:#111827;">Submit</button>
              </form>
            </div>
          </div>
        </div>

        <!-- Map -->
        <div class="col-lg-6">
          <div class="card h-100 shadow-sm border-0" style="background-color:#f7f0d9;">
            <div class="card-body p-0">
              <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14441.992555972345!2d55.242136353254054!3d25.18641825551446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e5f69d0bf460681%3A0xf852a2f5d28ca4d2!2sThe%20Regal%20Tower%20-%20Business%20Bay%20-%20Dubai!5e0!3m2!1sen!2sae!4v1766785134455!5m2!1sen!2sae"
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
  <section class="py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h3 class="fw-bold">Our <span style="color:#d0aa4b;">Offices</span></h3>
        <p class="lead">Visit or reach out to one of our offices worldwide.</p>
      </div>
      <div class="row g-4">
        <!-- Dubai Office -->
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0" style="background-color:#f7f0d9;">
            <div class="card-body">
              <h5 class="fw-bold text-dark">Dubai, UAE</h5>
              <p class="lead mb-1">OGM Business Consultants FZCO</p>
              <p class="lead mb-1">Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai,</p>
              <p class="lead mb-1">United Arab Emirates. P.O. Box 33418</p>
              <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmbc.ae" class="text-dark">info@ogmbc.ae</a></p>
              <p class="mb-0"><strong>Tel:</strong> +971509860136</p>
            </div>
          </div>
        </div>
        <!-- Delaware Office -->
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0" style="background-color:#f7f0d9;">
            <div class="card-body">
              <h5 class="fw-bold text-dark">Delaware, USA</h5>
              <p class="lead mb-1">OGMBC Holding Co. Ltd</p>
              <p class="lead mb-1">16192 Coastal Highway, Lewes</p>
              <p class="lead mb-1">P.O. Box 19958, Delaware, United States</p>
              <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmauditing.com" class="text-dark">info@ogmauditing.com</a></p>
              <!-- <p class="mb-0"><strong>Tel:</strong> +971509860136</p> -->
            </div>
          </div>
        </div>

        <!-- London Office -->
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0" style="background-color:#f7f0d9;">
            <div class="card-body">
              <h5 class="fw-bold text-dark">London, UK</h5>
              <p class="lead mb-1">OGMBC UK Ltd</p>
              <p class="lead mb-1">128 City Road, EC1V 2NX</p>
              <p class="lead mb-1">London, United Kingdom</p>
              <p class="mb-1"><strong>Email:</strong> <a href="mailto:info@ogmauditing.com" class="text-dark">info@ogmauditing.com</a></p>
              <!-- <p class="mb-0"><strong>Tel:</strong> +971509860136</p> -->
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

<!-- Footer (same as home page) -->
<?php
include 'includes/footer.php'
?>