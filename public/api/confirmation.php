<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook - Appointment Confirmed</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: block;
            height: auto;
            overflow: auto;
            background-color: var(--bg-dark);
        }

        .confirmation-container {
            max-width: 500px;
            margin: 50px auto;
            text-align: center;
            padding: 40px;
            background-color: var(--bg-panel);
            border-radius: 12px;
        }

        .confirmation-icon {
            width: 80px;
            height: 80px;
            background-color: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }

        .confirmation-title {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--success);
        }

        .confirmation-subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .summary-card {
            background-color: var(--bg-card);
            padding: 25px;
            border-radius: 10px;
            text-align: left;
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--text-muted);
        }

        .summary-value {
            font-weight: 500;
        }

        .ref-number {
            font-family: monospace;
            background-color: var(--bg-dark);
            padding: 5px 10px;
            border-radius: 4px;
        }

        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-home:hover {
            background-color: var(--primary-hover);
        }
    </style>
</head>
<body>

    <div class="confirmation-container">
        <div class="confirmation-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="confirmation-title">APPOINTMENT CONFIRMED</h1>
        <p class="confirmation-subtitle">Your appointment has been booked successfully</p>

        <div class="summary-card">
            <div class="summary-row">
                <span class="summary-label">Reference ID</span>
                <span class="summary-value ref-number" id="ref-id">---</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Doctor</span>
                <span class="summary-value" id="doctor-name">---</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Date</span>
                <span class="summary-value" id="appointment-date">---</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Time</span>
                <span class="summary-value" id="appointment-time">---</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Fee</span>
                <span class="summary-value" id="appointment-fee">---</span>
            </div>
        </div>

        <a href="index.php" class="btn-home">Back to Home</a>
    </div>

    <script>
        // Confirmation Page - D2-05
        document.addEventListener('DOMContentLoaded', function() {
            // Replace browser history to prevent back navigation
            window.history.replaceState(null, '', 'confirmation.php');
            
            // Load appointment data from session storage
            const data = sessionStorage.getItem('appointmentData');
            
            if (!data) {
                // No data, redirect to home
                window.location.href = 'index.php';
                return;
            }
            
            const appointment = JSON.parse(data);
            
            // Display appointment details
            document.getElementById('ref-id').textContent = appointment.refId;
            document.getElementById('doctor-name').textContent = appointment.doctorName;
            document.getElementById('appointment-date').textContent = formatDate(appointment.date);
            document.getElementById('appointment-time').textContent = appointment.times.join(', ');
            document.getElementById('appointment-fee').textContent = '$' + appointment.fee;
            
            // Clear session storage
            sessionStorage.removeItem('appointmentData');
        });

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
    </script>
</body>
</html>
