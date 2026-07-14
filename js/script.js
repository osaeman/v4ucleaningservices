'use strict';

    /* -------------------------------------------
       HEADER: scroll effect
    ------------------------------------------- */
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 60);
    }, { passive: true });


    /* -------------------------------------------
       MOBILE MENU
    ------------------------------------------- */
    const burger     = document.getElementById('burger');
    const mobileMenu = document.getElementById('mobileMenu');

    burger.addEventListener('click', () => {
      const open = mobileMenu.classList.toggle('open');
      burger.classList.toggle('open', open);
      burger.setAttribute('aria-expanded', open);
    });

    document.querySelectorAll('[data-mobile-link]').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('open');
        burger.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      });
    });


    /* -------------------------------------------
       HERO SLIDER
    ------------------------------------------- */
    const slides    = document.querySelectorAll('.hero__slide');
    const dots      = document.querySelectorAll('.hero__dot');
    let   current   = 0;
    let   sliderInt = null;

    function loadSlideBackground(slide) {
      if (!slide || slide.dataset.bgLoaded === 'true') return;
      const source = window.matchMedia('(max-width: 640px)').matches
        ? slide.dataset.bgMobile
        : slide.dataset.bgDesktop;
      if (source) {
        slide.style.backgroundImage = `url("${source}")`;
        slide.dataset.bgLoaded = 'true';
      }
    }

    function goToSlide(idx) {
      slides[current].classList.remove('active');
      dots[current].classList.remove('active');
      current = (idx + slides.length) % slides.length;
      loadSlideBackground(slides[current]);
      slides[current].classList.add('active');
      dots[current].classList.add('active');
    }

    function nextSlide() { goToSlide(current + 1); }

    sliderInt = setInterval(nextSlide, 5500);

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        clearInterval(sliderInt);
        goToSlide(parseInt(dot.dataset.slide));
        sliderInt = setInterval(nextSlide, 5500);
      });
    });

    // Keep later hero slides out of the critical loading path.
    window.addEventListener('load', () => {
      const loadRemainingSlides = () => slides.forEach(loadSlideBackground);
      if ('requestIdleCallback' in window) {
        requestIdleCallback(loadRemainingSlides, { timeout: 2500 });
      } else {
        setTimeout(loadRemainingSlides, 1200);
      }
    }, { once: true });

    // Load the quote background only when the section approaches the viewport.
    const quoteSection = document.querySelector('.quote');
    if (quoteSection) {
      const quoteBackgroundObserver = new IntersectionObserver((entries, obs) => {
        if (entries.some(entry => entry.isIntersecting)) {
          quoteSection.classList.add('bg-loaded');
          obs.disconnect();
        }
      }, { rootMargin: '600px 0px' });
      quoteBackgroundObserver.observe(quoteSection);
    }


    /* -------------------------------------------
       FADE-IN ON SCROLL
    ------------------------------------------- */
    const fadeEls = document.querySelectorAll('.fade-up');

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          // Stagger cards slightly
          const delay = entry.target.closest('.services__grid')
            ? Array.from(entry.target.parentElement.children).indexOf(entry.target) * 60
            : 0;
          setTimeout(() => {
            entry.target.classList.add('visible');
          }, delay);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    fadeEls.forEach(el => observer.observe(el));


    /* -------------------------------------------
       BACK TO TOP
    ------------------------------------------- */
    const backTop = document.getElementById('backTop');
    window.addEventListener('scroll', () => {
      backTop.classList.toggle('visible', window.scrollY > 500);
    }, { passive: true });
    backTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });


    /* -------------------------------------------
       QUOTE FORM — AJAX SUBMIT
    ------------------------------------------- */
    const quoteForm  = document.getElementById('quoteForm');
    const submitBtn  = document.getElementById('submitBtn');
    const spinner    = document.getElementById('spinner');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const formSuccess= document.getElementById('formSuccess');
    const formError  = document.getElementById('formError');

    function setLoading(state) {
      submitBtn.disabled = state;
      spinner.style.display    = state ? 'block' : 'none';
      submitIcon.style.display = state ? 'none'  : 'inline';
      submitText.textContent   = state ? 'Sending…' : 'Request a Quote';
    }

    quoteForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      formSuccess.style.display = 'none';
      formError.style.display   = 'none';

      // Basic client-side validation
      const required = quoteForm.querySelectorAll('[required]');
      let valid = true;
      required.forEach(field => {
        if (!field.value.trim()) {
          valid = false;
          field.style.borderColor = '#ff6b7a';
          field.addEventListener('input', () => {
            field.style.borderColor = '';
          }, { once: true });
        }
      });
      if (!valid) return;

      setLoading(true);

      const formData = new FormData(quoteForm);

      try {
        const response = await fetch('mailer.php', {
          method: 'POST',
          body: formData
        });

        if (response.ok) {
          const text = await response.text();
          if (text.trim() === 'success') {
            formSuccess.style.display = 'block';
            quoteForm.reset();
          } else {
            formError.style.display = 'block';
          }
        } else {
          formError.style.display = 'block';
        }
      } catch (err) {
        formError.style.display = 'block';
      } finally {
        setLoading(false);
      }
    });
