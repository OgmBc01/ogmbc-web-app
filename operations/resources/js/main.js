// Copied from main resources/js/main.js for operations compatibility
// ================================
// Fix Bootstrap dropdown-submenu for touch/click
// ================================
document.addEventListener("DOMContentLoaded", function () {
  // Target only submenu toggles
  document.querySelectorAll(".dropdown-submenu > .dropdown-toggle").forEach(function (el) {
    el.addEventListener("click", function (e) {
      e.preventDefault();  // stop link navigation
      e.stopPropagation(); // stop Bootstrap from closing parent

      // close any other open submenus inside the same parent
      let parentMenu = this.closest(".dropdown-menu");
      parentMenu.querySelectorAll(".dropdown-menu.show").forEach(function (submenu) {
        if (submenu !== el.nextElementSibling) {
          submenu.classList.remove("show");
        }
      });

      // toggle the submenu
      let submenu = this.nextElementSibling;
      if (submenu) {
        submenu.classList.toggle("show");
      }
    });
  });

  // Close submenus when main dropdown closes
  document.querySelectorAll(".dropdown").forEach(function (dd) {
    dd.addEventListener("hide.bs.dropdown", function () {
      this.querySelectorAll(".dropdown-menu.show").forEach(function (submenu) {
        submenu.classList.remove("show");
      });
    });
  });
});
// ================================
// Script.js — Navbar + Carousel
// ================================
document.addEventListener("DOMContentLoaded", () => {
  // Navbar toggle
  const menuToggle = document.getElementById("menu-toggle");
  const menu = document.getElementById("menu");

  if (menuToggle && menu) {
    menuToggle.addEventListener("click", () => {
      menu.classList.toggle("show");
      menuToggle.classList.toggle("open");
    });

    // Hamburger animation
    menuToggle.addEventListener("click", () => {
      const spans = menuToggle.querySelectorAll("span");
      spans[0].classList.toggle("rotate1");
      spans[1].classList.toggle("fade");
      spans[2].classList.toggle("rotate2");
    });
  }
});

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
