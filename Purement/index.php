<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUREMENT | Brasserie & Bar Pasig</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@200;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div id="public-site">
        <header>
            <a href="#home" id="logo" class="logo">PUREMENT</a>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">The Story</a></li>
                    <li><a href="#menu">The Menu</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><button class="book-btn" onclick="toggleModal('bookingModal', true)">Reservations</button></li>
                    <li>
  <button class="staff-icon-btn" aria-label="Staff Portal"
          onclick="openStaffLogin()">
    <!-- user icon (inline SVG) -->
    <svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">
      <path fill="currentColor" d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-4 0-7 2-7 5v1h14v-1c0-3-3-5-7-5z"/>
    </svg>
  </button>
</li>
                </ul>
            </nav>
        </header>

        <section id="home" class="hero">
            <h1>PUREMENT</h1>
            <p>Refined Dining & Artisan Cocktails</p>
        </section>

        <section id="about" class="about-container">
            <div class="about-img">
                <img src="https://od2-image-api.abs-cbn.com/prod/editorImage/1743167981427Photo%203_%20Skybar%20Night%20City%20Skyline.jpg" alt="Interior">
            </div>
            <div class="about-text">
                <p class="accent-sub">Our Story</p>
<h2>Crafting Purity in Every Plate</h2>
<p>At <b>PUREMENT</b>, our story began in 2018 with a single idea: fine dining should feel warm, honest, and unmistakably local. Founded in the heart of Pasig, our kitchen grew from a small team of cooks and friends who believed that great food starts with carefully chosen ingredients and ends in a welcoming table shared with loved ones.</p>

<p>Under the direction of our head chef, we combine classical French technique with the vibrant flavors of Filipino produce. We work closely with nearby farms and artisanal producers to source seasonal vegetables, heritage pork, and sustainably caught seafood then prepare them simply and precisely so each ingredient can speak for itself. Menus evolve each week to reflect what’s freshest, and our team designs each dish to highlight texture, balance, and restraint.</p>

<p>Beyond the food, PUREMENT is about rituals: the ritual of a well-brewed consommé, the quiet satisfaction of a perfectly caramelized crust, and the little moments between courses that turn dinner into memory. Our dining room was created to feel intimate yet unpretentious candlelit evenings, attentive service, and a bar that pours artisan cocktails crafted to pair with the menu.</p>

<p>We care about hospitality, sustainability, and creating experiences that linger. Whether you’re joining us for a special celebration or a simple weeknight dinner, expect honest flavors, thoughtful plating, and staff who treat every guest like an old friend returning home.</p>

<button class="book-btn" style="background: transparent; color: black; border: 1px solid black; margin-top: 10px;">Experience Purement</button>
            </div>
        </section>

        <section id="menu" class="menu-section">
            <div class="section-header">
                <p>Experience the Flavors</p>
                <h2>La Carte</h2>
            </div>
            <div class="menu-grid" id="menu-display">
                </div>
        </section>

        <section class="experience-section">
    <div class="experience-container">

        <div class="experience-image">
            <img src="https://cdn.tatlerasia.com/asiatatler/i/ph/2020/01/27145156-10-hua-yuan_cover_2000x1333.jpg" alt="Fine Dining Experience">
        </div>

        <div class="experience-content">
            <p class="accent-sub">Experience Purement</p>
            <h2>An Evening of Taste, Elegance & Atmosphere</h2>

            <p>
                At PUREMENT, dining is more than a meal it is a carefully curated journey.
                From the soft glow of candlelight to the gentle rhythm of plated courses,
                every detail is designed to create a refined yet welcoming atmosphere.
            </p>

            <p>
                Our chefs craft each dish with precision and restraint, allowing the purity
                of seasonal ingredients to shine. Paired with artisan cocktails and a
                thoughtfully selected wine list, every visit becomes a memory worth returning to.
            </p>

            <button class="book-btn" onclick="toggleModal('bookingModal', true)">
                Reserve Your Evening
            </button>
        </div>

    </div>
</section>
<section id="contact" class="contact-section">
    <div class="contact-container">

        <div class="contact-info">
            <p class="accent-sub">Contact</p>
            <h2>Visit or Reach Us</h2>

            <p><strong>Address:</strong> Blk 27 Bakawan Street, Pasig City</p>
            <p><strong>Phone:</strong> 123-456-7890</p>
            <p><strong>Email:</strong> reservations@purement.ph</p>

            <p class="contact-hours">
                Monday - Friday: 5pm - 11pm<br>
                Saturday - Sunday: 12pm - 11pm
            </p>

            <a href="https://instagram.com/" target="_blank" class="insta-large">
                Follow Us on Instagram
            </a>
        </div>

        <div class="contact-image">
            <img src="https://bellina-alimentari.com/wp-content/uploads/2017/11/398.jpg" alt="Restaurant Interior">
        </div>

    </div>
</section>
        <footer>
            <div>
                <h3>PUREMENT</h3>
                <p>Pasig City's premier destination for fine food. Elevating every moment.</p>
                
            </div>
            <div>
                <p><b>LOCATION</b></p>
                <p>Blk 27 Bakawan Street, Pasig City</p>
                <p>Tel: 123-456-7890</p>
            </div>
            <div>
                <p><b>SERVICE HOURS</b></p>
                <p>Monday - Friday: 5pm - 11pm</p>
                <p>Saturday - Sunday: 12pm - 11pm</p>
            </div>
        </footer>
    </div>

    <div id="bookingModal" class="modal">
        <div class="booking-card">
            <div class="booking-sidebar">
                <p><i>"The fondest memories are made when gathered around the table."</i></p>
            </div>
            <div class="booking-form-area">
                <span class="close-x" onclick="toggleModal('bookingModal', false)">✕</span>
                <h2>Secure a Table</h2>
                <form id="res-form" onsubmit="handleBooking(event)">
                    <div class="input-group"><label>Full Name</label><input type="text" id="res-name" name="name" maxlength="120" required></div>
                    <div class="input-row">
                        <div class="input-group"><label>Email</label><input type="email" id="res-email" name="email" maxlength="190" required></div>
                        <div class="input-group"><label>Phone</label><input type="tel" id="res-phone" name="phone" maxlength="40" required></div>
                    </div>
                    <div class="input-row">
                        <div class="input-group"><label>Date</label><input type="date" id="res-date" name="date" required></div>
                        <div class="input-group"><label>Time</label>
                            <select id="res-time" name="time"><option>6:00 PM</option><option>7:30 PM</option><option>9:00 PM</option></select>
                        </div>
                    </div>
                    <div class="input-group"><label>Guests</label>
                        <select id="res-guests" name="guests"><option value="2">2 Persons</option><option value="4">4 Persons</option><option value="6">6+ Persons</option></select>
                    </div>
                    <button type="submit" class="reserve-btn">Confirm Reservation</button>
                </form>
            </div>
        </div>
    </div>

    <div id="loginModal" class="modal">
        <div class="admin-login-card">
           <h2 class="admin-login-title" id="login-title">Staff Authentication</h2>
<p class="admin-login-sub" id="login-subtitle">Authorized Personnel Only</p>

<form id="staff-login-form" onsubmit="checkLogin(event)">
    <div class="admin-login-input">
        <input type="text" id="staffUser" placeholder="Staff username" autocomplete="username" required>
        <input type="password" id="staffPass" placeholder="Enter Access Code" autocomplete="current-password" required>
    </div>

    <p id="login-error" class="login-error" role="alert"></p>

    <button class="admin-login-btn" id="login-submit" type="submit">Access Dashboard</button>
    <button class="admin-login-btn staff-create-account-btn" type="button" onclick="requestStaffCreation()">Create Staff Account</button>
    <button class="admin-login-btn" style="background: gray; margin-top: 10px;" type="button" onclick="closeStaffLogin()">
        Back to Home
    </button>
</form>
        </div>
    </div>

    <div id="createStaffModal" class="modal" aria-hidden="true" aria-labelledby="create-staff-title">
        <div class="admin-login-card edit-reservation-card" role="dialog" aria-modal="true">
            <button class="edit-modal-close" type="button" aria-label="Close staff account form" onclick="closeCreateStaff()">&#215;</button>
            <h2 class="admin-login-title" id="create-staff-title">Create Staff Account</h2>
            <p class="admin-login-sub">Enter a username and password for the new staff member</p>
            <form id="create-staff-form" onsubmit="createStaff(event)">
                <div class="input-group">
                    <label for="new-staff-username">Staff Username</label>
                    <input id="new-staff-username" name="username" type="text" minlength="3" maxlength="60"
                           pattern="[A-Za-z0-9_-]{3,60}" autocomplete="username" required>
                    <small>3–60 letters, numbers, underscores, or hyphens.</small>
                </div>
                <div class="input-group">
                    <label for="new-staff-password">Password</label>
                    <input id="new-staff-password" name="password" type="password" minlength="12" maxlength="200"
                           autocomplete="new-password" required>
                    <small>Use at least 12 characters.</small>
                </div>
                <div class="input-group">
                    <label for="new-staff-password-confirm">Confirm Password</label>
                    <input id="new-staff-password-confirm" name="password_confirm" type="password" minlength="12"
                           maxlength="200" autocomplete="new-password" required>
                </div>
                <p id="create-staff-error" class="login-error" role="alert"></p>
                <p id="create-staff-success" class="admin-success" role="status"></p>
                <div class="edit-reservation-actions">
                    <button class="admin-login-btn edit-save-btn" id="create-staff-submit" type="submit">Create Staff Login</button>
                    <button class="edit-cancel-btn" type="button" onclick="closeCreateStaff()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editReservationModal" class="modal" aria-hidden="true" aria-labelledby="edit-reservation-title">
        <div class="admin-login-card edit-reservation-card" role="dialog" aria-modal="true">
            <button class="edit-modal-close" type="button" aria-label="Close edit form" onclick="closeEditReservation()">&#215;</button>
            <h2 class="admin-login-title" id="edit-reservation-title">Edit Reservation</h2>
            <p class="admin-login-sub">Update the guest and booking details</p>
            <form id="edit-reservation-form" onsubmit="saveReservationEdit(event)">
                <input type="hidden" id="edit-reservation-id">
                <div class="input-group">
                    <label for="edit-reservation-name">Full Name</label>
                    <input id="edit-reservation-name" name="name" type="text" maxlength="120" required>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label for="edit-reservation-email">Email</label>
                        <input id="edit-reservation-email" name="email" type="email" maxlength="190" required>
                    </div>
                    <div class="input-group">
                        <label for="edit-reservation-phone">Phone</label>
                        <input id="edit-reservation-phone" name="phone" type="tel" maxlength="40" required>
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group">
                        <label for="edit-reservation-date">Date</label>
                        <input id="edit-reservation-date" name="date" type="date" required>
                    </div>
                    <div class="input-group">
                        <label for="edit-reservation-time">Time</label>
                        <input id="edit-reservation-time" name="time" type="text" maxlength="30" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="edit-reservation-guests">Number of Guests</label>
                    <input id="edit-reservation-guests" name="guests" type="number" min="1" max="30" required>
                </div>
                <p id="edit-reservation-error" class="login-error" role="alert"></p>
                <div class="edit-reservation-actions">
                    <button class="admin-login-btn edit-save-btn" id="edit-reservation-submit" type="submit">Save Changes</button>
                    <button class="edit-cancel-btn" type="button" onclick="closeEditReservation()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="admin-page" class="admin-view">
        <div class="admin-nav">
            <h1>Reservations Ledger</h1>
            <button class="book-btn" type="button" onclick="logoutAdmin()">Logout</button>
        </div>
        <div class="dashboard-stats" id="dashboard-stats"></div>
        <div class="dashboard-section">
            <div class="dashboard-section-heading">
                <h2>All Reservations</h2>
                <input id="reservation-search" type="search" placeholder="Search reservations">
            </div>
        <table class="res-table">
            <thead>
    <tr>
        <th>Guest</th>
        <th>Email / Phone</th>
        <th>Date / Time</th>
        <th>Party</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
</thead>
            <tbody id="admin-list"></tbody>
        </table>
        </div>
        <div class="dashboard-section">
            <div class="dashboard-section-heading">
                <h2>Reservation History</h2>
                <p>Creation, edits, and cancellations</p>
            </div>
            <div class="history-list" id="reservation-history"></div>
        </div>
        <p id="dashboard-error" class="login-error" role="alert"></p>
    </div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="module.js"></script>
<script src="script.js"></script>
<script src="jquery.js"></script>



</body>
</html>