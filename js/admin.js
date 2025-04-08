$(document).ready(function() {

    const modal = document.getElementById("addProductModal");
    const btn = document.getElementById("openAddProductModal");
    const span = document.getElementsByClassName("close")[0];
    const cancelBtn = document.getElementById("close-modal");

    btn.onclick = function() {
        modal.style.display = "block";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
        document.body.style.overflow = "hidden";
    }

    span.onclick = function() {
        closeModal();
    }

    cancelBtn.onclick = function() {
        closeModal();
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    function closeModal() {
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }, 300);
    }

    // Image preview functionality
    $('#images').on('change', function(event) {
        const $previewDiv = $('#imagePreview');
        $previewDiv.empty();
        
        if (event.target.files && event.target.files.length > 0) {
            $previewDiv.show();
            $previewDiv.append('<h5>New Images Preview:</h5>');
            $previewDiv.append('<div class="new-images-preview"></div>');
            const $container = $previewDiv.find('.new-images-preview');
            
            $container.css({
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
        } else {
            $previewDiv.hide();
        }
    });

    $('#addProductForm').on('submit', function(e) {
        e.preventDefualt();

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
                    modal.style.display = "none";
                    document.body.style.overlfow = "auto";

                    window.location.reload();
                } else {
                    alert('Error: ' + response.errors.join('\n'));;
                }
            }, 
            error: function() {
                alert('An error occured. Please try again.');
            }
        });
    })
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