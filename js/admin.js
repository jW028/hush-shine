$(document).ready(function() {
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