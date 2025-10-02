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
            <div class="col-lg-7 mb-5">
              <span class="chip">Auditing • Accounting • Advisory</span>
              <h1 class="hero-title">Trusted audits & financial clarity for growing businesses.</h1>
              <p class="lead hero-lead">We deliver ISA-compliant audits, IFRS reporting, and practical advisory so you can make confident decisions and satisfy stakeholders.</p>
              <div class="d-flex gap-3 flex-wrap">
                <a class="btn btn-primary" href="#contact">Book a discovery call</a>
                <a class="btn btn-ghost" href="#services">Explore services</a>
              </div>
            </div>
            <!--div class="col-lg-5">
              <div class="hero-illustration card">
                <img src="images/hero1.jpg" alt="Audit working session" class="img-fluid rounded-3">
              </div>
            </div-->
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
            <!--div class="col-lg-5">
              <div class="hero-illustration card">
                <img src="images/hero2.jpg" alt="Financial reporting" class="img-fluid rounded-3">
              </div>
            </div-->
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
            <!--div class="col-lg-5">
              <div class="hero-illustration card">
                <img src="images/hero3.jpg" alt="Advisory session" class="img-fluid rounded-3">
              </div>
            </div-->
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

<!-- Overlapping Contact CTA -->
<div class="contact-cta container position-relative">
  <div class="contact-cta-inner d-flex flex-column flex-md-row align-items-center justify-content-between p-4 rounded-4 shadow-lg">
    <!-- Contact Info -->
    <div class="d-flex flex-column flex-md-row align-items-center gap-4 text-white contact-info">
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

    <!-- CTA Button -->
    <a href="contact.php" class="btn btn-glow mt-3 mt-md-0 text-white">
      Request Proposal
    </a>
  </div>
</div>

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
      <div class="col-lg-4 col-md-6">
        <article class="service card h-100">
          <div class="card-body">
            <div class="chip">Audit & Assurance</div>
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
          <div class="card-body">
            <div class="chip">Financial Reporting</div>
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
          <div class="card-body">
            <div class="chip">Advisory</div>
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
          <div class="card-body">
            <div class="chip">Tax</div>
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
          <div class="card-body">
            <div class="chip">Outsourcing</div>
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
          <div class="card-body">
            <div class="chip">Training</div>
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
<section class="checked py-5">
  <div class="container text-center">
    <h2 class="mb-4">Why Choose Us</h2>
    <p class="lead mb-5">
      We are committed to making a positive impact on our clients' businesses and helping them 
      navigate the complexities of today's business environment with confidence and success.
    </p>

    <div class="row g-4">
      <!-- Feature 1 -->
      <div class="col-md-4">
        <div class="feature-card p-4 h-100">
          <i class="bi bi-graph-up-arrow feature-icon"></i>
          <h4 class="mt-3">Financial Clarity</h4>
          <p>
            From IFRS reporting to statutory audits, we deliver insights that 
            enhance transparency and decision-making.
          </p>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="col-md-4">
        <div class="feature-card p-4 h-100">
          <i class="bi bi-shield-check feature-icon"></i>
          <h4 class="mt-3">Compliance & Assurance</h4>
          <p>
            Our experienced team ensures adherence to international standards, 
            regulatory frameworks, and tax obligations.
          </p>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="col-md-4">
        <div class="feature-card p-4 h-100">
          <i class="bi bi-people feature-icon"></i>
          <h4 class="mt-3">Client-Centered Approach</h4>
          <p>
            We partner with you, offering personalized advisory, outsourcing, 
            and training solutions to fit your growth needs.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats -->
<section id="stats" class="section">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-sm-6 col-md-3">
        <div class="stat card p-3">
          <div class="num display-6" data-target="150+">0</div>
          <div class="label muted">Engagements delivered</div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="stat card p-3">
          <div class="num display-6" data-target="98%">0</div>
          <div class="label muted">On-time completion</div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="stat card p-3">
          <div class="num display-6" data-target="40+">0</div>
          <div class="label muted">Industries served</div>
        </div>
      </div>
      <div class="col-sm-6 col-md-3">
        <div class="stat card p-3">
          <div class="num display-6" data-target="A+">0</div>
          <div class="label muted">Client satisfaction</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="section">
  <div class="container">
    <h2>What clients say</h2>
    <div class="d-flex justify-content-center">
      <p class="lead text-center">
        Board-ready insights, clear communication, and pragmatic recommendations.
      </p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <article class="quote card p-4">
          <p>“OGM helped us convert to IFRS and tightened our controls before our fundraise. Smooth end-to-end.”</p>
          <div class="text-light mt-3">CFO, Growth-stage Fintech</div>
        </article>
      </div>
      <div class="col-md-4">
        <article class="quote card p-4">
          <p>“Their audit team kept us informed and on schedule. The management letter was practical and prioritized.”</p>
          <div class="text-light mt-3">Chair, Audit Committee</div>
        </article>
      </div>
      <div class="col-md-4">
        <article class="quote card p-4">
          <p>“Reliable partner for compliance and reporting. Responsive, meticulous, and easy to work with.”</p>
          <div class="text-light mt-3">MD, Manufacturing Group</div>
        </article>
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


<!-- CTA -->
<section class="section">
  <div class="container">
    <div class="cta card p-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <div>
        <h2>Ready to get started?</h2>
        <p class="text-light">Share your current needs and timeline—our team will propose a clear scope, fees, and deliverables.</p>
      </div>
      <div>
        <a class="btn btn-primary" href="#contact">Request a proposal</a>
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