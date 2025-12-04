<?php
include 'includes/database.php';
include 'includes/header.php'
?>

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
            <h4 class="mb-2" style="color:#091e3e;">Financial Clarity</h4>
            <p style="color:#747576;">
              From IFRS reporting to statutory audits, we deliver insights that 
              enhance transparency and decision-making.
            </p>
          </div>
        </div>

        <!-- Feature 3 -->
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

        <!-- Feature 2 -->
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
            <div class="num display-5 fw-bold" data-target="150">0</div>
            <div class="label" style="color:#747576;">Projects Delivered</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-clock-history"></i></div>
            <div class="num display-5 fw-bold" data-target="98">0</div>
            <div class="label" style="color:#747576;">On-Time Completion (%)</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-building"></i></div>
            <div class="num display-5 fw-bold" data-target="40">0</div>
            <div class="label" style="color:#747576;">Industries Served</div>
          </div>
          <div class="col text-center">
            <div class="stat-icon mb-2"><i class="bi bi-star-fill"></i></div>
            <div class="num display-5 fw-bold" data-target="100">0</div>
            <div class="label" style="color:#747576;">Client Satisfaction (%)</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="section testimonials-section" style="padding: 0;">
    <div class="testimonials-bg">
      <div class="container py-5">
        <h2 class="mb-5" style="color: #091e3e; font-weight: 700;">What Clients Say</h2>
        <div class="row g-4 justify-content-center">
          <!-- Testimonial 1 -->
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
          <!-- Testimonial 2 -->
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
          <!-- Testimonial 3 -->
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
  </section>

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
          <p class="mb-4" style="color:#747576;">
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

  <!-- Floating Chat Button -->
  <div id="omni-floating-btn" class="omni-floating-btn">
      <div class="omni-avatar">
          <img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />
      </div>
      <span class="omni-pulse"></span>
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
          <button id="omni-close" class="omni-close-btn">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
          </button>
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
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!this.chatWidget.contains(e.target) && !this.floatingBtn.contains(e.target)) {
                this.hideChat();
            }
        });
        
        // Toggle prompts on header click
        this.promptsSection.addEventListener('click', (e) => {
            if (e.target.closest('.omni-prompts-header') && !e.target.closest('.omni-prompts-actions')) {
                this.togglePrompts();
            }
        });
    }

    setupAutoResize() {
        this.userInput.style.height = 'auto';
        this.userInput.style.height = (this.userInput.scrollHeight) + 'px';
    }

    autoResizeTextarea() {
    this.userInput.style.height = 'auto';
    const newHeight = Math.min(this.userInput.scrollHeight, 120);
    this.userInput.style.height = newHeight + 'px';
    this.userInput.style.overflowY = 'hidden'; // Always hide overflow
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
            
            // Add status animation
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
        
        // Filter out used prompts and get 3 random ones
        const unusedPrompts = this.availablePrompts.filter(prompt => !this.usedPrompts.has(prompt));
        const randomPrompts = this.shuffleArray([...unusedPrompts]).slice(0, 3);
        
        // If we don't have enough unused prompts, reuse some
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
            // Use omni image for bot avatar
            avatar.innerHTML = `<img src="resources/img/omni.svg" alt="omni Assistant" width="40" height="40" />`;
        }
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'omni-message-content';
        contentDiv.textContent = content;
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(contentDiv);
        this.messagesContainer.appendChild(messageDiv);
        
        this.scrollToBottom();
        return contentDiv;
    }

    typeMessage(contentDiv, fullContent) {
        return new Promise((resolve) => {
            this.isTypingResponse = true;
            this.updateStatus('Typing...');
            
            let index = 0;
            const baseSpeed = 40; // Slower base speed: 40ms per character (was 20ms)
            let currentSpeed = baseSpeed;
            const maxChunkSize = 3; // Smaller chunks for slower typing
            
            // First clear any existing typing interval
            if (this.currentTypingInterval) {
                clearInterval(this.currentTypingInterval);
                this.currentTypingInterval = null;
            }
            
            const typeNextChunk = () => {
                if (index >= fullContent.length) {
                    // Typing complete
                    this.isTypingResponse = false;
                    this.updateStatus('Online');
                    resolve();
                    return;
                }
                
                // Determine chunk size and speed based on upcoming characters
                let chunkSize = 1; // Start with 1 character
                let nextChars = fullContent.substring(index, Math.min(index + 5, fullContent.length));
                
                // Analyze next characters to determine typing behavior
                if (nextChars.match(/^[.,!?;:]/)) {
                    // Punctuation - type slowly and pause after
                    chunkSize = 1;
                    currentSpeed = 80; // Very slow for punctuation
                    
                    // Add extra pause after certain punctuation
                    if (/[.!?]/.test(nextChars[0])) {
                        currentSpeed = 160; // Even slower for sentence endings
                    }
                } else if (nextChars.match(/^\s\s+/)) {
                    // Multiple spaces or tabs
                    chunkSize = Math.min(2, nextChars.match(/^\s+/)[0].length);
                    currentSpeed = 20; // Faster for multiple spaces
                } else if (nextChars.match(/^\s/)) {
                    // Single space
                    chunkSize = 1;
                    currentSpeed = 30; // Fast for spaces
                } else if (nextChars.match(/^[a-zA-Z]{3,}/)) {
                    // Word with 3+ letters - type slightly faster
                    chunkSize = 3;
                    currentSpeed = 25;
                } else {
                    // Default speed for other characters
                    chunkSize = 1;
                    currentSpeed = baseSpeed;
                }
                
                // Add the chunk
                const chunk = fullContent.substring(index, index + chunkSize);
                contentDiv.textContent += chunk;
                index += chunkSize;
                
                // Apply formatting to the current text
                contentDiv.innerHTML = this.formatMessage(contentDiv.textContent);
                
                // Scroll to bottom
                this.scrollToBottom();
                
                // Schedule next chunk with variable delay
                let nextDelay = currentSpeed;
                
                // Add random variation to make it more human-like (±10ms)
                nextDelay += Math.random() * 20 - 10;
                
                // Ensure minimum delay
                nextDelay = Math.max(20, nextDelay);
                
                // Add longer pauses at natural breakpoints
                if (chunk.match(/[.!?]/)) {
                    nextDelay += 150; // Longer pause after sentences
                } else if (chunk.match(/[,;:]/)) {
                    nextDelay += 80; // Medium pause after commas/semicolons
                } else if (index < fullContent.length && fullContent.charAt(index) === ' ') {
                    nextDelay += 30; // Small pause before next word
                }
                
                // Schedule next chunk
                this.currentTypingInterval = setTimeout(typeNextChunk, nextDelay);
            };
            
            // Start typing
            typeNextChunk();
        });
    }

    formatMessage(content) {
        // Create a temporary div to parse the content
        const tempDiv = document.createElement('div');
        
        // First, handle code blocks separately
        const codeRegex = /`([^`]+)`/g;
        let formatted = content;
        let match;
        const codeParts = [];
        
        // Store and replace code blocks with placeholders
        let codeIndex = 0;
        while ((match = codeRegex.exec(content)) !== null) {
            codeParts.push(match[1]);
            formatted = formatted.replace(match[0], `__CODE${codeIndex}__`);
            codeIndex++;
        }
        
        // Process the formatted text
        let result = formatted;
        
        // Replace headers
        result = result.replace(/### (.*?)(?:\n|$)/g, '<h4>$1</h4>');
        result = result.replace(/## (.*?)(?:\n|$)/g, '<h3>$1</h3>');
        
        // Process line by line for better list handling
        const lines = result.split('\n');
        let htmlLines = [];
        let inList = false;
        let listItems = [];
        
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            
            // Check for list items
            const listMatch = line.match(/^([•\-*]|\d+\.)\s+(.+)/);
            
            if (listMatch) {
                if (!inList) {
                    inList = true;
                    listItems = [];
                }
                listItems.push(listMatch[2]);
                
                // If next line is not a list item, close the list
                if (i === lines.length - 1 || !lines[i + 1].match(/^([•\-*]|\d+\.)\s/)) {
                    htmlLines.push('<ul class="omni-list">');
                    listItems.forEach(item => {
                        htmlLines.push(`<li>${this.applyInlineFormatting(item)}</li>`);
                    });
                    htmlLines.push('</ul>');
                    inList = false;
                }
            } else {
                // Not a list item
                if (inList) {
                    // We were in a list but this line isn't a list item
                    htmlLines.push('<ul class="omni-list">');
                    listItems.forEach(item => {
                        htmlLines.push(`<li>${this.applyInlineFormatting(item)}</li>`);
                    });
                    htmlLines.push('</ul>');
                    inList = false;
                    listItems = [];
                }
                
                // Apply inline formatting to regular lines
                if (line.trim()) {
                    htmlLines.push(this.applyInlineFormatting(line));
                } else {
                    htmlLines.push('<br>');
                }
            }
        }
        
        // Join all lines
        result = htmlLines.join('');
        
        // Restore code blocks
        codeParts.forEach((code, index) => {
            result = result.replace(`__CODE${index}__`, `<code class="omni-code">${this.escapeHtml(code)}</code>`);
        });
        
        // Replace newlines with <br> tags (except where we already have HTML)
        result = result.replace(/\n/g, '<br>');
        
        return result;
    }

    applyInlineFormatting(text) {
        // Apply bold formatting
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        
        // Apply italic formatting
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        
        // Apply emphasis for underscores (optional)
        text = text.replace(/_(.*?)_/g, '<em>$1</em>');
        
        return text;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showThinkingIndicator() {
        this.removeTypingIndicator();
        this.isThinking = true;
        
        const thinkingDiv = document.createElement('div');
        thinkingDiv.className = 'omni-thinking';
        thinkingDiv.id = 'omni-thinking';
        
        const avatar = document.createElement('div');
        avatar.className = 'omni-message-avatar';
        // Use omni image for thinking avatar as well
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
        
        // Clear any ongoing typing interval
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

        // Add user message to UI and history
        this.addMessage(messageText, true);
        this.conversationHistory.push({ role: 'user', content: messageText });

        // Clear input and update prompts if it's a new message
        if (!prefilledMessage) {
            this.userInput.value = '';
            this.updateCharCount();
            this.setupAutoResize();
            this.usedPrompts.add(messageText);
            this.generatePrompts();
        }

        // Disable UI
        this.userInput.disabled = true;
        this.sendBtn.disabled = true;

        // Show thinking indicator
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
            this.removeTypingIndicator(); // Remove thinking indicator

            if (data.error) {
                this.addMessage(`Error: ${data.error}`, false);
                this.updateStatus('Online');
            } else {
                // Create the bot message container (initially empty)
                const messageContentDiv = this.addMessage('', false);
                
                // Type out the response character by character with slower speed
                await this.typeMessage(messageContentDiv, data.reply);
                
                // Add to conversation history after typing is complete
                this.conversationHistory.push({ role: 'assistant', content: data.reply });
                
                // Status is already updated to Online by typeMessage when done
            }
        } catch (error) {
            this.removeTypingIndicator();
            this.updateStatus('Online');
            this.addMessage('Sorry, there was a connection error. Please try again.', false);
            console.error('omni Error:', error);
        }

        // Re-enable UI
        this.userInput.disabled = false;
        this.sendBtn.disabled = false;
        this.userInput.focus();
    }
}

// Initialize omni when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const omni = new omniChat();
    
    // Make omni globally accessible if needed
    window.omniChatInstance = omni;
});
</script>
