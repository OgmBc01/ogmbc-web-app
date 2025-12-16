<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

  <!-- Hero Section -->
  <section class="about-hero d-flex align-items-center text-center text-white">
    <div class="container">
      <h1 class="display-4 fw-bold">Our Team</h1>
      <p class="lead"></p>

      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb justify-content-center">
          <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active text-white" aria-current="page">About Us</li>
          <li class="breadcrumb-item active text-white" aria-current="page">Our Team</li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- Our Team -->
  <section class="section team-section">
    <div class="container">
      <!-- Section Header -->
      <div class="section-header text-center mb-5">
        <h2 class="section-title mb-3" style="color: var(--surface);">Meet Our Team</h2>
        <p class="section-description mx-auto" style="color: #4a5568; max-width: 600px;">
          Our strength lies in our people—qualified, experienced, and committed to your success. 
          Each team member brings specialized expertise to deliver exceptional results.
        </p>
      </div>

      <!-- Founder Card (Highlighted) -->
      <div class="row justify-content-center mb-5">
        <div class="col-12">
          <div class="founder-card card border-0 shadow-lg overflow-hidden" style="background: var(--surface);">
            <div class="row g-0">
              <div class="col-lg-4">
                <div class="founder-image-wrapper position-relative">
                  <img src="resources/img/team/usman.png" class="img-fluid w-100" alt="Mr. Odai Tom" style="height: auto; width:100%; object-fit:contain;">
                  <div class="founder-badge">Founder & CEO</div>
                </div>
              </div>
              <div class="col-lg-8 d-flex align-items-center">
                <div class="card-body p-4 p-lg-5">
                  <h3 class="card-title mb-2 text-white" style="text-align: center;">Mr. Odai Tom</h3>
                  <p class="card-text text-secondary mb-4 fw-semibold">Founder & Group CEO</p>
                  <p class="card-text text-light opacity-75 mb-4">
                    With over 11 years of experience in auditing, accounting, IFRS advisory, and global business formation, 
                    Mr. Odai Tom leads our multi-jurisdictional advisory group with integrity and innovation.
                  </p>
                  <div class="expertise-tags">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2 mb-2">Audit & Assurance</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2 mb-2">IFRS Advisory</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2 mb-2">Global Business</span>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 me-2 mb-2">Tax Strategy</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Members Grid -->
      <div class="row g-4 g-lg-5">
        <!-- Team Member 1 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/madan.jpg" class="card-img-top w-100" alt="CA. Mr. Madan Shah" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">CA. Mr. Madan Shah</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Director</p>
              <p class="team-expertise text-muted small">Chartered Accountant with 25+ years experience in auditing, taxation, and strategic advisory.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-award me-2"></i>UAE Registered Auditor</small>
                <small class="text-muted d-block"><i class="bi bi-building me-2"></i></small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 2 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/sudeep.jpg" class="card-img-top w-100" alt="CA. Mr. Sudeep" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">CA. Mr. Sudeep</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Audit Manager</p>
              <p class="team-expertise text-muted small">Specialized in financial audits, compliance reviews, and risk assessment for diverse industries.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-clipboard-check me-2"></i>Financial Audits</small>
                <small class="text-muted d-block"><i class="bi bi-shield-check me-2"></i>Risk Management</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 3 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/wajdi.jpg" class="card-img-top w-100" alt="Dr. Wajdi" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">Dr. Wajdi</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Business Consultant</p>
              <p class="team-expertise text-muted small">Strategic business planning, market analysis, and growth strategy development for global clients.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-graph-up me-2"></i>Growth Strategy</small>
                <small class="text-muted d-block"><i class="bi bi-globe me-2"></i>International Markets</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 4 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/soniya.jpg" class="card-img-top w-100" alt="Soniya Samuel" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">Soniya Samuel</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Senior Auditor</p>
              <p class="team-expertise text-muted small">Expert in internal controls, process optimization, and regulatory compliance across sectors.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-gear me-2"></i>Process Optimization</small>
                <small class="text-muted d-block"><i class="bi bi-file-earmark-text me-2"></i>Compliance</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 5 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/hussam.jpg" class="card-img-top w-100" alt="Hussam Abbass" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">Hussam Abbass</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Accountant & Tax Specialist</p>
              <p class="team-expertise text-muted small">Specialized in UAE tax regulations, VAT compliance, and corporate tax planning strategies.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-calculator me-2"></i>Tax Planning</small>
                <small class="text-muted d-block"><i class="bi bi-file-text me-2"></i>VAT Compliance</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 6 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/hakeem.jpg" class="card-img-top w-100" alt="Hakeem Lucas" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">Hakeem Lucas</h5>
              <p class="team-position text-secondary fw-semibold mb-3">Business Developer</p>
              <p class="team-expertise text-muted small">Focused on client acquisition, partnership development, and market expansion strategies.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-handshake me-2"></i>Partnerships</small>
                <small class="text-muted d-block"><i class="bi bi-graph-up-arrow me-2"></i>Business Growth</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Team Member 7 -->
        <div class="col-sm-6 col-lg-3">
          <div class="team-member-card card border-0 h-100 shadow-sm">
            <div class="team-image-wrapper position-relative">
              <img src="resources/img/team/ansam.jpg" class="card-img-top w-100" alt="Ansam" style="height: 280px; object-fit: contain;">
              <div class="social-overlay">
                <div class="social-icons">
                  <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                  <a href="#" class="social-icon"><i class="bi bi-envelope"></i></a>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <h5 class="team-name mb-2" style="color: var(--surface); text-align: center;">Ansam</h5>
              <p class="team-position text-secondary fw-semibold mb-3">PRO Specialist</p>
              <p class="team-expertise text-muted small">Expert in UAE government relations, document processing, and regulatory liaison services.</p>
              <div class="team-divider"></div>
              <div class="team-contact mt-3">
                <small class="text-muted d-block"><i class="bi bi-building me-2"></i>Government Liaison</small>
                <small class="text-muted d-block"><i class="bi bi-file-earmark me-2"></i>Document Processing</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center mt-5 pt-3">
        <p class="mb-4" style="color: #4a5568;">Ready to work with our expert team?</p>
        <a href="contact.php" class="btn btn-primary px-5 py-3" style="background: var(--secondary); border-color: var(--secondary);">
          <i class="bi bi-arrow-right me-2"></i>Get in Touch
        </a>
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
