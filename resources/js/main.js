
// ================================
// Extra CSS classes for hamburger animation
// ================================
const style = document.createElement("style");
  style.textContent = `
    .menu-toggle.open span:nth-child(1){ transform: rotate(45deg) translateY(8px); }
    .menu-toggle.open span:nth-child(2){ opacity:0; }
    .menu-toggle.open span:nth-child(3){ transform: rotate(-45deg) translateY(-8px); }
    .menu-toggle span { transition: all .3s ease; }`;
  document.head.appendChild(style);


// JS: small enhancements — we will add interactions per your screenshots
document.addEventListener("DOMContentLoaded", function() {
  const yearElement = document.getElementById('year');
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }

  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      const id = a.getAttribute('href');
      if(id.length > 1){
        const el = document.querySelector(id);
        if(el){ e.preventDefault(); el.scrollIntoView({behavior:'smooth', block:'start'}); }
      }
    });
  });

  // Optional: reveal on scroll (basic)
  const observer = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){ entry.target.animate([
        {opacity:0, transform:'translateY(12px)'},
        {opacity:1, transform:'translateY(0)'}
      ], {duration:500, easing:'ease-out', fill:'forwards'}); observer.unobserve(entry.target); }
    })
  }, { threshold: .12 });
  document.querySelectorAll('.service, .stat, .quote, .cta').forEach(el=>observer.observe(el));
});


// ================================
// Stats Counter
// ================================
document.addEventListener("DOMContentLoaded", () => {
  const counters = document.querySelectorAll(".num");
  let started = false;

  function animateCount(counter) {
    const target = counter.getAttribute("data-target");
    const isPercentage = target.includes("%");
    const isPlus = target.includes("+");
    const isGrade = target.toUpperCase().includes("A+");

    if (isGrade) {
      // Animate 0 → 100, then replace with "A+"
      let count = 0;
      const duration = 2000;
      const stepTime = 20;
      const increment = Math.ceil(100 / (duration / stepTime));

      const timer = setInterval(() => {
        count += increment;
        if (count >= 100) {
          clearInterval(timer);
          counter.textContent = "A+";
        } else {
          counter.textContent = count;
        }
      }, stepTime);
    } else {
      // Handle numbers with + or %
      const numericTarget = parseInt(target.replace(/\D/g, ""), 10);
      let count = 0;
      const duration = 2000;
      const stepTime = Math.max(Math.floor(duration / numericTarget), 20);

      const timer = setInterval(() => {
        count++;
        if (count >= numericTarget) {
          clearInterval(timer);
          counter.textContent = isPercentage
            ? numericTarget + "%"
            : numericTarget + (isPlus ? "+" : "");
        } else {
          counter.textContent = isPercentage
            ? count + "%"
            : count + (isPlus ? "+" : "");
        }
      }, stepTime);
    }
  }

  function checkScroll() {
    const section = document.querySelector("#stats");
    if (!section) return;
    const rect = section.getBoundingClientRect();

    if (!started && rect.top < window.innerHeight && rect.bottom > 0) {
      counters.forEach(animateCount);
      started = true;
    }
  }

  if (counters.length > 0) {
    window.addEventListener("scroll", checkScroll);
    // Initial check in case the section is already in view
    checkScroll();
  }
});


// ================================
// Satisfaction Card
// ================================
document.addEventListener("DOMContentLoaded", () => {
  const valueEl = document.getElementById("satisfactionValue");
  const barEl = document.getElementById("satisfactionBar");
  let animated = false;

  function animateSatisfaction() {
    if (animated || !valueEl || !barEl) return; // prevent retrigger
    animated = true;

    let current = 0;
    const target = 89; // final %
    const interval = setInterval(() => {
      current++;
      valueEl.textContent = current;
      if (barEl) barEl.style.width = current + "%";
      if (current >= target) clearInterval(interval);
    }, 30); // speed (30ms per step)
  }

  if (valueEl && barEl) {
    // Trigger when visible
    const observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting) animateSatisfaction();
    }, { threshold: 0.5 });

    const satisfactionCard = document.querySelector(".satisfaction-card");
    if (satisfactionCard) observer.observe(satisfactionCard);
  }
});


// ================================
// Back to top button functionality
// ================================
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.querySelector('.back-to-top');
    
    if (backToTopButton) {
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
    }
    
    // WhatsApp button click tracking (optional)
    const whatsappBtn = document.querySelector('.whatsapp-btn');
    if (whatsappBtn) {
        whatsappBtn.addEventListener('click', function() {
            // You can add analytics tracking here
            console.log('WhatsApp button clicked');
        });
    }
});


// ================================
// Automatically update the year
// ================================
  document.getElementById('current-year').textContent = new Date().getFullYear();
