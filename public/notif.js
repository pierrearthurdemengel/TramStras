//  Sticky-MENU 

document.addEventListener("DOMContentLoaded", function() {
    (function() {
  
            // Si la largeur de l'écran est inférieure à 850px, désactivez cette fonction.
            if (window.innerWidth <= 850) {
              console.log("La largeur de l'écran est inférieure à 850px, donc la fonction a été désactivée.");
              return;
          }
        
        let lastScrollTop = 0;
        const navbar = document.getElementById('navbar');
        const header = document.getElementById('header');
        const menu = document.getElementById('menu');
        const menuIcon = document.getElementById('menu-icon');
  
        if (navbar && menu && menuIcon) {
            console.log("La navbar, l'en-tête, le menu et l'icône de menu ont été trouvés");
        } else {
            console.log("Un ou plusieurs éléments n'ont PAS été trouvés");
            return;
        }
  
        navbar.addEventListener('mouseover', function() {
            navbar.style.top = '0';
        });
  
        navbar.addEventListener('mouseout', function() {
            navbar.style.top = '-6%';
        });
  
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
  
            if (scrollTop > lastScrollTop) {
                navbar.style.top = '-100%';
                menu.style.display = "none";
  
                if (window.innerWidth <= 850) {
                    menu.style.left = '-100%';
                    menuIcon.setAttribute("aria-expanded", "false");
                }
  
            } else {
                navbar.style.top = '0';
                menu.style.display = "";
                if (window.innerWidth <= 850) {
                    menu.style.left = "0";
                    menuIcon.setAttribute("aria-expanded", "true");
                }
            }
            
            lastScrollTop = scrollTop;
        });
  
    })(); 
  });
  
      
      
  // JS parallax Footer
  
  window.addEventListener('scroll', function() {
      var parallax = document.querySelector('.parallax-footer');
      var scrollPosition = window.scrollY;
  
      parallax.style.backgroundPosition = 'center ' + (scrollPosition * 0.1) + 'px';
  });
  
  // MENU CARDS
  
  document.querySelectorAll('.gravityButton').forEach(btn => {
    
      btn.addEventListener('mousemove', (e) => {
        
        const rect = btn.getBoundingClientRect();    
        const h = rect.width / 2;
        
        const x = e.clientX - rect.left - h;
        const y = e.clientY - rect.top - h;
    
        const r1 = Math.sqrt(x*x+y*y);
        const r2 = (1 - (r1 / h)) * r1;
    
        const angle = Math.atan2(y, x);
        const tx = Math.round(Math.cos(angle) * r2 * 100) / 100;
        const ty = Math.round(Math.sin(angle) * r2 * 100) / 100;
        
        const op = (r2 / r1) + 0.25;
    
        btn.style.setProperty('--tx', `${tx}px`);
        btn.style.setProperty('--ty', `${ty}px`);
        btn.style.setProperty('--opacity', `${op}`);
      });
    
      btn.addEventListener('mouseleave', (e) => {
        btn.style.setProperty('--tx', '0px');
        btn.style.setProperty('--ty', '0px');
        btn.style.setProperty('--opacity', `${0.25}`);
      });
    })
  
  //   BURGER MENU
  
  document.addEventListener("DOMContentLoaded", function() {
    var menuIcon = document.getElementById('menu-icon');
    var menu = document.getElementById('menu');
    var backgroundBlur = document.getElementById('background-blur');
    
    menuIcon.addEventListener('click', function() {
        menuIcon.classList.toggle('active');
        menu.classList.toggle('open');
        backgroundBlur.classList.toggle('open');
  
        if (menu.classList.contains('open')) {
            backgroundBlur.style.display = 'block'; // Affichez le fond flouté
        } else {
            backgroundBlur.style.display = 'none'; // Cachez le fond flouté
        }
    });
  
    // Fermez le menu lorsque vous cliquez en dehors
    backgroundBlur.addEventListener('click', function() {
        menuIcon.classList.remove('active');
        menu.classList.remove('open');
        backgroundBlur.style.display = 'none'; // Cachez le fond flouté
    });
  });
  
  
  
  
  // STYLE INSCRIPTION 
  function darken(color, percentage) {
      const amount = (percentage / 100) * 255;
      let [r, g, b] = color.match(/\w\w/g).map((c) => parseInt(c, 16) - amount);
  
      return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
  }  