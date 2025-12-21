<?php
include 'includes/database.php';
include 'includes/header.php';

// Detect error type from URL parameter
$error_type = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!-- Error Alert Container -->
<?php if ($error_type): ?>
<div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); max-width: 600px; z-index: 9999; margin: 0;">
    <?php if ($error_type === 'session'): ?>
        <strong>Session Expired!</strong> Your session has expired. Please <a href="login.php" class="alert-link">login again</a> to continue.
    <?php elseif ($error_type === 'permission'): ?>
        <strong>Access Denied!</strong> You do not have the required permissions to access the admin area. Please <a href="contact.php" class="alert-link">contact an administrator</a> for assistance.
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        // Auto-dismiss after 4 seconds
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(errorAlert);
            bsAlert.close();
        }, 7000);
    }
});
</script>
<?php endif; ?>

<!-- Hero Section (Carousel) -->

  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
  <!-- indicators (optional) -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>

  <div class="carousel-inner">
    <!-- Slide 1 -->
    <div class="carousel-item active" data-bs-interval="3000">
      <div class="container">
        <div class="row align-items-center gy-4">
          <div class="col-lg-7 mb-5" style="margin-top: 5rem;">
            <span class="chip">Auditing • Accounting • Taxation • Advisory</span>
            <h1 class="hero-title">Empowering growth through strategic advisory and financial transparency</h1>
            <p class="text-light">We providing IFRS-based reporting and strategic advisory that empower informed decisions and strengthen stakeholder trust</p>
            <div class="d-flex gap-3 flex-wrap">
              <a class="btn btn-primary" href="ratios.php">Ratio Calculator</a>
              <a class="btn btn-ghost" href="#services">Explore services</a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
              <!-- Global presence row -->
              <div class="global-presence d-flex align-items-center justify-content-center py-2">
                <span class="fw-bold" style="color:#d0aa4b; font-size:1.1rem;">Our global presence: UAE, UK, USA</span>
              </div>
              
              <div class="d-flex flex-column gap-3 contact-info">
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                  <span><a href="tel:+971502923136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 292 3136</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                  <span><a href="mailto:info@ogmbc.ae" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">info@ogmbc.ae</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-geo-alt-fill"></i></div>
                  <span>Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai UAE. P.O. Box 33418</span>
                </div>
              </div>
              <a href="contact.php" class="btn btn-secondary mt-3 text-white">
                Request Proposal
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="carousel-item" data-bs-interval="3000">
      <div class="container">
        <div class="row align-items-center gy-4">
          <div class="col-lg-7 mb-5">
            <span class="chip">Advisory & Compliance</span>
            <h1 class="hero-title">Strengthen governance & controls with OGMBC.</h1>
            <p class="text-light">Risk assessments, SOPs, internal audits, and tax compliance tailored to your sector.</p>
            <div class="d-flex gap-3 flex-wrap">
              <a class="btn btn-primary" href="ratios.php">Ratio Calculator</a>
              <a class="btn btn-ghost" href="contact.php">Talk to an advisor</a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
              <!-- Global presence row -->
              <div class="global-presence d-flex align-items-center justify-content-center py-2">
                <span class="fw-bold" style="color:#d0aa4b; font-size:1.1rem;">Our global presence: UAE, UK, USA</span>
              </div>
              <div class="d-flex flex-column gap-3 contact-info">
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                  <span><a href="tel:+971502923136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 292 3136</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                  <span><a href="mailto:info@ogmbc.ae" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">info@ogmbc.ae</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-geo-alt-fill"></i></div>
                  <span>Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai UAE. P.O. Box 33418</span>
                </div>
              </div>
              <a href="contact.php" class="btn btn-secondary mt-3 text-white">
                Request Proposal
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="carousel-item" data-bs-interval="3000">
      <div class="container">
        <div class="row align-items-center gy-4">
          <div class="col-lg-7 mb-5">
            <span class="chip">Global Business Formation</span>
            <h1 class="hero-title">Clear, compliant, and timely IFRS reporting.</h1>
            <p class="text-light">We make business setup simple, compliant, and stress-free so you can focus on growth.</p>
            <div class="d-flex gap-3 flex-wrap">
              <a class="btn btn-primary" href="ratios.php">Ratio Calculator</a>
              <a class="btn btn-ghost" href="ifrs.php">Get IFRS support</a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
              <!-- Global presence row -->
              <div class="global-presence d-flex align-items-center justify-content-center py-2">
                <span class="fw-bold" style="color:#d0aa4b; font-size:1.1rem;">Our global presence: UAE, UK, USA</span>
              </div>
              
              <div class="d-flex flex-column gap-3 contact-info">
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                  <span><a href="tel:+971502923136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 292 3136</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                  <span><a href="mailto:info@ogmbc.ae" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">info@ogmbc.ae</a></span>
                </div>
                <div class="info-box d-flex align-items-center gap-2">
                  <div class="icon-circle"><i class="bi bi-geo-alt-fill"></i></div>
                  <span>Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai UAE. P.O. Box 33418</span>
                </div>
              </div>
              <a href="contact.php" class="btn btn-secondary mt-3 text-white">
                Request Proposal
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Controls - Updated with higher z-index and wider positioning -->
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
  </div>
  </section>

  <!-- Services -->
  <section id="services" class="section">
    <div class="container">
      <h2>Services</h2>
      <div class="d-flex justify-content-center">
        <p class="lead text-center">
          Whether it’s auditing, accounting, taxation, company setup, or strategic advisory, we customize our approach to fit your business vision, governance needs, and growth plans.
        </p>
      </div> 
      <div class="row g-4 services">
        <!-- Auditing & Compliance -->
        <div class="col-lg-4 col-md-6 justify-content-center">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-shield-check"></i>
              </div>
              <h3>Auditing & Compliance</h3>
              <p>Beyond audits - empowering governance and control</p>
              <a href="audit-&-audit-support.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>

        <!-- Accounting & CFO -->
        <div class="col-lg-4 col-md-6">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-graph-up"></i>
              </div>
              <h3>Accounting & CFO</h3>
              <p>Excellence in numbers, confidence in growth</p>
              <a href="financial-statement-reporting.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>

        <!-- Taxation -->
        <div class="col-lg-4 col-md-6">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-calculator"></i>
              </div>
              <h3>Taxation</h3>
              <p>From registration to reporting — we've got you covered</p>
              <a href="tax-consultancy.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>

        <!-- IFRS Advisory -->
        <div class="col-lg-4 col-md-6">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-file-earmark-bar-graph"></i>
              </div>
              <h3>IFRS Advisory</h3>
              <p>Your IFRS partner for precise financial storytelling</p>
              <a href="ifrs-advisory.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>

        <!-- Company Setup -->
        <div class="col-lg-4 col-md-6">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-building"></i>
              </div>
              <h3>Company Setup</h3>
              <p>From trade license to bank account — we handle every step so you can focus on growth</p>
              <a href="uae-bussiness-formation.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>

        <!-- Management Advisory -->
        <div class="col-lg-4 col-md-6">
          <article class="service card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
              <div class="service-icon">
                <i class="bi bi-person-gear"></i>
              </div>
              <h3>Management Advisory</h3>
              <p>Our management advisors help you streamline operations, boost performance, and make better business decisions</p>
              <a href="management-accounting-&-kpi.php" class="btn btn-outline-primary mt-3">Learn More</a>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>

  <!-- Why Choose US -->
  <section class="why-choose-us py-5" style="background:#fff;">
    <div class="container text-center">
      <h2 class="mb-4" style="color:#d0aa4b;">Why Choose Us</h2>
      <p class="lead mb-5">
        We are committed to making a positive impact on our clients' businesses and helping them 
        navigate the complexities of today's business environment with confidence and success.
      </p>
      <div class="row g-4 justify-content-center">
        <!-- Feature 1 -->
        <div class="col-md-4">
          <div class="feature-modern d-flex flex-column align-items-center">
            <span class="feature-icon-modern mb-3">
              <i class="bi bi-graph-up-arrow"></i>
            </span>
            <h4 class="mb-2" style="color:#091e3e;">Compliance Clarity</h4>
            <p style="color:#747576;">
              From Auditing, Accounting, Taxation, and IFRS Reporting, we deliver 
              compliance clarity with insights that strengthen transparency and drive confident decision-making.
            </p>
          </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-md-4">
          <div class="feature-modern d-flex flex-column align-items-center">
            <span class="feature-icon-modern mb-3">
              <i class="bi bi-people"></i>
            </span>
            <h4 class="mb-2" style="color:#313131;">Client-Centered Approach</h4>
            <p style="color:#747576;">
              We partner with you, offering personalized advisory, outsourcing, 
              and training solutions to fit your growth needs.
            </p>
          </div>
        </div>

        <!-- Feature 3 -->
        <div class="col-md-4">
          <div class="feature-modern d-flex flex-column align-items-center">
            <span class="feature-icon-modern mb-3">
              <i class="bi bi-shield-check"></i>
            </span>
            <h4 class="mb-2" style="color:#313131;">Compliance & Assurance</h4>
            <p style="color:#747576;">
              Our experienced team ensures adherence to international standards, 
              regulatory frameworks, and tax obligations.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats -->
  <section id="stats" class="section stats-modern">
    <div class="container">
      <div class="stats-modern-bg p-5 rounded-5 shadow-lg d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
        <div class="stats-intro text-center text-md-start flex-grow-1">
          <h2 class="fw-bold mb-3" style="color:#313131;">Empowering Your Financial Growth</h2>
          <p class="mb-0" style="color:#747576;">
            We deliver clarity, compliance, and confidence for ambitious businesses. 
            Our expertise in audit, reporting, and advisory unlocks your next level.
          </p>
        </div>
        <div class="stats-numbers row row-cols-2 g-4 flex-grow-1 justify-content-center">
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="num display-5 fw-bold" data-target="1890">0</div>
            <div class="label" style="color:#747576;">Projects Delivered</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-clock-history"></i></div>
            <div class="num display-5 fw-bold" data-target="98">0</div>
            <div class="label" style="color:#747576;">On-Time Completion (%)</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-building"></i></div>
            <div class="num display-5 fw-bold" data-target="38">0</div>
            <div class="label" style="color:#747576;">Industries Served</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-star-fill"></i></div>
            <div class="num display-5 fw-bold" data-target="95">0</div>
            <div class="label" style="color:#747576;">Client Satisfaction (%)</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials 
  <section class="section testimonials-section" style="padding: 0;">
    <div class="testimonials-bg">
      <div class="container py-5">
        <h2 class="mb-5" style="color: #091e3e; font-weight: 700;">What Clients Say</h2>
        <div class="row g-4 justify-content-center">
          <!-- Testimonial 1 
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="testimonial-quote">
                <i class="bi bi-quote" style="font-size:4rem; color:#d0aa4b;"></i>
              </div>
              <p class="testimonial-text">
                OGBC’s team delivered clear, actionable audit insights that helped us streamline our processes.
              </p>
              <div class="testimonial-avatar mx-auto mb-3">
                <img src="assets/img/testimonial1.jpg" alt="Client 1" />
              </div>
              <div class="testimonial-author fw-bold">Sasha Grey</div>
              <div class="testimonial-role">Business Owner</div>
            </div>
          </div>
          <!-- Testimonial 2 
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="testimonial-quote">
                <i class="bi bi-quote" style="font-size:4rem; color:#d0aa4b;"></i>
              </div>
              <p class="testimonial-text">
                Their financial reporting expertise gave us confidence in our numbers and compliance.
              </p>
              <div class="testimonial-avatar mx-auto mb-3">
                <img src="assets/img/testimonial2.jpg" alt="Client 2" />
              </div>
              <div class="testimonial-author fw-bold">Nat Reynolds</div>
              <div class="testimonial-role">Chief Accountant</div>
            </div>
          </div>
          <!-- Testimonial 3 
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="testimonial-quote">
                <i class="bi bi-quote" style="font-size:4rem; color:#d0aa4b;"></i>
              </div>
              <p class="testimonial-text">
                OGBC’s advisory and training helped our team grow and adapt to new standards.
              </p>
              <div class="testimonial-avatar mx-auto mb-3">
                <img src="assets/img/testimonial3.jpg" alt="Client 3" />
              </div>
              <div class="testimonial-author fw-bold">Bob Roberts</div>
              <div class="testimonial-role">Sales Manager</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section -->

  <!-- Blog Section Posts Fetching Query -->
  <?php
    // Fetch latest published posts from database
    $sql = "SELECT p.*, u.first_name, u.last_name 
            FROM posts p 
            LEFT JOIN users u ON p.post_author = u.user_id 
            WHERE p.post_status = 'published'
            ORDER BY p.created_at DESC 
            LIMIT 3"; // Limit to 3 posts for the section

    $result = $connection->query($sql);
  ?>

  <section id="blog" class="section blog-section bg-light">
    <div class="container">
      <!-- Heading -->
      <div class="text-center mb-5">
        <h2 class="section-title">Latest Insights</h2>
        <div class="d-flex justify-content-center">
          <p class="lead text-center">
            Stay updated with expert commentary on audits, IFRS, taxation, and governance trends.
          </p>
        </div>
      </div>

      <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($post = $result->fetch_assoc()): ?>
            <!-- Blog Post -->
            <div class="col-md-6 col-lg-4">
              <article class="blog-card card h-100">
                <!-- Post Image -->
                <?php
                $image_path = "uploads/posts/" . $post['post_image'];
                $image_url = (!empty($post['post_image']) && file_exists($image_path)) 
                    ? $image_path 
                    : "https://via.placeholder.com/600x400/f1bf70/0f172a?text=Blog+Post";
                ?>
                <img src="<?php echo $image_url; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['post_title']); ?>">
                
                <div class="card-body">
                  <!-- Category -->
                  <?php if (!empty($post['post_category'])): ?>
                    <span class="chip"><?php echo htmlspecialchars($post['post_category']); ?></span>
                  <?php endif; ?>
                  
                  <!-- Title -->
                  <h3 class="blog-title"><?php echo htmlspecialchars($post['post_title']); ?></h3>
                  
                  <!-- Excerpt -->
                  <p class="blog-excerpt">
                    <?php 
                    $excerpt = !empty($post['post_excerpt']) 
                        ? $post['post_excerpt'] 
                        : strip_tags($post['post_content']);
                    echo htmlspecialchars(substr($excerpt, 0, 120)) . '...'; 
                    ?>
                  </p>
                  
                  <!-- Read More Link -->
                  <a href="post_detail.php?slug=<?php echo urlencode($post['post_slug']); ?>" class="read-more">Read More →</a>
                </div>
              </article>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <!-- Fallback content if no posts found -->
          <div class="col-12 text-center py-5">
            <div class="alert alert-info">
              <i class="bi bi-info-circle me-2"></i> No blog posts available at the moment. Please check back later.
            </div>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- View All Blog Posts Button -->
      <div class="text-center mt-5">
        <a href="blog.php" class="btn btn-primary">View All Blog Posts</a>
      </div>
    </div>
  </section>

  <?php
    // Close result set
    if ($result) {
        $result->close();
    }
  ?>

  <!-- CTA Section -->
  <section class="cta-section py-5">
    <div class="container">
      <div class="row align-items-stretch g-0">
        <!-- Left: Info -->
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
              <a href="tel:+971502923136" class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971 50 292 3136</a>
            </div>
          </div>
        </div>
        <!-- Right: Form -->
        <div class="col-lg-5 bg-cta-gold p-5 d-flex align-items-center">
          <form action="send_mail.php" method="POST" class="w-100">
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
                <input type="text" name="subject" class="form-control cta-input" placeholder="Subject" required>
            </div>
            <div class="mb-3">
                <textarea name="message" class="form-control cta-input" rows="3" placeholder="Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2 fw-bold" style="background:#091e3e;">Request A Quote</button>
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

<!-- Floating Button (Should be outside the chat widget) -->
<div id="omni-floating-btn" class="omni-floating-btn">
    <div class="omni-pulse"></div>
    <div class="omni-avatar">
        <img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />
    </div>
</div>

<!-- omni Chat Widget -->
<div id="omni-chat-widget" class="omni-chat-widget omni-hidden">
    <!-- Chat Header -->
    <div class="omni-header">
        <div class="omni-header-content">
            <div class="omni-avatar-sm">
                <img src="resources/img/omni.svg" alt="omni Assistant" width="25" height="25" />
            </div>
            <div class="omni-header-text">
                <h6 class="omni-title">OmniOGM Assistant</h6>
                <span id="omni-status" class="omni-status">Online</span>
            </div>
        </div>
        <div class="omni-header-actions">
            <button id="omni-download-btn" class="omni-action-btn" title="Download conversation">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
            </button>
            <button id="omni-maximize-btn" class="omni-action-btn" title="Maximize window">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                </svg>
            </button>
            <button id="omni-close" class="omni-close-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Chat Messages Container -->
    <div id="omni-messages" class="omni-messages">
        <div class="omni-welcome-message">
            <div class="omni-welcome-avatar">
                <img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />
            </div>
            <div class="omni-welcome-text">
                <h4>Hello! I'm OmniOGM 👋</h4>
                <p>OGMBC AI Assistant. I'm here to help you learn about OGMBC's services and answer any questions you might have.</p>
            </div>
        </div>
    </div>

    <!-- Dynamic Prompts -->
    <div id="omni-prompts" class="omni-prompts">
        <div class="omni-prompts-header">
            <div class="omni-prompts-title-container">
                <button id="omni-toggle-prompts" class="omni-toggle-btn" title="Toggle quick questions">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <span class="omni-prompts-title">Quick Questions</span>
                <span id="omni-collapse-indicator" class="omni-collapse-indicator">(click to expand)</span>
            </div>
            <div class="omni-prompts-actions">
                <button id="omni-refresh-prompts" class="omni-refresh-btn" title="Refresh suggestions">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6M1 20v-6h6"/>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </button>
            </div>
        </div>
        <div id="omni-prompts-container" class="omni-prompts-container omni-prompts-expanded">
            <!-- Dynamic prompts will be inserted here -->
        </div>
    </div>

    <!-- Chat Input Area -->
    <div class="omni-input-area">
        <div class="omni-input-container">
            <textarea id="omni-user-input" class="omni-input" placeholder="Message omni..." maxlength="500" rows="1"></textarea>
            <button id="omni-send-btn" class="omni-send-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                </svg>
            </button>
        </div>
        <div class="omni-input-footer">
            <span class="omni-char-count">0/500</span>
            <span class="omni-powered-by">Powered by OmniOGM</span>
        </div>
    </div>
</div>



<?php
include 'includes/footer.php'
?>



<script>
class omniChat {
    constructor() {
        this.conversationHistory = [];
        this.usedPrompts = new Set();
        this.promptsCollapsed = false;
        this.isTypingResponse = false;
        this.isMaximized = false;

        this.availablePrompts = [
            "What business setup services do you offer?",
            "Tell me about UAE company formation",
            "How can I set up a company in the USA?",
            "What accounting services do you provide?",
            "Do you offer tax consultancy services?",
            "Can you help with bank account opening?",
            "Do you offer Golden Visa assistance?",
            "What audit services do you provide?",
            "What industries do you serve?",
            "How can I contact OGMBC?"
        ];

        this.initElements();
        this.bindEvents();
        this.renderPrompts();
        this.createDownloadOverlay();
        this.injectClearButton(); // Fixed: Add clear button
    }

    /* ---------- INIT ---------- */

    initElements() {
        this.widget = document.getElementById('omni-chat-widget');
        this.messages = document.getElementById('omni-messages');
        this.input = document.getElementById('omni-user-input');
        this.sendBtn = document.getElementById('omni-send-btn');
        this.floatBtn = document.getElementById('omni-floating-btn');
        this.closeBtn = document.getElementById('omni-close');
        this.status = document.getElementById('omni-status');

        this.promptsBox = document.getElementById('omni-prompts-container');
        this.togglePromptsBtn = document.getElementById('omni-toggle-prompts');
        this.refreshPromptsBtn = document.getElementById('omni-refresh-prompts');
        this.collapseIndicator = document.getElementById('omni-collapse-indicator');

        this.maximizeBtn = document.getElementById('omni-maximize-btn');
        this.downloadBtn = document.getElementById('omni-download-btn');
        this.charCount = document.querySelector('.omni-char-count');
    }

    bindEvents() {
        this.floatBtn.onclick = () => this.toggleChat();
        this.closeBtn.onclick = () => this.hideChat();
        this.sendBtn.onclick = () => this.sendMessage();

        this.input.oninput = () => {
            this.charCount.textContent = `${this.input.value.length}/500`;
            this.input.style.height = 'auto';
            this.input.style.height = Math.min(this.input.scrollHeight, 120) + 'px';
        };

        this.input.onkeydown = e => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        };

        this.togglePromptsBtn.onclick = () => this.togglePrompts();
        this.refreshPromptsBtn.onclick = () => this.renderPrompts(true);
        this.maximizeBtn.onclick = () => this.toggleMaximize();
        this.downloadBtn.onclick = () => this.showDownloadModal();
    }

    /* ---------- CHAT ---------- */

    toggleChat() {
        this.widget.classList.toggle('omni-hidden');
        this.status.textContent = this.widget.classList.contains('omni-hidden') ? 'Offline' : 'Online';
    }

    hideChat() {
        this.widget.classList.add('omni-hidden');
        this.status.textContent = 'Offline';
    }

    async sendMessage(text = null) {
        const message = text || this.input.value.trim();
        if (!message) return;

        this.addMessage(message, true);
        this.conversationHistory.push({ role: 'user', content: message });

        this.input.value = '';
        this.input.style.height = 'auto';
        this.charCount.textContent = '0/500';

        this.showThinking();

        try {
            const res = await fetch('chat_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message,
                    history: this.conversationHistory
                })
            });

            const data = await res.json();
            this.removeThinking();

            const clean = this.cleanResponse(data.reply);
            const el = this.addMessage('', false);
            this.typeWriter(el, clean);
            this.conversationHistory.push({ role: 'assistant', content: clean });

        } catch {
            this.removeThinking();
            this.addMessage('Connection error. Please try again.', false);
        }
    }

    /* ---------- RESPONSE CLEANUP ---------- */

    cleanResponse(text) {
        // Remove any truncation markers but keep the full response
        let cleaned = text
            .replace(/\.\.\.\s*\(truncated\)/gi, '')
            .replace(/\(truncated\)/gi, '')
            .trim();

        // Remove any "..." at the end if it was added by truncation
        if (cleaned.endsWith('...')) {
            cleaned = cleaned.substring(0, cleaned.length - 3).trim();
        }

        return cleaned;
    }

    /* ---------- MESSAGES ---------- */

    addMessage(content, isUser) {
        const msg = document.createElement('div');
        msg.className = `omni-message ${isUser ? 'omni-message-user' : 'omni-message-bot'}`;

        const avatar = document.createElement('div');
        avatar.className = 'omni-message-avatar';
        avatar.innerHTML = isUser ? 'You' : `<img src="resources/img/omni.svg" width="32">`;

        const body = document.createElement('div');
        body.className = 'omni-message-content';
        if (isUser) body.textContent = content;

        msg.appendChild(avatar);
        msg.appendChild(body);
        this.messages.appendChild(msg);
        this.scrollBottom();

        return body;
    }

    typeWriter(el, text) {
        el.innerHTML = '';
        let i = 0;
        const formatted = this.formatText(text);

        const step = () => {
            if (i <= formatted.length) {
                el.innerHTML = formatted.slice(0, i++);
                this.scrollBottom();
                requestAnimationFrame(step);
            }
        };
        step();
    }

    formatText(t) {
        // Process text with proper spacing
        let formatted = t
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/(\n|^)- (.*?)(?=\n|$)/gm, '<li>$2</li>')
            .replace(/(\n|^)(\d+)\. (.*?)(?=\n|$)/gm, '<li>$3</li>')
            .replace(/\n/g, '<br>');
        
        // Wrap consecutive list items in ul
        formatted = formatted.replace(/(<li>.*?<\/li>(<br>)?)+/g, (match) => {
            return '<ul class="omni-list">' + match.replace(/<br>/g, '') + '</ul>';
        });
        
        return formatted;
    }

    /* ---------- THINKING ---------- */

    showThinking() {
        this.thinking = document.createElement('div');
        this.thinking.className = 'omni-thinking';
        this.thinking.innerHTML = `
            <div class="omni-message-avatar"><img src="resources/img/omni.svg" width="32"></div>
            <div class="omni-thinking-text">Thinking...</div>`;
        this.messages.appendChild(this.thinking);
        this.scrollBottom();
    }

    removeThinking() {
        if (this.thinking) this.thinking.remove();
    }

    scrollBottom() {
        this.messages.scrollTop = this.messages.scrollHeight;
    }

    /* ---------- PROMPTS (3 ONLY) ---------- */

    renderPrompts(shuffle = false) {
        this.promptsBox.innerHTML = '';
        let prompts = [...this.availablePrompts];
        if (shuffle) prompts.sort(() => Math.random() - 0.5);

        // Filter out used prompts first
        const available = prompts.filter(p => !this.usedPrompts.has(p));
        
        // Take up to 3 available prompts
        const displayPrompts = available.slice(0, 3);
        
        // If we don't have enough available, add some used ones back
        if (displayPrompts.length < 3) {
            const used = prompts.filter(p => this.usedPrompts.has(p));
            displayPrompts.push(...used.slice(0, 3 - displayPrompts.length));
        }

        displayPrompts.forEach(p => {
            const btn = document.createElement('button');
            btn.className = 'omni-prompt-btn';
            btn.textContent = p;

            if (this.usedPrompts.has(p)) {
                btn.classList.add('omni-prompt-used');
                btn.disabled = true;
            }

            btn.onclick = () => {
                this.usedPrompts.add(p);
                this.sendMessage(p);
                btn.classList.add('omni-prompt-used');
                btn.disabled = true;
            };

            this.promptsBox.appendChild(btn);
        });
    }

    togglePrompts() {
        this.promptsCollapsed = !this.promptsCollapsed;
        this.promptsBox.classList.toggle('omni-prompts-collapsed', this.promptsCollapsed);
        this.promptsBox.classList.toggle('omni-prompts-expanded', !this.promptsCollapsed);
        this.togglePromptsBtn.classList.toggle('collapsed', this.promptsCollapsed);
        this.collapseIndicator.textContent =
            this.promptsCollapsed ? '(click to expand)' : '(click to collapse)';
    }

    /* ---------- MAXIMIZE ---------- */

    toggleMaximize() {
        this.isMaximized = !this.isMaximized;
        this.widget.classList.toggle('omni-maximized', this.isMaximized);
    }

    /* ---------- CLEAR CHAT ---------- */

    injectClearButton() {
        // Check if button already exists
        if (document.querySelector('.omni-clear-btn')) return;
        
        const btn = document.createElement('button');
        btn.className = 'omni-action-btn omni-clear-btn';
        btn.title = 'Clear chat';
        btn.innerHTML = '🗑';
        btn.onclick = () => this.clearChat();
        
        // Insert before maximize button
        const headerActions = this.downloadBtn.parentNode;
        headerActions.insertBefore(btn, this.maximizeBtn);
    }

    clearChat() {
        // Store welcome message HTML
        const welcomeMsg = this.messages.querySelector('.omni-welcome-message');
        
        // Clear all messages
        this.messages.innerHTML = '';
        
        // Add welcome message back
        if (welcomeMsg) {
            this.messages.appendChild(welcomeMsg);
        }
        
        // Reset conversation history
        this.conversationHistory = [];
        this.usedPrompts.clear();
        
        // Refresh prompts
        this.renderPrompts(true);
        this.scrollBottom();
    }

    /* ---------- DOWNLOAD (PDF) ---------- */

    createDownloadOverlay() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'omni-download-overlay';
        this.overlay.innerHTML = `
            <div class="omni-download-modal">
                <h3 class="omni-download-title">Download Chat</h3>
                <div class="omni-download-actions">
                    <button class="omni-cancel-btn">Cancel</button>
                    <button class="omni-download-btn">Download PDF</button>
                </div>
            </div>`;
        document.body.appendChild(this.overlay);

        this.overlay.querySelector('.omni-cancel-btn').onclick = () =>
            this.overlay.classList.remove('active');

        this.overlay.querySelector('.omni-download-btn').onclick = () =>
            this.downloadPDF();
    }

    showDownloadModal() {
        // Check if there's content to download
        if (this.conversationHistory.length === 0) {
            alert('No conversation to download.');
            return;
        }
        this.overlay.classList.add('active');
    }

    async downloadPDF() {
        // Dynamically load jsPDF if not available
        if (!window.jspdf || !window.jspdf.jsPDF) {
            try {
                // Try to load jsPDF from CDN
                await this.loadJSPDF();
            } catch (error) {
                console.error('Failed to load PDF library:', error);
                alert('PDF library not loaded. Please check internet connection.');
                this.overlay.classList.remove('active');
                return;
            }
        }

        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            let y = 15;

            // Title
            doc.setFontSize(16);
            doc.setTextColor(33, 33, 33);
            doc.text('OGM Business Consultants - Chat Conversation', 105, y, { align: 'center' });
            y += 10;

            // Date
            doc.setFontSize(10);
            doc.setTextColor(100, 100, 100);
            const now = new Date();
            const dateStr = now.toLocaleString();
            doc.text(`Generated: ${dateStr}`, 105, y, { align: 'center' });
            y += 15;

            // Conversation
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);

            this.conversationHistory.forEach((m, index) => {
                // Check for page break
                if (y > 270) {
                    doc.addPage();
                    y = 15;
                }

                // Role header
                doc.setFontSize(11);
                doc.setFont(undefined, 'bold');
                doc.text(`${m.role === 'user' ? 'You' : 'OmniOGM'}:`, 10, y);
                y += 7;

                // Content
                doc.setFontSize(10);
                doc.setFont(undefined, 'normal');
                
                // Clean content for PDF
                const cleanContent = m.content
                    .replace(/\*\*/g, '') // Remove bold markers
                    .replace(/<br>/g, '\n')
                    .replace(/<[^>]*>/g, ''); // Remove any HTML tags
                
                const lines = doc.splitTextToSize(cleanContent, 180);
                doc.text(lines, 15, y);
                y += lines.length * 5 + 8;
            });

            // Footer
            doc.setFontSize(8);
            doc.setTextColor(150, 150, 150);
            doc.text('Powered by OGM Business Consultants - www.ogmbc.ae', 105, 285, { align: 'center' });

            // Save
            const fileName = `OGMBC_Chat_${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}.pdf`;
            doc.save(fileName);

            this.overlay.classList.remove('active');
        } catch (error) {
            console.error('PDF generation error:', error);
            alert('Error generating PDF. Please try again.');
            this.overlay.classList.remove('active');
        }
    }

    loadJSPDF() {
        return new Promise((resolve, reject) => {
            // Check if already loaded
            if (window.jspdf && window.jspdf.jsPDF) {
                resolve();
                return;
            }

            // Load jsPDF from CDN
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script.onload = () => {
                if (window.jspdf && window.jspdf.jsPDF) {
                    resolve();
                } else {
                    reject(new Error('jsPDF not loaded properly'));
                }
            };
            script.onerror = () => reject(new Error('Failed to load jsPDF'));
            document.head.appendChild(script);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.omniChat = new omniChat();
});
</script>




