<?php
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/helper.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = getUserName($user_id, $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - BUPC Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/appoinment.css">
    <style>
        .appointment-card {
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .status-confirmed {
            background-color: #28a745;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .status-completed {
            background-color: #007bff;
            color: white;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-weight: bold;
        }
        .appointment-date {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff8000;
        }
        .appointment-time {
            font-weight: bold;
            color: #2b2a29;
        }
        .no-appointments {
            text-align: center;
            padding: 50px 0;
            color: #6c757d;
        }
        #refreshBtn {
            background-color: #ff8000;
            border: none;
        }
        #refreshBtn:hover {
            background-color: #e67300;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?php include 'src/views/header.php'; ?>

<div class="container mt-5 pt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Appointments</h2>
        <button id="refreshBtn" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
    
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading your appointments...</p>
    </div>
    
    <div id="appointmentsContainer">
        <!-- Appointments will be loaded here via AJAX -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
        // This uses history.replaceState to modify the URL without reloading
        const newUrl = window.location.pathname;
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
    
    // Refresh button event
    document.getElementById('refreshBtn').addEventListener('click', function() {
        loadAppointments();
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
});
</script>

</body>
</html>