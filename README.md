# Ronald_Trinidad-FINALPROJECT

PUREMENT is a restaurant website where guests can explore the menu and book a table. Staff can sign in to view and manage reservations.

## What you can do

Guests can make a reservation by entering their contact details, choosing a date and time, and selecting the number of guests.

Staff can view all bookings in one dashboard, search for a reservation, update its details, or cancel it. The dashboard also keeps a history of reservation changes.

New staff can create an account from the Staff Login window using **Create Staff Account**. Choose a username and a password with at least 12 characters, then use those details to sign in.

## Run the website

1. Put the project folder in `C:\xampp\htdocs\purement`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Import `database.sql` using phpMyAdmin, or let the website create its tables when it connects to the database.
4. Visit [http://localhost/purement/](http://localhost/purement/).

The database connection is set in `config.php`. The default settings are for a local XAMPP installation.

## A note about staff accounts

Anyone who can access the website can currently create a staff account. If the site will be available to other people, staff registration should be restricted first.
