<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocBook Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --page-bg: #12131a;
            --panel-bg: #1f2028;
            --text-main: #f1f3f9;
            --text-soft: #8e93a2;
            --primary: #4c9bf8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Outfit", sans-serif;
            background: #0f1016;
            color: var(--text-main);
        }

        .dashboard {
            min-height: 100vh;
            margin: 8px;
            border: 1px solid #272a36;
            border-radius: 12px;
            background: var(--page-bg);
        }

        .navbar {
            height: 60px;
            background: #1f2028;
            border-bottom: 1px solid #2a2d39;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
        }

        .navbar-brand {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .navbar-brand span {
            color: var(--primary);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
            font-size: 0.9rem;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .user-role {
            color: var(--text-soft);
            font-size: 0.85rem;
        }

        .logout-btn {
            background: #f77b7b;
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-family: inherit;
        }

        .logout-btn:hover {
            background: #e66666;
        }

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-card {
            background: linear-gradient(170deg, #2b2d37 0%, #262833 70%);
            border: 1px solid #343845;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .welcome-card h1 {
            margin: 0 0 10px;
            font-size: 1.8rem;
            color: #d7e8ff;
        }

        .welcome-card p {
            margin: 0;
            color: var(--text-soft);
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: linear-gradient(170deg, #2b2d37 0%, #262833 70%);
            border: 1px solid #343845;
            border-radius: 12px;
            padding: 20px;
        }

        .card h2 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            color: #d7e8ff;
        }

        .card p {
            margin: 0;
            color: var(--text-soft);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .info-group {
            background: #1b1d25;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .info-label {
            color: #9ba1b0;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            color: var(--primary);
            font-weight: 600;
            font-size: 1rem;
            margin-top: 4px;
        }

        .info-group:last-child {
            margin-bottom: 0;
        }

        .error {
            background: #f77b7b;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .success {
            background: #81c784;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .navbar {
                flex-direction: column;
                height: auto;
                padding: 12px 15px;
                gap: 10px;
            }

            .navbar-user {
                width: 100%;
                justify-content: space-between;
            }

            .welcome-card h1 {
                font-size: 1.4rem;
            }

            .welcome-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="navbar">
            <div class="navbar-brand">Doc<span>Book</span></div>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="user-name" id="userName">User</div>
                    <div class="user-role" id="userRole">Role</div>
                </div>
                <button class="logout-btn" onclick="logout()">Log out</button>
            </div>
        </div>

        <div class="container">
            <div class="welcome-card">
                <h1>Welcome to DocBook</h1>
                <p id="welcomeMessage">Loading...</p>
            </div>

            <div class="content-grid">
                <div class="card">
                    <h2>Your Account</h2>
                    <div class="info-group">
                        <div class="info-label">Email</div>
                        <div class="info-value" id="displayEmail">-</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">Role</div>
                        <div class="info-value" id="displayRole">-</div>
                    </div>
                    <div class="info-group">
                        <div class="info-label">User ID</div>
                        <div class="info-value" id="displayId">-</div>
                    </div>
                </div>

                <div class="card">
                    <h2>Quick Actions</h2>
                    <p>
                        ✅ Email verified<br>
                        ✅ Account created<br>
                        🔒 Profile complete<br>
                        📅 Ready to book appointments
                    </p>
                </div>

                <div class="card">
                    <h2>Features</h2>
                    <p>
                        Browse doctors by specialization<br>
                        Check real-time availability<br>
                        Book and manage appointments<br>
                        Receive instant confirmation
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Check if user is logged in
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            const userName = localStorage.getItem('userName');
            const userRole = localStorage.getItem('userRole');

            if (!token) {
                alert('Please log in first');
                window.location.href = 'index.php';
                return;
            }

            // Display user info
            document.getElementById('userName').textContent = userName || 'User';
            document.getElementById('userRole').textContent = userRole || 'Role';
            document.getElementById('displayRole').textContent = userRole || 'N/A';
            document.getElementById('welcomeMessage').textContent = `Welcome, ${userName}! You are logged in as a ${userRole}.`;

            // Verify token with backend
            verifyToken(token);
        });

        async function verifyToken(token) {
            try {
                const response = await fetch('api/verify.php', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (!response.ok) {
                    throw new Error('Token invalid or expired');
                }

                const data = await response.json();
                
                if (data.user) {
                    document.getElementById('displayEmail').textContent = data.user.email;
                    document.getElementById('displayId').textContent = data.user.userId;
                }
            } catch (error) {
                console.error('Token verification failed:', error);
                alert('Session expired. Please log in again.');
                logout();
            }
        }

        function logout() {
            if (confirm('Are you sure you want to log out?')) {
                localStorage.removeItem('authToken');
                localStorage.removeItem('userName');
                localStorage.removeItem('userRole');
                window.location.href = 'index.php';
            }
        }
    </script>
</body>
</html>
