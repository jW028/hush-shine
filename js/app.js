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
  let addToCartBtn = document.querySelector('.add-to-cart');
  let closeBtn = document.querySelector(".close");
  // let cancelBtn = document.querySelector(".cancel");
  let currentProduct = null;

  document.querySelectorAll(".product").forEach(product => {
    // Hover Effect on Product
    product.addEventListener("mouseenter", function () {
      this.classList.add("hovered");
    });
    product.addEventListener("mouseleave", function () {
      this.classList.remove("hovered");
    });
    

        // Event when user Click Product    WITH DATABASE
        product.addEventListener("click", function (event) {
          event.preventDefault(); // Prevent default link behavior

          let productId = this.dataset.id;

          currentProduct = {
              id: productId,
              name: this.dataset.name,
              desc: this.dataset.desc,
              price: this.dataset.price,
              image: this.dataset.image
          };

          // Update modal
          modalName.textContent = currentProduct.name;
          modalDesc.textContent = currentProduct.desc;
          modalPrice.textContent = 'RM ' + currentProduct.price;
          modalImage.src = '/images/prod_img/' + currentProduct.image;

          // Update the first preview image dynamically
          let previewImages = document.querySelectorAll(".preview");

          // Remove "active" class from all preview images
          document.querySelectorAll(".preview").forEach(img => img.classList.remove("active"));

          if (previewImages.length > 0) {
              previewImages[0].src = '/images/prod_img/' + currentProduct.image;
              previewImages[0].setAttribute("onclick", `changeImage(this, '/images/prod_img/${currentProduct.image}')`);
              previewImages[0].classList.add("active"); // Ensure it's active
          }
          
          // Show the modal
          // modal.style.display = "block";
          modal.style.display = "flex";
          setTimeout(() => {
              modal.classList.add("show");
          }, 10); // Delay for CSS transition
      });

  });

  function resetQuantity() {
      quantityInput.value = 1;
  }

  const quantityInput = document.getElementById('quantity');
  const minusBtn = document.querySelector('.qty-btn.minus');
  const plusBtn = document.querySelector('.qty-btn.plus');
  
  // Quantity buttons functionality
  minusBtn.addEventListener('click', function() {
      let value = parseInt(quantityInput.value);
      if (value > 1) {
          quantityInput.value = value - 1;
      }
  });
  
  plusBtn.addEventListener('click', function() {
      let value = parseInt(quantityInput.value);
      if (value < 99) {
          quantityInput.value = value + 1;
      }
  });
  
  // Ensure valid quantity input
  quantityInput.addEventListener('change', function() {
      let value = parseInt(this.value);
      if (isNaN(value) || value < 1) {
          this.value = 1;
      } else if (value > 99) {
          this.value = 99;
      }
  });

  addToCartBtn.addEventListener("click", function() {
      if (!currentProduct) {
          alert("Please select a product first");
          return;
      }
      
      const quantity = parseInt(document.getElementById('quantity').value) || 1;
      
      const btn = this;
      btn.disabled = true;
      btn.textContent = "Adding...";
      
      fetch(window.location.href, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
          },
          body: `product_id=${currentProduct.id}&quantity=${quantity}&action=add_to_cart`
      })    

      .then(response => response.json())
      .then(data => {
          if (data.success) {
              btn.textContent = "✓ Added!";
              // Close modal after delay
              setTimeout(() => {
                  modal.classList.remove("show");
                  setTimeout(() => {
                      modal.style.display = "none";
                      btn.textContent = "Add to Cart";
                      btn.disabled = false;
                  }, 300);
              }, 1000);
          } else {
              throw new Error(data.message || "Failed to add to cart");
          }
      })
      .catch(error => {
          console.error('Error:', error);
          alert(error.message);
          btn.textContent = "Add to Cart";
          btn.disabled = false;
      });
  });

  // Close modal when clicking "X"
  closeBtn.addEventListener("click", function () {
      modal.classList.remove("show");
      setTimeout(() => {
          modal.style.display = "none";
          resetQuantity();
      }, 300);    // Match transition duration
  });

  // Close modal when clicking outside of it
  window.addEventListener("click", function (event) {
      if (event.target == modal) {
          modal.classList.remove("show");
          this.setTimeout(() => {
              modal.style.display = "none";
              resetQuantity();
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

<<<<<<< HEAD
/* Shopping Cart */
// Calculate and update selected subtotal
function updateSelectedSubtotal() {
  let subtotal = 0;
  
  document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
      const row = checkbox.closest('tr');
      const totalText = row.querySelector('.item-total').textContent;
      const totalValue = parseFloat(totalText.replace('RM ', '').replace(',', ''));
      subtotal += totalValue;
  });
  
  document.getElementById('selected-subtotal').textContent = 'RM ' + subtotal.toFixed(2);
}

// Checkbox change handler
document.addEventListener('DOMContentLoaded', function() {
  // Update subtotal when checkboxes change
  document.querySelectorAll('.item-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', updateSelectedSubtotal);
  });
  
  // Initial calculation
  updateSelectedSubtotal();
  
  // Checkout selected items
  document.querySelector('.checkout-selected').addEventListener('click', function() {
      const selectedItems = [];
      
      document.querySelectorAll('.item-checkbox:checked').forEach(checkbox => {
          selectedItems.push(checkbox.value);
      });
      
      if (selectedItems.length === 0) {
          alert('Please select at least one item to checkout');
          return;
      }
      
      // Proceed to checkout with selected items
      window.location.href = '/page/checkout.php?items=' + selectedItems.join(',');
  });
  
  // Checkout all items
  document.querySelector('.checkout-all').addEventListener('click', function() {
      const allItems = Array.from(document.querySelectorAll('.item-checkbox')).map(cb => cb.value);
      window.location.href = '/page/checkout.php?items=' + allItems.join(',');
  });

  // Quantity control handlers
  document.querySelectorAll('.quantity-control').forEach(control => {
      const minusBtn = control.querySelector('.minus');
      const plusBtn = control.querySelector('.plus');
      const qtyValue = control.querySelector('.qty-value');
      const prodId = minusBtn.dataset.id;
      const row = control.closest('tr');
      const priceCell = row.querySelector('td:nth-child(5)');
      const price = parseFloat(priceCell.textContent.replace(/[^\d.-]/g, ''));

      // Update button states initially
      updateButtonStates(qtyValue, minusBtn);

      minusBtn.addEventListener('click', function() {
          const currentQty = parseInt(qtyValue.textContent);
          if (currentQty > 1) {
              updateQuantity(prodId, currentQty - 1, qtyValue, row, price, minusBtn, plusBtn);
          }
      });

      plusBtn.addEventListener('click', function() {
          const currentQty = parseInt(qtyValue.textContent);
          if (currentQty < 99) {
              updateQuantity(prodId, currentQty + 1, qtyValue, row, price, minusBtn, plusBtn);
          }
      });
  });

  // Function to update button states
  function updateButtonStates(qtyElement, minusBtn) {
      minusBtn.disabled = parseInt(qtyElement.textContent) <= 1;
  }

  // Function to update quantity via AJAX
  function updateQuantity(prodId, newQuantity, qtyElement, row, price, minusBtn) {
      // Show loading state
      const originalValue = qtyElement.textContent;
      qtyElement.textContent = '...';
      
      fetch('cart.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
              action: 'update_quantity',
              product_id: prodId,
              quantity: newQuantity
          })
      })
      .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
      })
      .then(data => {
          if (data.success) {
              // Update the UI
              qtyElement.textContent = newQuantity;
              const totalElement = row.querySelector('.item-total');
              totalElement.textContent = 'RM ' + (price * newQuantity).toFixed(2);
              updateButtonStates(qtyElement, minusBtn);
              updateSelectedSubtotal();
          } else {
              throw new Error(data.message || 'Update failed');
          }
      })
      .catch(error => {
          console.error('Error:', error);
          alert(error.message);
          qtyElement.textContent = originalValue;
      });
  }

  // Handle deletion in cart
  document.querySelectorAll('.remove-btn').forEach(button => {
      button.addEventListener('click', function() {
          if (!confirm('Are you sure you want to remove this item?')) {
              return;
          }
              
          const cartId = this.getAttribute('data-cart-id');
          const prodId = this.getAttribute('data-prod-id');
          const row = this.closest('tr'); // Store reference to row before AJAX call
          const productName = row.querySelector('td:nth-child(3)').textContent;

          if (!cartId || !prodId) {
              alert('Missing product or cart ID');
              return;
          }

          // AJAX request to delete the cart item
          $.ajax({
              url: 'cart.php',
              type: 'POST',
              data: {
                  action: 'delete_item',
                  cart_id: cartId,
                  prod_id: prodId
              },
              dataType: 'json',
              success: function(response) {
                  if (response.success) {
                      alert("Item removed successfully");
                      // Optionally, remove the item from the UI without reloading the page
                      row.remove();
                      updateSelectedSubtotal();
                  } else {
                      alert("Error: " + response.message);
                  }
              },
              error: function(xhr, status, error) {
                  alert('Error: ' + error);
              }
          });
      });
  });
});

$.ajax({
  url: 'products.php',
  type: 'POST',
  data: { action: 'add_to_cart', product_id: productId, quantity: quantity },
  dataType: 'json',
  success: function(response) {
      if (response.success) {
          alert("Product added to cart!");
      } else {
          alert("Error: " + response.message);
      }
  }
});
=======
>>>>>>> 401ecf24550306c51383457cb1f8af312e34574c
