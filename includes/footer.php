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

<!-- Success Modal -->
<div class="modal fade" id="enquirySuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="modal-title mb-3">Thank You!</h4>
                <p class="mb-4">Your enquiry has been submitted successfully. We'll contact you soon.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="enquiryErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="fas fa-exclamation-circle text-danger" style="font-size: 4rem;"></i>
                </div>
                <h4 class="modal-title mb-3">Oops!</h4>
                <p class="mb-4 error-message"></p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Try Again</button>
            </div>
        </div>
    </div>
</div>

<!-- Add to your HTML head section -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<footer id="contact">
  <div class="container footer-grid lead">
    <div>
      <a class="brand" href="#">OGMBC Consultants</a>
      <p class="lead">
        This company was founded with the mission to provide clarity, compliance, and confidence 
        to businesses navigating today’s financial and regulatory landscapes. With years of 
        expertise in audit, tax, and advisory, our professionals bring industry insight, 
        technical know-how, and a client-first approach to every engagement.
      </p>
    </div>

    <div>
      <h4>Quick Links</h4>
      <a href="blog.php">Blog</a><br>
      <a href="contact.php">Contact</a><br>
      <a href="uae-bussiness-formation.php">UAE Company Formation</a><br>
      <a href="usa-company-formation.php">USA Company Formation</a><br>
      <a href="bookkeeping.php">Bookkeeping</a><br>
      <a href="audit-&-audit-support.php">Audit & Audit Support</a><br>
      <a href="bank-account-opening.php">Bank Account Opening</a>

    </div>

    <div>
      <h4>Contact</h4>
      <p>Email: info@ogmbc.ae</p>
      <p>Phone: +971 50 292 3136</p>
      <p>Address: Office No. A07, 18th Floor, The Regal Tower, Business Bay, Dubai UAE. P.O. Box 33418</p>

      <!-- Social Media -->
      <div class="social-links mt-3">
        <a href="https://www.linkedin.com/company/o-g-m-holding-co-ltd/" target="_blank" aria-label="LinkedIn">
          <i class="bi bi-linkedin"></i>
        </a>
        <a href="https://www.facebook.com/share/16iF6LSSwc/?mibextid=wwXIfr" target="_blank" aria-label="Facebook">
          <i class="bi bi-facebook"></i>
        </a>
         <a href="https://www.instagram.com/ogmconsultants_?igsh=cDd2NDExbWp3eQ==" target="_blank" aria-label="Instagram">
          <i class="bi bi-instagram"></i>
        </a>
        <a href="#" target="_blank" aria-label="Twitter">
          <i class="bi bi-twitter"></i>
        </a>
      </div>
    </div>
  </div>

  <p style="text-align:center; margin-top:2rem; color:var(--muted); font-size:.85rem;">
    © 2025 OGMBC Consultants. All rights reserved.
  </p>
</footer>

<!-- Bootstrap icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="resources/js/main.js" defer></script>
<script>
  // Back to top button functionality
  document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.querySelector('.back-to-top');
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    // Smooth scroll to top
    backToTopButton.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // WhatsApp button click tracking (optional)
    const whatsappBtn = document.querySelector('.whatsapp-btn');
    whatsappBtn.addEventListener('click', function() {
        // You can add analytics tracking here
        console.log('WhatsApp button clicked');
    });
});
</script>
</body>
</html>
