// Smooth scrolling and active section detection
(function() {
    'use strict';

    // Get all sections and nav links
    const sections = document.querySelectorAll('section[id], main[id]');
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const logoLink = document.getElementById('logo-link');
    
    // Track current visible section
    let currentSection = 'home';
    
    // Function to get the currently visible section
    function getCurrentSection() {
        const scrollPosition = window.scrollY + 100; // Offset for navbar
        
        for (let i = sections.length - 1; i >= 0; i--) {
            const section = sections[i];
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                return section.id || 'home';
            }
        }
        
        // Default to home if at top
        if (window.scrollY < 100) {
            return 'home';
        }
        
        return currentSection; // Keep last known section
    }
    
    // Function to update active nav link
    function updateActiveNav(sectionId) {
        navLinks.forEach(link => {
            if (link.getAttribute('data-section') === sectionId) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    // Function to scroll to section
    function scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    // Handle nav link clicks
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            currentSection = sectionId;
            scrollToSection(sectionId);
            updateActiveNav(sectionId);
        });
    });
    
    // Handle logo click - scroll to current section
    if (logoLink) {
        logoLink.addEventListener('click', function(e) {
            e.preventDefault();
            currentSection = getCurrentSection();
            scrollToSection(currentSection);
        });
    }
    
    // Update active nav on scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            currentSection = getCurrentSection();
            updateActiveNav(currentSection);
        }, 100);
    });
    
    // Initial active nav update
    updateActiveNav(getCurrentSection());
    
})();

