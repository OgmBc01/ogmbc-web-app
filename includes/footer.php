<!-- Back to Top Button -->
<a href="#" class="back-to-top btn btn-primary rounded-circle">
    <i class="bi bi-arrow-up"></i>
</a>

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
        <a href="https://facebook.com" target="_blank" aria-label="Facebook">
          <i class="bi bi-facebook"></i>
        </a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter">
          <i class="bi bi-twitter"></i>
        </a>
        <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn">
          <i class="bi bi-linkedin"></i>
        </a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram">
          <i class="bi bi-instagram"></i>
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
