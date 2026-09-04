(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initPreloader();
    initScrollProgress();
    initNavbar();
    initReveal();
    initTilt();
    initGallery();
    initTestimonials();
  });

  function initPreloader() {
    var el = document.querySelector('.preloader');
    if (!el) return;
    window.addEventListener('load', function () {
      setTimeout(function () { el.classList.add('is-hidden'); }, 300);
    });
  }

  function initScrollProgress() {
    var bar = document.querySelector('.scroll-progress');
    if (!bar) return;
    function update() {
      var h = document.documentElement;
      var scrolled = h.scrollTop;
      var max = h.scrollHeight - h.clientHeight;
      var ratio = max > 0 ? scrolled / max : 0;
      bar.style.transform = 'scaleX(' + ratio + ')';
    }
    document.addEventListener('scroll', update, { passive: true });
    update();
  }

  function initNavbar() {
    var nav = document.querySelector('.navbar');
    var toggle = document.querySelector('.nav-toggle');
    var links = document.querySelector('.nav-links');
    if (!nav) return;

    function onScroll() {
      nav.classList.toggle('is-scrolled', window.scrollY > 40);
    }
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (toggle && links) {
      toggle.addEventListener('click', function () {
        links.classList.toggle('is-open');
      });
      links.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () { links.classList.remove('is-open'); });
      });
    }

    var sections = Array.prototype.slice.call(document.querySelectorAll('section[id]'));
    var navLinks = Array.prototype.slice.call(document.querySelectorAll('.nav-links a[href^="#"]'));
    if (sections.length && navLinks.length && 'IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            navLinks.forEach(function (a) {
              a.classList.toggle('is-active', a.getAttribute('href') === '#' + entry.target.id);
            });
          }
        });
      }, { rootMargin: '-45% 0px -45% 0px' });
      sections.forEach(function (s) { observer.observe(s); });
    }
  }

  function initReveal() {
    var items = document.querySelectorAll('[data-reveal], [data-reveal-scale]');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var groups = {};
    items.forEach(function (el) {
      var group = el.getAttribute('data-reveal-group') || 'default';
      groups[group] = groups[group] || [];
      groups[group].push(el);
    });

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var group = el.getAttribute('data-reveal-group') || 'default';
        var index = groups[group].indexOf(el);
        setTimeout(function () { el.classList.add('is-visible'); }, Math.max(0, index) * 90);
        observer.unobserve(el);
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

    items.forEach(function (el) { observer.observe(el); });
  }

  function initTilt() {
    var cards = document.querySelectorAll('.tilt-card');
    if (!cards.length || window.matchMedia('(pointer: coarse)').matches) return;

    cards.forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = 'perspective(800px) rotateY(' + (x * 8) + 'deg) rotateX(' + (y * -8) + 'deg) translateY(-4px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = 'perspective(800px) rotateY(0) rotateX(0) translateY(0)';
      });
    });
  }

  function initGallery() {
    var items = document.querySelectorAll('[data-lightbox]');
    var lightbox = document.querySelector('.lightbox');
    if (!items.length || !lightbox) return;

    var img = lightbox.querySelector('img');
    var caption = lightbox.querySelector('.lightbox-caption');
    var closeBtn = lightbox.querySelector('.lightbox-close');

    function open(src, text) {
      img.src = src;
      caption.textContent = text || '';
      lightbox.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      lightbox.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    items.forEach(function (el) {
      el.addEventListener('click', function () {
        open(el.getAttribute('data-lightbox'), el.getAttribute('data-caption'));
      });
    });
    closeBtn.addEventListener('click', close);
    lightbox.addEventListener('click', function (e) { if (e.target === lightbox) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  function initTestimonials() {
    var root = document.querySelector('.testimonial-slider');
    if (!root) return;
    var track = root.querySelector('.testimonial-slides');
    var slides = Array.prototype.slice.call(root.querySelectorAll('.testimonial-slide'));
    var prev = root.querySelector('.testimonial-arrow--prev');
    var next = root.querySelector('.testimonial-arrow--next');
    var dotsWrap = root.querySelector('.testimonial-dots');
    if (!track || slides.length < 2) return;

    var index = 0;
    var dots = slides.map(function (_, i) {
      var b = document.createElement('button');
      b.className = 'testimonial-dot' + (i === 0 ? ' is-active' : '');
      b.type = 'button';
      b.addEventListener('click', function () { goTo(i); });
      dotsWrap.appendChild(b);
      return b;
    });

    function goTo(i) {
      index = (i + slides.length) % slides.length;
      track.style.transform = 'translateX(' + (index * 100) + '%)';
      dots.forEach(function (d, di) { d.classList.toggle('is-active', di === index); });
    }

    if (prev) prev.addEventListener('click', function () { goTo(index - 1); });
    if (next) next.addEventListener('click', function () { goTo(index + 1); });

    var timer = setInterval(function () { goTo(index + 1); }, 6000);
    root.addEventListener('mouseenter', function () { clearInterval(timer); });
    root.addEventListener('mouseleave', function () { timer = setInterval(function () { goTo(index + 1); }, 6000); });

    // RTL layout: slides are laid out right-to-left, so track moves in the same visual direction.
    track.style.direction = 'ltr';
  }
})();
