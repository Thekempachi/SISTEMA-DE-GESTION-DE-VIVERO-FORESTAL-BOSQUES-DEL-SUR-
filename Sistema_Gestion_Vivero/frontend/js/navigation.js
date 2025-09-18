// Navigation handler for index.html
'use strict';

window.addEventListener('DOMContentLoaded', () => {
  // Wait a bit to ensure all elements are loaded
  setTimeout(() => {
    try {
      document.querySelectorAll('nav button').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('nav button').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('main section').forEach(s => s.classList.remove('active'));
          btn.classList.add('active');
          const targetId = btn.dataset.target;
          if (targetId) {
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
              targetSection.classList.add('active');
            }
          }
        });
      });
      
      // Activate first button by default
      const firstButton = document.querySelector('nav button');
      if (firstButton) {
        firstButton.click();
      }
    } catch (error) {
      console.error('Error initializing navigation:', error);
    }
  }, 100);
});
