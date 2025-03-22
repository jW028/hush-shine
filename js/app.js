$(() => {
    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });
});

document.addEventListener('DOMContentLoaded', function() {

    AOS.init({
        duration: 800,
        once: true,
        offset: 100
      });
    // Index JS
    let counter = 1;
    let interval = setInterval(autoSlide, 3000);

    function autoSlide() {
        document.getElementById('radio' + counter).checked = true;
        counter = counter === 4 ? 1 : counter + 1;
    }

    document.querySelectorAll('.manual-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            clearInterval(interval);
            counter = parseInt(btn.getAttribute('for').replace('radio', ''));
            interval = setInterval(autoSlide, 3000);
        });
    });

  class SimpleCarousel {
      constructor() {
        this.slides = document.querySelectorAll('.hover-slide');
        this.textItems = document.querySelectorAll('.text-item');
        this.currentIndex = 0;
        this.autoPlayInterval = null;
        this.init();
      }
      
      init() {
        this.startAutoPlay();
        
        this.textItems.forEach(item => {
          item.addEventListener('mouseenter', (e) => {
            const index = parseInt(e.currentTarget.dataset.index);
            this.goTo(index);
            this.stopAutoPlay(); 
          });
          
          item.addEventListener('mouseleave', () => {
            this.startAutoPlay(); 
          });
        });
      }
      
      goTo(index) {
        this.currentIndex = index;
        
        this.slides.forEach(slide => slide.classList.remove('active'));
        this.slides[this.currentIndex].classList.add('active');
        
        this.textItems.forEach(item => item.classList.remove('active'));
        this.textItems[this.currentIndex].classList.add('active');
      }
      
      next() {
        this.goTo((this.currentIndex + 1) % this.slides.length);
      }
      
      startAutoPlay() {
        if (this.autoPlayInterval) this.stopAutoPlay();
        this.autoPlayInterval = setInterval(() => this.next(), 3000);
      }
      
      stopAutoPlay() {
        if (this.autoPlayInterval) {
          clearInterval(this.autoPlayInterval);
          this.autoPlayInterval = null;
        }
      }
    }
    
    new SimpleCarousel();

    // Get all form inputs
    const inputs = document.querySelectorAll('.form-input');
    
    // Function to check if input has value and update label accordingly
    function checkInputValue(input) {
        // Find the label using the input's id
        const label = document.querySelector('label[for="' + input.id + '"]');
        
        // Make sure we found a label
        if (label) {
            if (input.value.trim() !== '' || document.activeElement === input) {
                // Keep label up if input has value OR input is currently focused
                label.classList.add('active');
            } else {
                // Only move label down if input is empty AND not focused
                label.classList.remove('active');
            }
        }
    }
    
    // Add event listeners to each input
    inputs.forEach(function(input) {
        // Check inputs on page load (for prefilled values)
        checkInputValue(input);
        
        // Check on input change
        input.addEventListener('input', function() {
            checkInputValue(this);
        });
        
        // Add focus handler
        input.addEventListener('focus', function() {
            const label = document.querySelector('label[for="' + this.id + '"]');
            if (label) {
                label.classList.add('active');
            }
        });
        
        // Only check on blur if the field is actually empty
        input.addEventListener('blur', function() {
            const label = document.querySelector('label[for="' + this.id + '"]');
            if (label && this.value.trim() === '') {
                label.classList.remove('active');
            }
        });
    });
});

$(document).ready(function(){
    $(".dropdown-form").hide(); // Hide the form by default

    $(".toggle-form").click(function(event){
        event.preventDefault(); // Prevent page reload
        $(".dropdown-form").slideToggle(); // Toggle dropdown with animation
    });
});