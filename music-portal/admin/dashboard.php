<?php
include '../db_connect.php'; // Connects to the DB from admin sub-directory
include '../log_page_view.php';
session_start();

// Check if user is logged in and is an Admin (RoleID = 1)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 1) {
    // Redirect to the login page (assuming it's in the parent directory)
    header("Location: ../index.php?error=access_denied");
    exit();
}

// In a real application, you might fetch some dashboard-specific data here,
// but for now, it's just a navigation hub.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SoundWave</title>
    <style>
        /* Modern CSS variables for consistency */
        :root {
            --primary-color: #6c5ce7; /* Purple */
            --secondary-color: #a29bfe; /* Lighter Purple */
            --accent-color: #fd79a8; /* Pink */
            --dark-bg: #2d3436; /* Dark Grey */
            --darker-bg: #1e2124; /* Even Darker Grey */
            --text-white: #ffffff;
            --text-gray: #b2bec3;
            --card-bg: #36393f; /* Card Background */
            --hover-bg: #40434a; /* Hover Background */
            --error-red: #ff6b6b;
            --success-green: #2ecc71;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            color: var(--text-white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center; /* Center vertically on this page */
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background-color: var(--card-bg);
            padding: 50px 40px; /* More padding */
            border-radius: 15px; /* Softer corners */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            text-align: center;
        }

        h1 {
            font-size: 3.2rem; /* Even larger, more impactful title */
            text-align: center;
            margin-bottom: 25px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); /* Purple gradient */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); /* Subtle text shadow */
        }

        p {
            color: var(--text-gray);
            margin-bottom: 40px; /* More space below intro text */
            font-size: 1.15rem;
            line-height: 1.6;
        }

        .nav-links {
            margin-top: 30px;
            margin-bottom: 50px; /* Increased space below links */
            display: grid; /* Use CSS Grid for better layout control */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Responsive grid columns */
            gap: 25px; /* More space between grid items */
            justify-content: center; /* Center grid items horizontally */
        }

        .nav-links a {
            display: flex; /* Use flex for content inside each link card */
            flex-direction: column; /* Stack icon and text */
            align-items: center;
            background-color: var(--darker-bg);
            color: var(--text-white);
            padding: 30px 20px; /* More padding for card feel */
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease; /* Smooth transition for all properties */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(108, 92, 231, 0.2); /* Subtle border */
        }

        .nav-links a:hover {
            background-color: var(--hover-bg);
            transform: translateY(-8px); /* Lift significantly on hover */
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.5); /* Stronger shadow on hover */
            border-color: var(--primary-color); /* Highlight border on hover */
        }

        .nav-links a::before {
            /* Icon placeholder - You can replace these with actual icon fonts (e.g., FontAwesome) */
            font-size: 2.5rem; /* Larger icon size */
            margin-bottom: 15px;
            color: var(--accent-color); /* Pink color for icons */
            display: block;
        }

        .nav-links a[href*="users"]::before { content: '👥'; } /* Users icon */
        .nav-links a[href*="artists"]::before { content: '🎤'; } /* Artists icon */
        .nav-links a[href*="songs"]::before { content: '🎶'; } /* Songs/Albums icon */
        .nav-links a[href*="analytics"]::before { content: '📊'; } /* Analytics icon */
        /* For the placeholder link, assign a default or specific icon */
        .nav-links a[href="#"]::before { content: '⚙️'; } /* Generic settings/gear icon */


        .logout-container { /* New container for logout button for centering */
            text-align: center;
            margin-top: 40px;
        }

        .logout-btn {
            background: linear-gradient(45deg, var(--accent-color), #ffafbe); /* Pink gradient */
            color: var(--text-white);
            padding: 15px 30px;
            border: none;
            border-radius: 30px; /* More rounded */
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(253, 121, 168, 0.4);
            text-decoration: none; /* Ensure it looks like a button even if it's an anchor */
            display: inline-block;
        }

        .logout-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(253, 121, 168, 0.6);
            opacity: 0.9;
        }

        /* --- Custom Confirmation Modal Styles --- */
        .custom-modal {
            position: fixed; /* Stay in place */
            z-index: 2000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            background-color: rgba(0, 0, 0, 0.7); /* Black w/ opacity */
            backdrop-filter: blur(5px); /* Blurred background */
            display: flex; /* Use flexbox to center content */
            justify-content: center;
            align-items: center;

            /* Start hidden and transition visibility */
            visibility: hidden; /* Hidden by default */
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease; /* Transition both */
        }

        .custom-modal.show {
            visibility: visible; /* Show when 'show' class is added */
            opacity: 1; /* Fade in */
        }

        .custom-modal-content {
            background-color: var(--card-bg);
            margin: auto; /* For centering */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            width: 90%;
            max-width: 400px;
            text-align: center;
            transform: translateY(-50px); /* Start slightly above for animation */
            opacity: 0; /* Start hidden for animation */
            transition: transform 0.3s ease, opacity 0.3s ease;
            position: relative; /* For the close button */
        }

        .custom-modal.show .custom-modal-content {
            transform: translateY(0); /* Slide down into place */
            opacity: 1; /* Fade in */
        }

        .custom-modal-content h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .custom-modal-content p {
            color: var(--text-gray);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 20px; /* Space between buttons */
        }

        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none; /* For the anchor tag if used */
            display: inline-block; /* For consistent sizing */
            text-align: center;
        }

        .modal-btn.confirm {
            background: linear-gradient(45deg, var(--accent-color), #ffafbe); /* Pink gradient for confirm */
            color: var(--text-white);
            box-shadow: 0 4px 10px rgba(253, 121, 168, 0.4);
        }

        .modal-btn.confirm:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(253, 121, 168, 0.6);
        }

        .modal-btn.cancel {
            background-color: var(--darker-bg);
            color: var(--text-gray);
            border: 2px solid var(--primary-color);
        }

        .modal-btn.cancel:hover {
            background-color: var(--primary-color);
            color: var(--text-white);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.4);
        }
        /* End Custom Confirmation Modal Styles */


        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 40px 25px;
            }
            h1 {
                font-size: 2.5rem;
            }
            p {
                font-size: 1.05rem;
            }
            .nav-links {
                grid-template-columns: 1fr; /* Stack links vertically on smaller screens */
                gap: 18px;
            }
            .nav-links a {
                padding: 25px 20px;
                font-size: 1rem;
            }
            .nav-links a::before {
                font-size: 2rem;
            }
            .logout-btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
            .modal-buttons {
                flex-direction: column;
                gap: 15px;
            }
            .modal-btn {
                width: 100%; /* Full width buttons on small screens */
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 15px;
            }
            h1 {
                font-size: 2rem;
            }
            p {
                font-size: 0.95rem;
            }
            .nav-links a {
                padding: 20px 15px;
            }
            .nav-links a::before {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>This is your exclusive admin dashboard. Use the links below to manage the platform.</p>

        <div class="nav-links">
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_artists.php">Manage Artists</a>
            <a href="manage_songs_albums.php">Manage Songs & Albums</a>
            <a href="analytics.php">Site Analytics</a>
        </div>

        <div class="logout-container">
            <button type="button" class="logout-btn" onclick="showLogoutConfirmation()">Logout</button>
        </div>
    </div>

    <div id="logoutConfirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out of your admin session?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn confirm" onclick="proceedLogout()">Yes, Log Out</button>
                <button type="button" class="modal-btn cancel" onclick="hideLogoutConfirmation()">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        function showLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.add('show');
        }

        function hideLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.remove('show');
            // Optional: Add a small delay before setting display: none if you want the animation to complete
            setTimeout(() => {
                modal.style.display = 'none'; // Temporarily setting display none to ensure clicks pass through
            }, 300); // Matches CSS transition duration
        }

        function proceedLogout() {
            window.location.href = "../logout.php"; // Redirect to your logout script
        }

        // Close modal if user clicks outside of the content (but within the modal overlay)
        document.getElementById('logoutConfirmationModal').addEventListener('click', function(event) {
            if (event.target === this) { // If the click was on the modal background itself
                hideLogoutConfirmation();
            }
        });

        // Re-enable clicks on the modal when it's hidden (important for future interactions)
        // This is a correction for the "button doesn't work" issue we had previously.
        // When the modal transitions to hidden (opacity 0, visibility hidden),
        // we want to explicitly make sure it doesn't block events.
        // The setTimeout in hideLogoutConfirmation coupled with the correct CSS visibility handling should cover this.
        // No extra JS needed for this specific bug fix, the CSS change was the key.
    </script>
</body>
</html>