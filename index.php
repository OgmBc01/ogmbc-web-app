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
// omni Chat Widget JavaScript
class omniChat {
    constructor() {
        this.conversationHistory = [];
        this.usedPrompts = new Set();
        this.promptsCollapsed = false;
        this.isThinking = false;
        this.isTypingResponse = false;
        this.currentTypingInterval = null;
        this.isMaximized = false;
        this.originalPosition = null;
        
        this.availablePrompts = [
            "What business setup services do you offer?",
            "Tell me about UAE company formation",
            "How can I set up a company in USA?",
            "What accounting services do you provide?",
            "Do you offer tax consultancy services?",
            "What is your corporate tax expertise?",
            "Can you help with bank account opening?",
            "What audit services do you provide?",
            "Tell me about your IFRS advisory services",
            "Do you offer Golden Visa assistance?",
            "What are your office locations?",
            "How long does company formation take?",
            "What documents are needed for UAE setup?",
            "Do you provide ongoing compliance support?",
            "What industries do you serve?",
            "Can you help with business valuation?",
            "What is transfer pricing?",
            "Do you offer supply chain consulting?",
            "What corporate governance services do you provide?",
            "How can I contact OGMBC?",
            "What makes OGMBC different from others?",
            "Do you work with startups?",
            "What are your fees for company formation?",
            "Can you help with annual license renewal?",
            "What AML support do you provide?",
            "Do you offer bookkeeping services?",
            "What accounting software do you support?",
            "Can you help with due diligence?",
            "What internal control services do you offer?",
            "Do you provide management accounting?"
        ];
        
        this.initializeElements();
        this.bindEvents();
        this.setupAutoResize();
        this.generatePrompts();
        this.updateStatus();
        this.createDownloadOverlay();
    }

    initializeElements() {
        this.floatingBtn = document.getElementById('omni-floating-btn');
        this.chatWidget = document.getElementById('omni-chat-widget');
        this.messagesContainer = document.getElementById('omni-messages');
        this.userInput = document.getElementById('omni-user-input');
        this.sendBtn = document.getElementById('omni-send-btn');
        this.closeBtn = document.getElementById('omni-close');
        this.promptsContainer = document.getElementById('omni-prompts-container');
        this.promptsSection = document.getElementById('omni-prompts');
        this.refreshPromptsBtn = document.getElementById('omni-refresh-prompts');
        this.togglePromptsBtn = document.getElementById('omni-toggle-prompts');
        this.collapseIndicator = document.getElementById('omni-collapse-indicator');
        this.charCount = document.querySelector('.omni-char-count');
        this.statusElement = document.getElementById('omni-status');
        this.maximizeBtn = document.getElementById('omni-maximize-btn');
        this.downloadBtn = document.getElementById('omni-download-btn');
    }

    bindEvents() {
        this.floatingBtn.addEventListener('click', () => this.toggleChat());
        this.closeBtn.addEventListener('click', () => this.hideChat());
        this.sendBtn.addEventListener('click', () => this.sendMessage());
        
        this.userInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        this.userInput.addEventListener('input', () => {
            this.updateCharCount();
            this.autoResizeTextarea();
        });
        
        this.refreshPromptsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.generatePrompts();
        });
        
        this.togglePromptsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.togglePrompts();
        });
        
        this.maximizeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleMaximize();
        });
        
        this.downloadBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.showDownloadModal();
        });
        
        document.addEventListener('click', (e) => {
            if (!this.chatWidget.contains(e.target) && !this.floatingBtn.contains(e.target)) {
                this.hideChat();
            }
        });
        
        this.promptsSection.addEventListener('click', (e) => {
            if (e.target.closest('.omni-prompts-header') && !e.target.closest('.omni-prompts-actions')) {
                this.togglePrompts();
            }
        });
    }

    createDownloadOverlay() {
        this.downloadOverlay = document.createElement('div');
        this.downloadOverlay.className = 'omni-download-overlay';
        this.downloadOverlay.innerHTML = `
            <div class="omni-download-modal">
                <h3 class="omni-download-title">Download Conversation</h3>
                <input type="text" class="omni-download-input" value="Omni-OGM_Conversation_${this.getFormattedDate()}" placeholder="Enter file name">
                <div class="omni-download-actions">
                    <button class="omni-cancel-btn">Cancel</button>
                    <button class="omni-download-btn">Download PDF</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(this.downloadOverlay);
        
        this.downloadOverlay.querySelector('.omni-cancel-btn').addEventListener('click', () => {
            this.hideDownloadModal();
        });
        
        this.downloadOverlay.querySelector('.omni-download-btn').addEventListener('click', () => {
            this.downloadPDF();
        });
        
        this.downloadOverlay.addEventListener('click', (e) => {
            if (e.target === this.downloadOverlay) {
                this.hideDownloadModal();
            }
        });
    }

    getFormattedDate() {
        const now = new Date();
        const date = now.getDate().toString().padStart(2, '0');
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const year = now.getFullYear();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return `${date}-${month}-${year}_${hours}-${minutes}`;
    }

    showDownloadModal() {
        this.downloadOverlay.classList.add('active');
        const input = this.downloadOverlay.querySelector('.omni-download-input');
        setTimeout(() => {
            input.focus();
            input.select();
        }, 100);
    }

    hideDownloadModal() {
        this.downloadOverlay.classList.remove('active');
    }

    async downloadPDF() {
        const filename = this.downloadOverlay.querySelector('.omni-download-input').value || `Omni-OGM_Conversation_${this.getFormattedDate()}`;
        
        // Get all messages from the conversation history
        let htmlContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>${filename}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 40px; 
                        color: #333; 
                        line-height: 1.6;
                    }
                    .header { 
                        text-align: center; 
                        margin-bottom: 30px; 
                        border-bottom: 2px solid #0d274d; 
                        padding-bottom: 10px;
                    }
                    .logo { 
                        color: #0d274d; 
                        font-size: 24px; 
                        font-weight: bold; 
                        margin-bottom: 5px;
                    }
                    .subtitle { 
                        color: #666; 
                        font-size: 14px; 
                        margin-bottom: 20px;
                    }
                    .meta { 
                        font-size: 12px; 
                        color: #888; 
                        text-align: center; 
                        margin-bottom: 30px;
                    }
                    .message { 
                        margin-bottom: 15px; 
                        padding: 10px; 
                        border-radius: 8px; 
                        display: flex;
                    }
                    .user-message { 
                        background: #f5f5f5; 
                        border-left: 4px solid #ffd260; 
                        margin-left: 20%;
                    }
                    .bot-message { 
                        background: #eef5ff; 
                        border-left: 4px solid #0d274d; 
                        margin-right: 20%;
                    }
                    .avatar { 
                        width: 30px; 
                        height: 30px; 
                        border-radius: 50%; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        font-size: 12px; 
                        font-weight: bold; 
                        margin-right: 10px; 
                        flex-shrink: 0;
                    }
                    .user-avatar { 
                        background: #ffd260; 
                        color: #333; 
                    }
                    .bot-avatar { 
                        background: #0d274d; 
                        color: white; 
                    }
                    .content { 
                        flex: 1;
                        font-size: 14px;
                    }
                    .time { 
                        font-size: 11px; 
                        color: #888; 
                        margin-top: 5px; 
                        text-align: right;
                    }
                    h2, h3, h4 { 
                        color: #0d274d; 
                        margin-top: 15px; 
                        margin-bottom: 8px;
                        font-weight: 600;
                    }
                    h2 { font-size: 18px; }
                    h3 { font-size: 16px; }
                    h4 { font-size: 14px; }
                    ul, ol { 
                        margin: 8px 0 8px 20px; 
                        padding-left: 0;
                    }
                    li { 
                        margin: 4px 0;
                        line-height: 1.4;
                    }
                    ul { list-style-type: disc; }
                    ol { list-style-type: decimal; }
                    strong { font-weight: bold; }
                    em { font-style: italic; }
                    .footer { 
                        margin-top: 40px; 
                        padding-top: 20px; 
                        border-top: 1px solid #ddd; 
                        font-size: 12px; 
                        color: #666; 
                        text-align: center;
                    }
                    .separator {
                        height: 1px;
                        background: #eee;
                        margin: 10px 0;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="logo">OmniOGM Assistant</div>
                    <div class="subtitle">OGM Business Consultants - Conversation History</div>
                    <div class="meta">Downloaded on: ${new Date().toLocaleString()}</div>
                </div>
                <div class="conversation">
        `;
        
        // Include welcome message
        htmlContent += `
            <div class="message bot-message">
                <div class="avatar bot-avatar">AI</div>
                <div class="content">
                    <strong>Hello! I'm OmniOGM 👋</strong><br>
                    OGMBC AI Assistant. I'm here to help you learn about OGMBC's services and answer any questions you might have.
                </div>
            </div>
        `;
        
        // Add all conversation messages
        this.conversationHistory.forEach((msg, index) => {
            const isUser = msg.role === 'user';
            const content = msg.content;
            
            htmlContent += `
                <div class="message ${isUser ? 'user-message' : 'bot-message'}">
                    <div class="avatar ${isUser ? 'user-avatar' : 'bot-avatar'}">
                        ${isUser ? 'You' : 'AI'}
                    </div>
                    <div class="content">
                        ${this.formatContentForPDF(content)}
                        <div class="time">Message ${index + 1}</div>
                    </div>
                </div>
            `;
        });
        
        htmlContent += `
                </div>
                <div class="footer">
                    © ${new Date().getFullYear()} OGM Business Consultants. All rights reserved.<br>
                    This conversation was generated by the OmniOGM Assistant.
                </div>
            </body>
            </html>
        `;
        
        try {
            if (typeof html2pdf !== 'undefined') {
                const options = {
                    margin: [10, 10, 10, 10],
                    filename: `${filename}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 2, 
                        useCORS: true,
                        logging: true
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a4', 
                        orientation: 'portrait' 
                    }
                };
                
                console.log('Generating PDF...');
                const pdf = await html2pdf().set(options).from(htmlContent).save();
                this.hideDownloadModal();
            } else {
                console.log('html2pdf not found, using print method');
                const printWindow = window.open('', '_blank');
                printWindow.document.write(htmlContent);
                printWindow.document.close();
                printWindow.print();
                this.hideDownloadModal();
            }
        } catch (error) {
            console.error('PDF generation error:', error);
            alert('Failed to generate PDF. Please try printing the page instead.');
        }
    }

    formatContentForPDF(text) {
        if (!text) return '';
        
        // Apply basic formatting
        let result = text;
        
        // Convert markdown-like formatting
        result = result.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        result = result.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Convert line breaks to <br>
        result = result.replace(/\n/g, '<br>');
        
        // Process numbered lists
        result = result.replace(/(\d+)\.\s+(.+?)(?=(\d+\.|$))/g, function(match, num, content) {
            return '<br>' + num + '. ' + content.trim();
        });
        
        // Process bulleted lists
        result = result.replace(/([•\-*])\s+(.+?)(?=([•\-*]|$))/g, function(match, bullet, content) {
            return '<br>' + bullet + ' ' + content.trim();
        });
        
        return result;
    }

    toggleMaximize() {
        this.isMaximized = !this.isMaximized;
        
        if (this.isMaximized) {
            if (!this.originalPosition) {
                const rect = this.chatWidget.getBoundingClientRect();
                this.originalPosition = {
                    bottom: this.chatWidget.style.bottom,
                    right: this.chatWidget.style.right,
                    width: this.chatWidget.style.width,
                    height: this.chatWidget.style.height
                };
            }
            
            this.chatWidget.classList.add('omni-maximized');
            this.maximizeBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/>
                </svg>
            `;
            this.maximizeBtn.title = 'Restore window';
        } else {
            this.chatWidget.classList.remove('omni-maximized');
            this.maximizeBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                </svg>
            `;
            this.maximizeBtn.title = 'Maximize window';
            
            if (this.originalPosition) {
                this.chatWidget.style.bottom = this.originalPosition.bottom;
                this.chatWidget.style.right = this.originalPosition.right;
                this.chatWidget.style.width = this.originalPosition.width;
                this.chatWidget.style.height = this.originalPosition.height;
            }
        }
    }

    setupAutoResize() {
        this.userInput.style.height = 'auto';
        this.userInput.style.height = (this.userInput.scrollHeight) + 'px';
    }

    autoResizeTextarea() {
        this.userInput.style.height = 'auto';
        const newHeight = Math.min(this.userInput.scrollHeight, 120);
        this.userInput.style.height = newHeight + 'px';
        this.userInput.style.overflowY = 'hidden';
    }

    toggleChat() {
        this.chatWidget.classList.toggle('omni-hidden');
        if (!this.chatWidget.classList.contains('omni-hidden')) {
            this.userInput.focus();
            this.updateStatus('Online');
        } else {
            this.updateStatus('Offline');
        }
    }

    hideChat() {
        this.chatWidget.classList.add('omni-hidden');
        this.updateStatus('Offline');
    }

    updateStatus(status) {
        if (this.statusElement) {
            this.statusElement.textContent = status;
            this.statusElement.style.opacity = '0.5';
            setTimeout(() => {
                this.statusElement.style.opacity = '1';
            }, 300);
        }
    }

    updateCharCount() {
        const count = this.userInput.value.length;
        this.charCount.textContent = `${count}/500`;
        
        if (count > 450) {
            this.charCount.style.color = 'var(--secondary)';
        } else if (count > 400) {
            this.charCount.style.color = 'var(--primary)';
        } else {
            this.charCount.style.color = 'var(--muted)';
        }
    }

    togglePrompts() {
        this.promptsCollapsed = !this.promptsCollapsed;
        
        if (this.promptsCollapsed) {
            this.promptsContainer.classList.remove('omni-prompts-expanded');
            this.promptsContainer.classList.add('omni-prompts-collapsed');
            this.togglePromptsBtn.classList.add('collapsed');
            this.promptsSection.classList.add('omni-compact');
            this.collapseIndicator.textContent = '(click to expand)';
        } else {
            this.promptsContainer.classList.remove('omni-prompts-collapsed');
            this.promptsContainer.classList.add('omni-prompts-expanded');
            this.togglePromptsBtn.classList.remove('collapsed');
            this.promptsSection.classList.remove('omni-compact');
            this.collapseIndicator.textContent = '(click to collapse)';
        }
    }

    generatePrompts() {
        this.promptsContainer.innerHTML = '';
        
        const unusedPrompts = this.availablePrompts.filter(prompt => !this.usedPrompts.has(prompt));
        const randomPrompts = this.shuffleArray([...unusedPrompts]).slice(0, 3);
        
        if (randomPrompts.length < 3) {
            const additional = this.shuffleArray([...this.usedPrompts]).slice(0, 3 - randomPrompts.length);
            randomPrompts.push(...additional);
        }

        randomPrompts.forEach(prompt => {
            const button = document.createElement('button');
            button.className = `omni-prompt-btn ${this.usedPrompts.has(prompt) ? 'omni-prompt-used' : ''}`;
            button.textContent = prompt;
            button.addEventListener('click', () => {
                if (!this.usedPrompts.has(prompt)) {
                    this.usedPrompts.add(prompt);
                    button.classList.add('omni-prompt-used');
                    this.sendMessage(prompt);
                }
            });
            this.promptsContainer.appendChild(button);
        });
    }

    shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    addMessage(content, isUser) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `omni-message ${isUser ? 'omni-message-user' : 'omni-message-bot'}`;
        
        const avatar = document.createElement('div');
        avatar.className = 'omni-message-avatar';
        
        if (isUser) {
            avatar.textContent = 'You';
            avatar.style.background = 'linear-gradient(135deg, var(--primary), var(--primary-700))';
        } else {
            avatar.innerHTML = `<img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />`;
        }
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'omni-message-content';
        
        // Use the enhanced formatContent method
        contentDiv.innerHTML = this.formatContent(content);
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(contentDiv);
        this.messagesContainer.appendChild(messageDiv);
        
        this.scrollToBottom();
        return contentDiv;
    }

    // NEW: IMPROVED FORMAT CONTENT METHOD WITH BETTER SPACING FIX
    formatContent(text) {
        if (!text) return '';
        
        console.log('Original text:', text);
        
        // FIX 1: Add spaces between words that are stuck together
        let cleanedText = this.fixSpacingIssues(text);
        
        // FIX 2: Ensure list items preserve their numbers/bullets
        cleanedText = this.preserveListMarkers(cleanedText);
        
        // Split into lines
        const lines = cleanedText.split('\n');
        let result = '';
        let inList = false;
        let listType = '';
        let listItems = [];
        
        for (let line of lines) {
            line = line.trim();
            
            if (!line) {
                // Empty line - close any open list
                if (inList && listItems.length > 0) {
                    result += this.createListHTML(listType, listItems);
                    listItems = [];
                    inList = false;
                }
                result += '<br>';
                continue;
            }
            
            // Check for numbered list (more robust matching)
            const numberedMatch = line.match(/^(\d+)[\.\)]\s+(.+)$/);
            // Check for bulleted list
            const bulletedMatch = line.match(/^([•\-*])\s+(.+)$/);
            
            if (numberedMatch) {
                const [, number, content] = numberedMatch;
                if (!inList || listType !== 'numbered') {
                    if (inList && listItems.length > 0) {
                        result += this.createListHTML(listType, listItems);
                        listItems = [];
                    }
                    inList = true;
                    listType = 'numbered';
                }
                // Preserve the number in the content
                listItems.push(this.applyInlineFormatting(content));
            } 
            else if (bulletedMatch) {
                const [, bullet, content] = bulletedMatch;
                if (!inList || listType !== 'bulleted') {
                    if (inList && listItems.length > 0) {
                        result += this.createListHTML(listType, listItems);
                        listItems = [];
                    }
                    inList = true;
                    listType = 'bulleted';
                }
                listItems.push(this.applyInlineFormatting(content));
            } 
            else {
                // Not a list item - close any open list
                if (inList && listItems.length > 0) {
                    result += this.createListHTML(listType, listItems);
                    listItems = [];
                    inList = false;
                }
                
                // Check for headers
                if (line.startsWith('### ')) {
                    result += `<h4>${this.applyInlineFormatting(line.substring(4))}</h4>`;
                } else if (line.startsWith('## ')) {
                    result += `<h3>${this.applyInlineFormatting(line.substring(3))}</h3>`;
                } else if (line.startsWith('# ')) {
                    result += `<h2>${this.applyInlineFormatting(line.substring(2))}</h2>`;
                } else if (line.startsWith('**') && line.endsWith('**')) {
                    result += `<strong>${this.applyInlineFormatting(line.substring(2, line.length - 2))}</strong><br>`;
                } else {
                    result += `<p>${this.applyInlineFormatting(line)}</p>`;
                }
            }
        }
        
        // Close any remaining list
        if (inList && listItems.length > 0) {
            result += this.createListHTML(listType, listItems);
        }
        
        console.log('Formatted result:', result);
        return result;
    }

    // NEW METHOD: Fix spacing issues in text
    fixSpacingIssues(text) {
        let result = text;
        
        // Fix common spacing issues:
        
        // 1. Add space after period if missing (but not for decimals)
        result = result.replace(/([a-zA-Z])\.([A-Z])/g, '$1. $2');
        
        // 2. Add space after colon if missing
        result = result.replace(/:(?=[A-Za-z])/g, ': ');
        
        // 3. Fix "word1.word2" -> "word1. word2"
        result = result.replace(/([a-z])\.([A-Z])/g, '$1. $2');
        
        // 4. Fix "word1,word2" -> "word1, word2"
        result = result.replace(/([a-z]),([A-Za-z])/g, '$1, $2');
        
        // 5. Fix "Service:" becoming "Service:"
        result = result.replace(/([A-Z][a-z]+):([A-Z])/g, '$1: $2');
        
        // 6. Fix specific pattern from your example: "Services:" at end of line
        result = result.replace(/([A-Za-z]+):$/gm, '$1: ');
        
        return result;
    }

    // NEW METHOD: Preserve list markers
    preserveListMarkers(text) {
        let result = text;
        
        // Ensure list items have proper formatting
        // Fix: "1.BoardFormation" -> "1. Board Formation"
        result = result.replace(/(\d+)[\.\)]([A-Z][a-z]+)/g, '$1. $2');
        
        // Fix: "•Assistancewith" -> "• Assistance with"
        result = result.replace(/([•\-*])([A-Z][a-z]+)/g, '$1 $2');
        
        // Fix the specific example from image: split long concatenated text
        // This handles cases where the AI returns text without spaces
        result = this.splitConcatenatedText(result);
        
        return result;
    }

    // NEW METHOD: Split concatenated text into proper words
    splitConcatenatedText(text) {
        // This is a simple word splitter - in production you might want a more sophisticated solution
        // Common patterns from your example:
        
        // Fix "CorporateGovernanceServices:" -> "Corporate Governance Services:"
        text = text.replace(/([a-z])([A-Z])/g, '$1 $2');
        
        // Fix "AI" at beginning (should stay as "AI")
        text = text.replace(/^AI\s+/, '');
        text = text.replace(/\s+AI\s+/, ' ');
        
        // Fix "OGMBC" (should stay together)
        text = text.replace(/O G M B C/g, 'OGMBC');
        text = text.replace(/O G M/g, 'OGM');
        
        // Fix specific known concatenations
        const knownWords = {
            'rangoot': 'a range of',
            'compilance': 'compliance',
            'sevices': 'services',
            'recordstand': 'records and',
            'iterinstitutions': 'international institutions',
            'de': 'ae'
        };
        
        Object.entries(knownWords).forEach(([wrong, correct]) => {
            text = text.replace(new RegExp(wrong, 'gi'), correct);
        });
        
        return text;
    }

    createListHTML(type, items) {
        if (type === 'numbered') {
            return `<ol class="omni-list">${items.map((item, index) => `<li>${item}</li>`).join('')}</ol>`;
        } else {
            return `<ul class="omni-list">${items.map(item => `<li>${item}</li>`).join('')}</ul>`;
        }
    }

    applyInlineFormatting(text) {
        if (!text) return '';
        
        let result = text;
        
        // Apply bold formatting
        result = result.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Apply italic formatting
        result = result.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Apply code formatting
        result = result.replace(/`([^`]+)`/g, '<code class="omni-code">$1</code>');
        
        return result;
    }

    cleanupAIText(text) {
        // Fix common formatting issues
        let result = text;
        
        // Fix: "1. Item1. Item2" -> "1. Item\n2. Item"
        result = result.replace(/(\d+)\.\s*([^.]+?)(?=\d+\.)/g, function(match, num, content) {
            return num + '. ' + content.trim() + '\n';
        });
        
        // Fix: "• Item• Item" -> "• Item\n• Item"
        result = result.replace(/([•\-*])\s*([^•\-*\n]+?)(?=[•\-*])/g, function(match, bullet, content) {
            return bullet + ' ' + content.trim() + '\n';
        });
        
        // Add spaces after periods if missing
        result = result.replace(/([a-z])\.([A-Z])/g, '$1. $2');
        
        // Add line breaks before lists
        result = result.replace(/:\s*(\d+\.)/g, ':\n\n$1');
        result = result.replace(/:\s*([•\-*])/g, ':\n\n$1');
        
        // Ensure proper spacing for list items
        result = result.replace(/^(\d+)\.(\S)/gm, '$1. $2');
        result = result.replace(/^([•\-*])(\S)/gm, '$1 $2');
        
        // Split into lines and clean up
        const lines = result.split('\n');
        const cleanedLines = [];
        
        for (const line of lines) {
            const cleanedLine = line.trim();
            if (cleanedLine) {
                cleanedLines.push(cleanedLine);
            }
        }
        
        return cleanedLines.join('\n');
    }

    typeMessage(contentDiv, fullContent) {
        return new Promise((resolve) => {
            this.isTypingResponse = true;
            this.updateStatus('Typing...');
            
            contentDiv.innerHTML = '';
            
            let index = 0;
            const baseSpeed = 40;
            let currentSpeed = baseSpeed;
            
            if (this.currentTypingInterval) {
                clearInterval(this.currentTypingInterval);
                this.currentTypingInterval = null;
            }
            
            const typeNextChunk = () => {
                if (index >= fullContent.length) {
                    this.isTypingResponse = false;
                    this.updateStatus('Online');
                    resolve();
                    return;
                }
                
                let chunkSize = 1;
                let nextChars = fullContent.substring(index, Math.min(index + 5, fullContent.length));
                
                if (nextChars.match(/^[.,!?;:]/)) {
                    chunkSize = 1;
                    currentSpeed = 80;
                    
                    if (/[.!?]/.test(nextChars[0])) {
                        currentSpeed = 160;
                    }
                } else if (nextChars.match(/^\s\s+/)) {
                    chunkSize = Math.min(2, nextChars.match(/^\s+/)[0].length);
                    currentSpeed = 20;
                } else if (nextChars.match(/^\s/)) {
                    chunkSize = 1;
                    currentSpeed = 30;
                } else if (nextChars.match(/^[a-zA-Z]{3,}/)) {
                    chunkSize = 3;
                    currentSpeed = 25;
                } else {
                    chunkSize = 1;
                    currentSpeed = baseSpeed;
                }
                
                const chunk = fullContent.substring(index, index + chunkSize);
                
                // Get the current text
                const currentText = contentDiv.textContent || '';
                const newText = currentText + chunk;
                
                // Format the complete text so far
                contentDiv.innerHTML = this.formatContent(newText);
                
                index += chunkSize;
                this.scrollToBottom();
                
                let nextDelay = currentSpeed;
                nextDelay += Math.random() * 20 - 10;
                nextDelay = Math.max(20, nextDelay);
                
                if (chunk.match(/[.!?]/)) {
                    nextDelay += 150;
                } else if (chunk.match(/[,;:]/)) {
                    nextDelay += 80;
                } else if (index < fullContent.length && fullContent.charAt(index) === ' ') {
                    nextDelay += 30;
                }
                
                this.currentTypingInterval = setTimeout(typeNextChunk, nextDelay);
            };
            
            typeNextChunk();
        });
    }

    showThinkingIndicator() {
        this.removeTypingIndicator();
        this.isThinking = true;
        
        const thinkingDiv = document.createElement('div');
        thinkingDiv.className = 'omni-thinking';
        thinkingDiv.id = 'omni-thinking';
        
        const avatar = document.createElement('div');
        avatar.className = 'omni-message-avatar';
        avatar.innerHTML = `<img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'omni-thinking-content';
        
        const dotsDiv = document.createElement('div');
        dotsDiv.className = 'omni-thinking-dots';
        dotsDiv.innerHTML = `
            <div class="omni-thinking-dot"></div>
            <div class="omni-thinking-dot"></div>
            <div class="omni-thinking-dot"></div>
        `;
        
        const textDiv = document.createElement('div');
        textDiv.className = 'omni-thinking-text';
        textDiv.textContent = 'Thinking...';
        
        contentDiv.appendChild(dotsDiv);
        contentDiv.appendChild(textDiv);
        
        thinkingDiv.appendChild(avatar);
        thinkingDiv.appendChild(contentDiv);
        this.messagesContainer.appendChild(thinkingDiv);
        
        this.scrollToBottom();
    }

    removeTypingIndicator() {
        const typing = document.getElementById('omni-typing');
        if (typing) typing.remove();
        
        const thinking = document.getElementById('omni-thinking');
        if (thinking) {
            thinking.remove();
            this.isThinking = false;
        }
        
        if (this.currentTypingInterval) {
            clearTimeout(this.currentTypingInterval);
            this.currentTypingInterval = null;
        }
        this.isTypingResponse = false;
    }

    scrollToBottom() {
        setTimeout(() => {
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        }, 50);
    }

    async sendMessage(prefilledMessage = null) {
        const messageText = prefilledMessage || this.userInput.value.trim();
        
        if (!messageText) return;

        this.addMessage(messageText, true);
        this.conversationHistory.push({ role: 'user', content: messageText });

        if (!prefilledMessage) {
            this.userInput.value = '';
            this.updateCharCount();
            this.setupAutoResize();
            this.usedPrompts.add(messageText);
            this.generatePrompts();
        }

        this.userInput.disabled = true;
        this.sendBtn.disabled = true;

        this.showThinkingIndicator();
        this.updateStatus('Thinking...');

        try {
            const response = await fetch('chat_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: messageText,
                    history: this.conversationHistory
                })
            });

            const data = await response.json();
            this.removeTypingIndicator();

            if (data.error) {
                this.addMessage(`Error: ${data.error}`, false);
                this.updateStatus('Online');
            } else {
                const messageContentDiv = this.addMessage('', false);
                await this.typeMessage(messageContentDiv, data.reply);
                this.conversationHistory.push({ role: 'assistant', content: data.reply });
            }
        } catch (error) {
            this.removeTypingIndicator();
            this.updateStatus('Online');
            this.addMessage('Sorry, there was a connection error. Please try again.', false);
            console.error('omni Error:', error);
        }

        this.userInput.disabled = false;
        this.sendBtn.disabled = false;
        this.userInput.focus();
    }
}

// Initialize omni when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const omni = new omniChat();
    window.omniChatInstance = omni;
});
</script>

