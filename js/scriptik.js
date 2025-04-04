document.addEventListener('DOMContentLoaded', () => {
  const hamburger = document.querySelector('.hamburger-menu');
  const mobileNav = document.querySelector('.mobile-nav');
  const overlay = document.querySelector('.overlay');
  const closeMenuBtn = document.querySelector('.close-menu');

  function openMenu() {
    mobileNav.classList.add('active');
    overlay.classList.add('active');
    hamburger.classList.add('active');
  }

  function closeMenu() {
    mobileNav.classList.remove('active');
    overlay.classList.remove('active');
    hamburger.classList.remove('active');
  }

  hamburger.addEventListener('click', openMenu);
  closeMenuBtn.addEventListener('click', closeMenu);
  overlay.addEventListener('click', closeMenu);

  // Добавляем закрытие меню при клике на любой пункт
  document.querySelectorAll('.mobile-nav ul li a').forEach(link => {
    link.addEventListener('click', closeMenu);
  });
});





document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger-menu');
    const navMenu = document.querySelector('nav ul');
    const header = document.querySelector('header');
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('mobile-menu');
        header.classList.toggle('menu-open'); 
    });

    const navLinks = document.querySelectorAll('nav ul li a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('mobile-menu');
            header.classList.remove('menu-open');
        });
    });
});




document.getElementById('copy-email-btn').addEventListener('click', function() {
  const btn = this;
  navigator.clipboard.writeText('mail@it.amurobl.ru').then(() => {
    btn.textContent = 'СКОПИРОВАНО!';
    btn.style.backgroundColor = "#fff";
    btn.style.boxShadow = "inset 0 0 0 2px #e0e0e0";
    setTimeout(() => {
      btn.textContent = 'СКОПИРОВАТЬ';
      btn.style.backgroundColor = "#e0e0e0";
      btn.style.boxShadow = "none";
    }, 2000);
  }).catch(function(err) {
    console.error('Ошибка копирования: ', err);
  });
});



$(document).ready(function() {
    $('a[href^="#"], .btn-order, .smooth-scroll, .btn-more').on('click', function(e) {
        e.preventDefault();
        var target;
        if ($(this).hasClass('btn-order')) {
            target = $('#form');
        } else if ($(this).hasClass('btn-more')) {
            target = $('#services');
        } else {
            target = $(this.hash);
        }
        
		if (target.length) {
            var offset;

            if (target.is('#form')) {
                offset = 150;
            } else if (target.is('#contact')) {
                offset = 0;
            } else {
                offset = 50;
            }

            $('html, body').animate({
                scrollTop: target.offset().top - offset
            }, 800, 'easeInOutCubic');
        }
    });
    jQuery.easing.easeInOutCubic = function (x, t, b, c, d) {
        if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
        return c / 2 * ((t -= 2) * t * t + 2) + b;
    };
});
