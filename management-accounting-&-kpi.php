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
          <h2 class="fw-bold" style="color:#d0aa4b;">Management Accounting & KPIs</h2>
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
              <td><i class="fas fa-chart-line me-2" style="color:#d0aa4b;"></i> Budgeting</td>
              <td>
                At <strong>OGMBC</strong>, we specialize in crafting comprehensive budgets tailored to your business objectives. 
                Our meticulous approach ensures optimal resource allocation, helping you achieve financial goals with precision. 
                Trust us to guide you on the path to fiscal responsibility and success.
              </td>
            </tr>
            <tr>
              <td><i class="fas fa-lightbulb me-2" style="color:#d0aa4b;"></i> Decision Making</td>
              <td>
                In the fast-paced business environment, making informed decisions is paramount. 
                <strong>OGMBC</strong> provides strategic insights backed by accurate data and analysis, 
                empowering you to navigate challenges and seize opportunities confidently. 
                Your success is our priority, and informed decisions are the key.
              </td>
            </tr>
            <tr>
              <td><i class="fas fa-coins me-2" style="color:#d0aa4b;"></i> Cost Accounting</td>
              <td>
                Gain control over your finances with our Cost Accounting services. 
                We delve deep into your expenses, offering a granular understanding of where your resources are allocated. 
                Our strategies ensure cost efficiency, allowing you to optimize spending without compromising on quality or growth.
              </td>
            </tr>
            <tr>
              <td><i class="fas fa-tachometer-alt me-2" style="color:#d0aa4b;"></i> KPIs</td>
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