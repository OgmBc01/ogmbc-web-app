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
      <div class="carousel-item active">
        <div class="container">
          <div class="row align-items-center gy-4">
            <div class="col-lg-7 mb-5" style="margin-top: 5rem;">
              <span class="chip">Auditing • Accounting • Advisory</span>
              <h1 class="hero-title">Trusted audits & financial clarity for growing businesses.</h1>
              <p class="lead hero-lead">We deliver ISA-compliant audits, IFRS reporting, and practical advisory so you can make confident decisions and satisfy stakeholders.</p>
              <div class="d-flex gap-3 flex-wrap">
                <a class="btn btn-primary" href="#contact">Book a discovery call</a>
                <a class="btn btn-ghost" href="#services">Explore services</a>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
                <div class="d-flex flex-column gap-3 contact-info">
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                    <span>+971 50 986 0136</span>
                  </div>
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                    <span>info@ogmbc.ae</span>
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
      <div class="carousel-item">
        <div class="container">
          <div class="row align-items-center gy-4">
            <div class="col-lg-7 mb-5">
              <span class="chip">Financial Reporting</span>
              <h1 class="hero-title">Clear, compliant, and timely IFRS reporting.</h1>
              <p class="lead hero-lead">Accurate preparation, conversion, and consolidation of financial statements aligned with global standards.</p>
              <div class="d-flex gap-3 flex-wrap">
                <a class="btn btn-primary" href="#contact">Get IFRS support</a>
              </div>
            </div>
            <div class="col-lg-5">
              <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
                <div class="d-flex flex-column gap-3 contact-info">
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                    <span>+971 50 986 0136</span>
                  </div>
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                    <span>info@ogmbc.ae</span>
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
      <div class="carousel-item">
        <div class="container">
          <div class="row align-items-center gy-4">
            <div class="col-lg-7 mb-5">
              <span class="chip">Advisory & Compliance</span>
              <h1 class="hero-title">Strengthen governance & controls with OGMBC.</h1>
              <p class="lead hero-lead">Risk assessments, SOPs, internal audits, and tax compliance tailored to your sector.</p>
              <div class="d-flex gap-3 flex-wrap">
                <a class="btn btn-primary" href="#contact">Talk to an advisor</a>
              </div>
            </div>
           <div class="col-lg-5">
              <div class="contact-cta-inner d-flex flex-column gap-3 p-4 rounded-4 shadow-lg bg-blur text-white">
                <div class="d-flex flex-column gap-3 contact-info">
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-telephone-fill"></i></div>
                    <span>+971 50 986 0136</span>
                  </div>
                  <div class="info-box d-flex align-items-center gap-2">
                    <div class="icon-circle"><i class="bi bi-envelope-fill"></i></div>
                    <span>info@ogmbc.ae</span>
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

    <!-- Controls -->
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
        From statutory audits to outsourced finance, we tailor our engagement to your governance and growth needs.
      </p>
    </div> 
    <div class="row g-4 services">
      <!-- Audit & Assurance -->
      <div class="col-lg-4 col-md-6 justify-content-center">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Audit & Assurance</div> -->
            <div class="service-icon">
              <i class="bi bi-clipboard-check"></i>
            </div>
            <h3>External Audit (ISA)</h3>
            <p>Annual audits, limited reviews, and agreed-upon procedures with clear management letters and board-ready reports.</p>
            <a href="audit-&-audit-support.php" class="btn btn-outline-primary mt-3">Learn More</a>
          </div>
        </article>
      </div>

      <!-- Financial Reporting -->
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Financial Reporting</div> -->
            <div class="service-icon">
              <i class="bi bi-file-earmark-bar-graph"></i>
            </div>
            <h3>IFRS Financial Statements</h3>
            <p>Preparation, conversion, and consolidation under IFRS with strong controls and documentation.</p>
            <a href="financial-statement-reporting.php" class="btn btn-outline-primary mt-3">Learn More</a>
          </div>
        </article>
      </div>

      <!-- Advisory -->
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Advisory</div> -->
            <div class="service-icon">
              <i class="bi bi-shield-check"></i>
            </div>
            <h3>Internal Control & Compliance</h3>
            <p>Risk assessments, SOPs, internal audit setup, and regulatory compliance (FIRS, CAC, SEC where applicable).</p>
            <a href="internal-controlling.php" class="btn btn-outline-primary mt-3">Learn More</a>
          </div>
        </article>
      </div>

      <!-- Tax -->
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Tax</div> -->
            <div class="service-icon">
              <i class="bi bi-calculator"></i>
            </div>
            <h3>Tax Compliance & Planning</h3>
            <p>Company income tax, PAYE/VAT, WHT, and liaison with authorities; efficient, compliant tax positions.</p>
            <a href="tax-consultancy.php" class="btn btn-outline-primary mt-3">Learn More</a>
          </div>
        </article>
      </div>

      <!-- Outsourcing -->
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Outsourcing</div> -->
            <div class="service-icon">
              <i class="bi bi-gear"></i>
            </div>
            <h3>Virtual/Outsourced Finance</h3>
            <p>Bookkeeping, management accounts, cash-flow, and board packs—technology-enabled and timely.</p>
            <a href="bookkeeping.php" class="btn btn-outline-primary mt-3">Learn More</a>
          </div>
        </article>
      </div>

      <!-- Training -->
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body d-flex flex-column align-items-center text-center">
            <!-- <div class="chip">Training</div> -->
            <div class="service-icon">
              <i class="bi bi-person-video3"></i>
            </div>
            <h3>Finance Team Upskilling</h3>
            <p>Workshops and clinics in Excel, controls, close process, and IFRS for finance teams.</p>
            <a href="ifrs-advisory.php" class="btn btn-outline-primary mt-3">Learn More</a>
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


<!-- Blog Section -->
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
            <span class="fw-bold" style="color:#d0aa4b; font-size:1.25rem;">+971502923136</span>
          </div>
        </div>
      </div>
      <!-- Right: Form -->
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
          <button type="submit" class="btn btn-dark w-100 py-2 fw-bold" style="background:#091e3e;">Request A Quote</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Floating Action Buttons -->
<div class="floating-buttons">
    <!-- WhatsApp Button -->
    <a href="https://wa.me/+971509860136" class="floating-btn whatsapp-btn" target="_blank" rel="noopener">
        <i class="bi bi-whatsapp"></i>
    </a>
    
    <!-- Back to Top Button -->
    <a href="#" class="floating-btn back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>
</div>

<?php
include 'includes/footer.php'
?>