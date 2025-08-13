# Medical Consultation Web Application

A basic medical consultation web application similar to Apollo 24*7, built with HTML, CSS, PHP, and MySQL.

## Features

- User registration and login (with show password functionality)
- Google login/register integration (UI only - requires OAuth setup for full functionality)
- Find doctors by specialization
- Doctor booking system
- AI chat for basic medical queries
- User appointment management

## Setup Instructions

### Prerequisites

1. XAMPP installed on your system
2. Web browser

### Installation Steps

1. **Start XAMPP Control Panel**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Place Files in htdocs**
   - Copy all files to `C:\xampp\htdocs\medical-consultation` (or your XAMPP installation directory)

3. **Create Database**
   - Open your web browser and go to `http://localhost/phpmyadmin`
   - Click on the "Databases" tab
   - Create a new database named `medconsult_db`
   - Select the `medconsult_db` database
   - Click on the "Import" tab
   - Choose the `medconsult_database.sql` file from the project folder
   - Click "Go" to import the database structure and sample data

4. **Configure Database Connection**
   - The database connection is already configured in the PHP files:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "medconsult_db";
     ```
   - If you have a different MySQL username/password, update these values in:
     - `register.php`
     - `login.php`
     - `find-doctors.php`
     - `book-appointment.php`
     - `my-appointments.php`

5. **Access the Application**
   - Open your web browser and go to `http://localhost/medical-consultation`

## File Structure

```
medical-consultation/
├── index.html          # Home page
├── register.html       # Registration form
├── login.html          # Login form
├── find-doctors.php    # Doctor search and listing
├── chat.html           # AI chat interface
├── dashboard.php       # User dashboard
├── book-appointment.php # Appointment booking
├── my-appointments.php # User appointments
├── register.php        # Registration backend
├── login.php           # Login backend
├── logout.php          # Logout functionality
├── styles.css          # Styling
├── medconsult_database.sql # Database schema
└── README.md           # This file
```

## Database Schema

The application uses the following tables:

1. **users** - Stores user information (patients and doctors)
2. **doctors** - Stores doctor-specific information
3. **doctor_availability** - Stores doctor availability schedules
4. **appointments** - Stores appointment information

## Usage

1. **Register/Login**
   - Navigate to the registration page to create an account
   - Use the login page to access your account

2. **Find Doctors**
   - Use the "Find Doctors" page to search for doctors by specialization
   - Filter results using the search form

3. **Book Appointments**
   - Click "Book Appointment" on any doctor card
   - Select a date and time slot
   - Confirm your booking

4. **Manage Appointments**
   - View your appointments in the "My Appointments" section
   - Cancel upcoming appointments if needed

5. **AI Chat**
   - Use the chat interface to ask basic medical questions
   - Get information about doctor availability and specializations

## Notes

- The Google login/register functionality is implemented as UI only. For full functionality, you would need to integrate with Google OAuth API.
- Passwords are securely hashed using PHP's `password_hash()` function
- Session management is used for user authentication
- The application uses prepared statements to prevent SQL injection
- All user inputs are sanitized using `htmlspecialchars()` to prevent XSS attacks

## Customization

To customize the application:

1. **Add More Doctors**
   - Add new records to the `users` table (with user_type = 'doctor')
   - Add corresponding records to the `doctors` table
   - Add availability information to the `doctor_availability` table

2. **Modify Specializations**
   - Update the specialization options in `find-doctors.php`
   - Add new specializations to the database

3. **Change Styling**
   - Modify `styles.css` to change the appearance

## Troubleshooting

1. **Database Connection Error**
   - Ensure MySQL service is running in XAMPP
   - Check database credentials in PHP files
   - Verify the database `medconsult_db` exists

2. **Page Not Found**
   - Ensure all files are in the correct directory
   - Check that Apache service is running in XAMPP
   - Verify the URL path is correct

3. **Registration/Login Issues**
   - Check browser console for JavaScript errors
   - Verify PHP error reporting in XAMPP settings

## Security Considerations

- Passwords are hashed before storage
- SQL injection prevention through prepared statements
- XSS prevention through input sanitization
- Session-based authentication

For production use, consider implementing:
- HTTPS
- More robust input validation
- Rate limiting
- Email verification
- Password strength requirements
