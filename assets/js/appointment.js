// Function to initialize the appointment form handlers
function initAppointmentForm() {
    // Handle form submission validation
    const form = document.getElementById('appointment-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Basic client-side validation
            const requiredFields = [
                'name', 'course', 'block', 'year', 'purpose', 'time_slot', 
                'parent_guardian', 'contact_no', 'home_address', 'appointment_date'
            ];
            
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (input && !input.value.trim()) {
                    isValid = false;
                    // Add error styling
                    input.classList.add('is-invalid');
                } else if (input) {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    }
    
    // Format phone number as it's typed
    const phoneInput = document.getElementById('contact_no');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Format phone number (example: XXX-XXX-XXXX)
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    e.target.value = value;
                } else if (value.length <= 6) {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    e.target.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // If on the appointments page, initialize the form
    if (window.location.pathname.includes('appointments.php')) {
        // The form will be initialized after the date is selected and form is loaded
        // We set up a mutation observer to detect when the form is added to the DOM
        
        const formContainer = document.getElementById('form-container');
        if (formContainer) {
            const observer = new MutationObserver(function(mutations) {
                if (document.getElementById('appointment-form')) {
                    initAppointmentForm();
                    observer.disconnect(); // Stop observing once the form is initialized
                }
            });
            
            observer.observe(formContainer, { childList: true, subtree: true });
        }
    }
});