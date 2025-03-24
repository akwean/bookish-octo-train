document.addEventListener('DOMContentLoaded', function() {
    // Load appointments on page load
    loadAppointments();
    
    // Check URL parameters for auto-refresh
    const urlParams = new URLSearchParams(window.location.search);
    const shouldAutoRefresh = urlParams.get('refresh') === 'true';
    const successMessage = urlParams.get('success');
    
    // If coming from a successful booking, show a success message
    if (successMessage === 'appointment_booked') {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            <strong>Success!</strong> Your appointment has been booked successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insert the alert before the appointments container
        const container = document.getElementById('appointmentsContainer');
        container.parentNode.insertBefore(alertDiv, container);
        
        // Remove the parameters from URL to prevent refresh on page reload
        const newUrl = window.location.pathname;
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
    
    // Refresh button event
    document.getElementById('refreshBtn').addEventListener('click', function() {
        loadAppointments();
    });
});

function loadAppointments() {
    const container = document.getElementById('appointmentsContainer');
    const loadingSpinner = document.querySelector('.loading-spinner');
    
    // Show loading spinner
    loadingSpinner.style.display = 'block';
    
    // Make AJAX request
    fetch('/process/get_user_appointments.php')
        .then(response => response.json())
        .then(data => {
            // Hide loading spinner
            loadingSpinner.style.display = 'none';
            
            // Clear container
            container.innerHTML = '';
            
            if (data.length === 0) {
                // No appointments
                container.innerHTML = `
                    <div class="no-appointments">
                        <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">No appointments found</h4>
                        <p>You haven't booked any appointments yet.</p>
                        <a href="appointments.php" class="btn btn-primary">Book an Appointment</a>
                    </div>
                `;
            } else {
                // Build cards for each appointment
                data.forEach(appointment => {
                    const statusClass = `status-${appointment.status.toLowerCase()}`;
                    const formattedDate = formatDate(appointment.appointment_date);
                    
                    const card = document.createElement('div');
                    card.className = 'appointment-card card';
                    card.innerHTML = `
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="appointment-date">${formattedDate}</span>
                                <span class="ms-2 appointment-time">${appointment.time_slot}</span>
                            </div>
                            <span class="status-badge ${statusClass}">${capitalizeFirstLetter(appointment.status)}</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Purpose:</strong> ${getPurposeText(appointment.purpose)}</p>
                                    <p><strong>Course:</strong> ${appointment.course}</p>
                                    <p><strong>Block:</strong> ${appointment.block}</p>
                                    <p><strong>Year:</strong> ${getYearText(appointment.year)}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Parent/Guardian:</strong> ${appointment.parent_guardian}</p>
                                    <p><strong>Contact:</strong> ${appointment.contact_no}</p>
                                    <p><strong>Address:</strong> ${appointment.home_address}</p>
                                </div>
                            </div>
                            ${appointment.additional_notes ? `<p><strong>Notes:</strong> ${appointment.additional_notes}</p>` : ''}
                            ${appointment.status === 'pending' ? `
                                <div class="mt-3">
                                    <button class="btn btn-danger btn-sm cancel-btn" data-id="${appointment.appointment_id}">
                                        Cancel Appointment
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    container.appendChild(card);
                });
                
                // Add event listeners to cancel buttons
                document.querySelectorAll('.cancel-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const appointmentId = this.getAttribute('data-id');
                        cancelAppointment(appointmentId);
                    });
                });
            }
        })
        .catch(error => {
            loadingSpinner.style.display = 'none';
            container.innerHTML = `
                <div class="alert alert-danger">
                    Error loading appointments. Please try again later.
                </div>
            `;
            console.error('Error:', error);
        });
}

function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch('/process/cancel_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `appointment_id=${appointmentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload appointments to reflect changes
                loadAppointments();
            } else {
                alert('Failed to cancel appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the appointment.');
        });
    }
}

// Helper functions
function formatDate(dateStr) {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateStr).toLocaleDateString('en-US', options);
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getPurposeText(purposeCode) {
    const purposes = {
        'medical': 'Medical consultation & treatment',
        'physical_examination': 'Physical examination',
        'dental': 'Dental consultation & treatment',
        'vaccination': 'Vaccination (Flu & Pneumonia)'
    };
    return purposes[purposeCode] || purposeCode;
}

function getYearText(yearCode) {
    const years = {
        '1st': 'First Year',
        '2nd': 'Second Year',
        '3rd': 'Third Year',
        '4th': 'Fourth Year'
    };
    return years[yearCode] || yearCode;
}

// Add real-time polling for appointment updates
let lastCheckedTimestamp = Math.floor(Date.now() / 1000);

// Poll for updates every 10 seconds
setInterval(function() {
    fetch(`/process/get_user_appointments_updates.php?timestamp=${lastCheckedTimestamp}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.appointments.length > 0) {
                // Update timestamp for next poll
                lastCheckedTimestamp = data.timestamp;
                
                // Show notification for updated appointments
                showUpdateNotification(data.appointments.length);
                
                // Refresh appointments display
                loadAppointments();
            }
        })
        .catch(error => {
            console.error('Error checking for updates:', error);
        });
}, 10000);

function showUpdateNotification(count) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show';
    notification.innerHTML = `
        <strong>Update!</strong> ${count} appointment(s) have been updated.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    const container = document.querySelector('.container');
    container.insertBefore(notification, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}