$(() => {
    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });
});

document.addEventListener('DOMContentLoaded', function() {
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