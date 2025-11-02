<?php
include 'includes/database.php';
include 'includes/header-1.php'
?>

    <!-- Hero Section -->
    <section class="about-hero d-flex align-items-center text-center text-white" style="height: 50vh;">
    <div class="container">
        <h1 class="fw-bold">OGM Business Consultants Company Profile</h1>
        <p class="lead">Navigate through our carefully crafted company profile, decorated with our amazing services & solutions, essential for your businesses.</p>
    </section>

    <!-- Interactive Profile Viewer Section -->
    <section class="profile-viewer-section py-5">
    <div class="container">
        <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="profile-viewer-container">
                <!-- Download PDF Button -->
                <a href="resources/docs/OGMBC-Company-Profile-2025.pdf" class="btn btn-outline-primary ms-2" download>
                    <i class="bi bi-download me-1"></i> Download PDF
                </a>
            
                <!-- Profile Viewer -->
                <div class="profile-viewer-wrapper">
                    <div class="profile-book" id="profile-book">
                    <!-- Pages will be loaded dynamically via JavaScript -->
                    </div>
                </div>

                <!-- Viewer Controls -->
                <div class="viewer-controls d-flex justify-content-between align-items-center mb-4">
                    <div class="viewer-actions">
                    <button id="zoom-out" class="btn btn-outline-secondary me-2" title="Zoom Out">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <span id="zoom-level" class="me-2">100%</span>
                    <button id="zoom-in" class="btn btn-outline-secondary me-3" title="Zoom In">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                    
                    <button id="toggle-fullscreen" class="btn btn-outline-secondary me-3" title="Toggle Fullscreen">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    
                    <button id="print-profile" class="btn btn-outline-secondary" title="Print Profile">
                        <i class="bi bi-printer"></i>
                    </button>
                    </div>
                    
                    <div class="page-navigation">
                    <button id="first-page" class="btn btn-outline-secondary me-2" title="First Page">
                        <i class="bi bi-skip-start"></i>
                    </button>
                    <button id="prev-page" class="btn btn-outline-secondary me-2" title="Previous Page">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <span id="page-indicator" class="mx-2">Page <span id="current-page">1</span> of <span id="total-pages">16</span></span>
                    <button id="next-page" class="btn btn-outline-secondary me-2" title="Next Page">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <button id="last-page" class="btn btn-outline-secondary" title="Last Page">
                        <i class="bi bi-skip-end"></i>
                    </button>
                    </div>
                </div>
            
            <!-- Thumbnail Navigation -->
            <div class="thumbnail-navigation mt-4">
                <div class="thumbnails-container" id="thumbnails-container">
                <!-- Thumbnails will be generated dynamically -->
                </div>
            </div>
            </div>
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

<script>
// Configuration - Update these paths according to your file structure
const PAGE_CONFIG = {
  totalPages: 16,
  imageBasePath: 'resources/img/profile-pages/',
  imageFormat: 'jpg', // or 'png', 'webp'
  imageQuality: 'high' // for naming convention if you have different quality versions
};

document.addEventListener('DOMContentLoaded', function() {
  const profileBook = document.getElementById('profile-book');
  const currentPageEl = document.getElementById('current-page');
  const totalPagesEl = document.getElementById('total-pages');
  const zoomLevelEl = document.getElementById('zoom-level');
  const thumbnailsContainer = document.getElementById('thumbnails-container');
  
  let currentPage = 0;
  let zoomLevel = 1;
  let pages = [];
  
  // Initialize the viewer
  initViewer();
  
  async function initViewer() {
    totalPagesEl.textContent = PAGE_CONFIG.totalPages;
    await loadPages();
    createThumbnails();
    goToPage(0);
  }
  
  // Load all pages
  async function loadPages() {
    profileBook.innerHTML = '<div class="loading-spinner"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    for (let i = 0; i < PAGE_CONFIG.totalPages; i++) {
      const page = document.createElement('div');
      page.className = 'profile-page';
      page.dataset.page = i;
      
      const img = document.createElement('img');
      img.className = 'page-image';
      img.loading = 'lazy';
      img.alt = `Company Profile Page ${i + 1}`;
      
      // Construct image path - adjust naming convention as needed
      const pageNumber = (i + 1).toString().padStart(2, '0');
      img.src = `${PAGE_CONFIG.imageBasePath}page-${pageNumber}.${PAGE_CONFIG.imageFormat}`;
      
      // Add error handling for missing images
      img.onerror = function() {
        this.src = 'resources/img/placeholder-page.jpg';
        console.warn(`Failed to load page image: ${this.src}`);
      };
      
      page.appendChild(img);
      profileBook.appendChild(page);
      pages.push(page);
    }
    
    // Remove loading spinner
    const spinner = profileBook.querySelector('.loading-spinner');
    if (spinner) {
      spinner.remove();
    }
  }
  
  // Navigation functions
  function goToPage(pageIndex) {
    if (pageIndex < 0 || pageIndex >= PAGE_CONFIG.totalPages) return;
    
    // Update active page
    pages.forEach(page => page.classList.remove('active', 'prev'));
    if (pages[pageIndex]) {
      pages[pageIndex].classList.add('active');
    }
    
    // Set previous page for animation
    if (pageIndex > 0 && pages[pageIndex - 1]) {
      pages[pageIndex - 1].classList.add('prev');
    }
    
    currentPage = pageIndex;
    updatePageIndicator();
    updateThumbnails();
  }
  
  function nextPage() {
    if (currentPage < PAGE_CONFIG.totalPages - 1) {
      goToPage(currentPage + 1);
    }
  }
  
  function prevPage() {
    if (currentPage > 0) {
      goToPage(currentPage - 1);
    }
  }
  
  function firstPage() {
    goToPage(0);
  }
  
  function lastPage() {
    goToPage(PAGE_CONFIG.totalPages - 1);
  }
  
  function updatePageIndicator() {
    currentPageEl.textContent = currentPage + 1;
  }
  
  // Zoom functions
  function zoomIn() {
    if (zoomLevel < 2) {
      zoomLevel += 0.1;
      applyZoom();
    }
  }
  
  function zoomOut() {
    if (zoomLevel > 0.5) {
      zoomLevel -= 0.1;
      applyZoom();
    }
  }
  
  function applyZoom() {
    pages.forEach(page => {
      page.style.transform = `scale(${zoomLevel})`;
    });
    zoomLevelEl.textContent = Math.round(zoomLevel * 100) + '%';
  }
  
  // Thumbnail functions
  function createThumbnails() {
    thumbnailsContainer.innerHTML = '';
    
    for (let i = 0; i < PAGE_CONFIG.totalPages; i++) {
      const thumbnail = document.createElement('div');
      thumbnail.className = 'thumbnail';
      if (i === currentPage) {
        thumbnail.classList.add('active');
      }
      
      const thumbnailImg = document.createElement('img');
      const pageNumber = (i + 1).toString().padStart(2, '0');
      thumbnailImg.src = `${PAGE_CONFIG.imageBasePath}thumb-page-${pageNumber}.${PAGE_CONFIG.imageFormat}`;
      thumbnailImg.alt = `Page ${i + 1}`;
      thumbnailImg.loading = 'lazy';
      
      // Fallback to main image if thumbnail doesn't exist
      thumbnailImg.onerror = function() {
        this.src = `${PAGE_CONFIG.imageBasePath}page-${pageNumber}.${PAGE_CONFIG.imageFormat}`;
      };
      
      thumbnail.appendChild(thumbnailImg);
      thumbnail.addEventListener('click', () => goToPage(i));
      
      thumbnailsContainer.appendChild(thumbnail);
    }
  }
  
  function updateThumbnails() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, index) => {
      if (index === currentPage) {
        thumb.classList.add('active');
      } else {
        thumb.classList.remove('active');
      }
    });
  }
  
  // Fullscreen function
  function toggleFullscreen() {
    const viewerContainer = document.querySelector('.profile-viewer-container');
    
    if (!document.fullscreenElement) {
      if (viewerContainer.requestFullscreen) {
        viewerContainer.requestFullscreen();
      } else if (viewerContainer.webkitRequestFullscreen) {
        viewerContainer.webkitRequestFullscreen();
      } else if (viewerContainer.msRequestFullscreen) {
        viewerContainer.msRequestFullscreen();
      }
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      }
    }
  }
  
  // Print function
  function printProfile() {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
      <html>
        <head>
          <title>OGMBC Company Profile</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .page { margin-bottom: 30px; break-after: page; }
            img { max-width: 100%; height: auto; }
          </style>
        </head>
        <body>
          <h1>OGMBC Company Profile</h1>
          ${pages.map((page, index) => `
            <div class="page">
              <h3>Page ${index + 1}</h3>
              <img src="${page.querySelector('img').src}" alt="Page ${index + 1}">
            </div>
          `).join('')}
        </body>
      </html>
    `);
    printWindow.document.close();
    printWindow.print();
  }
  
  // Event listeners
  document.getElementById('next-page').addEventListener('click', nextPage);
  document.getElementById('prev-page').addEventListener('click', prevPage);
  document.getElementById('first-page').addEventListener('click', firstPage);
  document.getElementById('last-page').addEventListener('click', lastPage);
  document.getElementById('zoom-in').addEventListener('click', zoomIn);
  document.getElementById('zoom-out').addEventListener('click', zoomOut);
  document.getElementById('toggle-fullscreen').addEventListener('click', toggleFullscreen);
  document.getElementById('print-profile').addEventListener('click', printProfile);
  
  // Keyboard navigation
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight' || e.key === ' ') {
      nextPage();
    } else if (e.key === 'ArrowLeft') {
      prevPage();
    } else if (e.key === 'Home') {
      firstPage();
    } else if (e.key === 'End') {
      lastPage();
    }
  });
  
  // Swipe navigation for touch devices
  let touchStartX = 0;
  let touchEndX = 0;
  
  profileBook.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
  });
  
  profileBook.addEventListener('touchend', (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  });
  
  function handleSwipe() {
    const swipeThreshold = 50;
    
    if (touchEndX < touchStartX - swipeThreshold) {
      nextPage();
    } else if (touchEndX > touchStartX + swipeThreshold) {
      prevPage();
    }
  }
});
</script>

<!-- Footer -->
<?php
include 'includes/footer.php'
?>