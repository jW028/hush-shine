$(document).ready(function() {
    // Get modals
    const addProductModal = document.getElementById("addProductModal");
    const deleteProductModal = document.getElementById("deleteProductModal");
    
    // Button selectors
    const addProductBtn = document.getElementById("openAddProductModal");
    const closeButtons = document.getElementsByClassName("close");
    const cancelButtons = document.getElementsByClassName("close-modal");
    
    console.log("Cancel buttons found:", cancelButtons.length);
    
    // Function to open any modal
    function openModal(modal) {
        if (!modal) return;
        
        modal.style.display = "block";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
        document.body.style.overflow = "hidden";
    }
    
    // Function to close any modal
    function closeModal(modal) {
        if (!modal) return;
        
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }, 100);
    }
    
    // Add Product button opens the modal
    if (addProductBtn) {
        addProductBtn.onclick = function() {
            openModal(addProductModal);
        };
    }
    
    // X buttons close modals
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            const modal = this.closest('.modal');
            if (modal) closeModal(modal);
        };
    }
    
    // Cancel buttons with close-modal class
    for (let i = 0; i < cancelButtons.length; i++) {
        cancelButtons[i].onclick = function() {
            console.log("Cancel button clicked");
            const modal = this.closest('.modal');
            if (modal) closeModal(modal);
        };
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            closeModal(event.target);
        }
    };
    
    console.log("Delete modal element exists: "), !!document.getElementById("deleteProductModal");

    // Handle delete product buttons
    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault();
        
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        console.log("Delete clicked for product:", productId, productName);
        console.log("Delete modal exists at click time:", !!document.getElementById("deleteProductModal"));
        // Update the delete modal content

        const deleteProductModal = document.getElementById("deleteProductModal");
        if (!deleteProductModal) {
            console.error("Delete product modal not found");
            return;
        }
        $(deleteProductModal).find('.modal-body strong').text(productName);
        $(deleteProductForm).find('#prod_id').val(productId);
        
        deleteProductModal.style.display = "block";
        setTimeout(() => {
            deleteProductModal.classList.add("show");
        }, 10);
        document.body.style.overflow = "hidden";
        // // Open delete modal
        openModal(deleteProductModal);
    });

    // Image preview functionality
    $('#images').on('change', function(event) {
        const $previewDiv = $('#imagePreview');
        $previewDiv.empty();
        
        if (event.target.files && event.target.files.length > 0) {
            $previewDiv.show();
            
            const $container = $('<div class="new-images-preview"></div>').css({
                'display': 'flex',
                'flex-wrap': 'wrap',
                'gap': '10px'
            });
            
            $.each(event.target.files, function(i, file) {
                const reader = new FileReader();
                
                const $imgDiv = $('<div></div>').css({
                    'width': '100px',
                    'margin-bottom': '10px'
                });
                
                reader.onload = function(e) {
                    $imgDiv.html(`
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 100px; object-fit: cover;">
                        <small class="d-block text-center mt-1">Image #${i+1}</small>
                    `);
                };
                
                reader.readAsDataURL(file);
                $container.append($imgDiv);
            });
            
            $previewDiv.append($container);
        } else {
            $previewDiv.hide();
        }
    });

    // Form submission with AJAX - Fixed typo in preventDefault
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault(); // Fixed typo here
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST', 
            data: formData, 
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    closeModal(addProductModal);
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.errors ? response.errors.join('\n') : 'Unknown error'));
                }
            }, 
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Delete product form submission
    $('#deleteProductForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    closeModal(deleteProductModal);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while trying to delete the product.');
            }
        });
    }); 
});


// Function to change main image when thumbnail is clicked
function changeMainImage(src, thumb) {
    document.getElementById('mainImage').src = src;
    
    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(item => {
        item.classList.remove('active');
    });
    thumb.classList.add('active');
}

document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const shippingInfo = document.getElementById('shipping-info');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'Shipped') {
            shippingInfo.classList.remove('d-none');
        } else {
            shippingInfo.classList.add('d-none');
        }
    });
});