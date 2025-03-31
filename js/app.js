$(() => {
    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

    $('[data-get]').on('click', e => {
      e.preventDefault();
      const url = e.target.dataset.get;
      location = url || location;
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


  // JS for product details
  let modal = document.getElementById("product-modal");
  let modalImage = document.getElementById("modal-image");
  let modalName = document.getElementById("modal-name");
  let modalDesc = document.getElementById("modal-desc");
  let modalPrice = document.getElementById("modal-price");
  let closeBtn = document.querySelector(".close");
  // let cancelBtn = document.querySelector(".cancel");

  document.querySelectorAll(".product").forEach(product => {
    // Hover Effect on Product
    product.addEventListener("mouseenter", function () {
      this.classList.add("hovered");
    });
    product.addEventListener("mouseleave", function () {
      this.classList.remove("hovered");
    });
    

    // Event when user Click Product    WITHOUT DATABASE
    product.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent default link behavior

      // Get product details
      let name = this.querySelector(".prod-description p").textContent;
      let price = this.querySelector(".price").textContent;
      let imageSrc = this.querySelector(".product-image").src;

      // Update modal content
      modalName.textContent = name;
      modalDesc.textContent = "Product Description goes here.";
      modalPrice.textContent = price;
      modalImage.src = imageSrc;

      // Update the first preview image dynamically
      let previewImages = document.querySelectorAll(".preview");

      // Remove "active" class from all preview images
      document.querySelectorAll(".preview").forEach(img => img.classList.remove("active"));

      if (previewImages.length > 0) {
        previewImages[0].src = imageSrc;
        previewImages[0].setAttribute("onclick", `changeImage(this, '${imageSrc}')`);
        previewImages[0].classList.add("active"); // Ensure it's active
      }

      // Show the modal
      // modal.style.display = "block";
      modal.style.display = "flex";
      setTimeout(() => {
        modal.classList.add("show");
      }, 10); // Delay for CSS transition
    });

    console.log(product.dataset.catId); // Logs the cat_id (e.g., CT04, CT01)


/*
    // Event when user Click Product    WITH DATABASE
    product.addEventListener("click", function (event) {
      event.preventDefault(); // Prevent default link behavior

      let name = this.dataset.name;
      let desc = this.dataset.desc;
      let price = this.dataset.price;
      let imageSrc = this.dataset.image;
      // let imageSrc = this.dataset.image.startsWith("http") ? this.dataset.image : window.location.origin + "/" + this.dataset.image;

      modalName.textContent = name;
      modalDesc.textContent = desc;  // Now uses database description
      modalPrice.textContent = "RM " + price;
      modalImage.src = imageSrc;

      // Update the first preview image dynamically
      let previewImages = document.querySelectorAll(".preview");

      // Remove "active" class from all preview images
      document.querySelectorAll(".preview").forEach(img => img.classList.remove("active"));

      if (previewImages.length > 0) {
        previewImages[0].src = imageSrc;
        previewImages[0].setAttribute("onclick", `changeImage(this, '${imageSrc}')`);
        previewImages[0].classList.add("active"); // Ensure it's active
      }
      
      // Show the modal
      // modal.style.display = "block";
      modal.style.display = "flex";
      setTimeout(() => {
        modal.classList.add("show");
      }, 10); // Delay for CSS transition
    });
*/
  });

  // Close modal when clicking "X"
  closeBtn.addEventListener("click", function () {
    modal.classList.remove("show");
    setTimeout(() => {
      modal.style.display = "none";
    }, 300);
  });

  // Close modal when clicking outside of it
  window.addEventListener("click", function (event) {
    if (event.target == modal) {
      modal.classList.remove("show");
      this.setTimeout(() => {
        modal.style.display = "none";
      }, 300);
    }
  });
  
  window.addEventListener("load", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get("category");

    if (category) {
      const section = document.getElementById("cat-" + category);
      setTimeout(() => {
        if (section) {
          section.scrollIntoView({ behavior: "smooth" });
        }
      }, 400);
      history.replaceState(null, null, "products.php"); // Clean URL after scrolling
    }
  });
      
  document.querySelectorAll(".category-link").forEach(link => {
    link.addEventListener("click", function (event) {
      event.preventDefault(); // Stop the default link behavior (no page reload)

      let category = this.getAttribute("data-cat");
      let section = document.getElementById("cat-" + category);

      if (section) {
        section.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
    
});
document.addEventListener('DOMContentLoaded', function() {
  // Contact Us Banner Slider
  let items = document.querySelectorAll('.cont-slider .list .cont-item');
  let next = document.getElementById('next');
  let prev = document.getElementById('prev');
  let thumbnails = document.querySelectorAll('.thumbnail .cont-item');

  // config param
  let countItem = items.length;
  let itemActive = 0;
  // event next click
  next.onclick = function(){
      itemActive = itemActive + 1;
      if(itemActive >= countItem){
          itemActive = 0;
      }
      showSlider();
  }
  //event prev click
  prev.onclick = function(){
      itemActive = itemActive - 1;
      if(itemActive < 0){
          itemActive = countItem - 1;
      }
      showSlider();
  }
  // auto run slider
  let refreshInterval = setInterval(() => {
      next.click();
  }, 5000)
  function showSlider(){
      // remove item active old
      let itemActiveOld = document.querySelector('.cont-slider .list .cont-item.active');
      let thumbnailActiveOld = document.querySelector('.thumbnail .cont-item.active');
      itemActiveOld.classList.remove('active');
      thumbnailActiveOld.classList.remove('active');

      // active new item
      items[itemActive].classList.add('active');
      thumbnails[itemActive].classList.add('active');
      setPositionThumbnail();

      // clear auto time run slider
      clearInterval(refreshInterval);
      refreshInterval = setInterval(() => {
          next.click();
      }, 5000)
  }
  function setPositionThumbnail () {
      let thumbnailActive = document.querySelector('.thumbnail .cont-item.active');
      let rect = thumbnailActive.getBoundingClientRect();
      if (rect.left < 0 || rect.right > window.innerWidth) {
          thumbnailActive.scrollIntoView({ behavior: 'smooth', inline: 'nearest' });
      }
  }

  // click thumbnail
  thumbnails.forEach((thumbnail, index) => {
      thumbnail.addEventListener('click', () => {
          itemActive = index;
          showSlider();
      })
})
  // Contact Us FAQ
  document.querySelectorAll('.faq-question').forEach(item => {
      item.addEventListener('click', () => {
          const parent = item.parentElement;
          parent.classList.toggle('active');
      });
  });
});

// $(document).ready(function(){
//     $(".dropdown-form").hide(); // Hide the form by default

//     $(".toggle-form").click(function(event){
//         event.preventDefault(); // Prevent page reload
//         $(".dropdown-form").slideToggle(); // Toggle dropdown with animation
//     });
// });

// Change Preview Image in Products Details 
function changeImage(selectedImg, imageSrc) {
  let modalImage = document.getElementById("modal-image");

  // Add fade-out effect
  modalImage.style.opacity = "0";
  modalImage.style.transform = "scale(0.95)"; // Slight zoom-out effect

  setTimeout(() => {
      modalImage.src = imageSrc; // Change the image
      modalImage.style.opacity = "1"; // Fade-in effect
      modalImage.style.transform = "scale(1)"; // Return to normal size
  }, 150); // Match the transition duration

  // Remove "active" class from all previews
  document.querySelectorAll(".preview").forEach(img => img.classList.remove("active"));

  // Add "active" class to the clicked preview
  selectedImg.classList.add("active");
}

