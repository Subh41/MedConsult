-- Medical Consultation System Database Schema
-- Database: medconsult_db

-- Create database
CREATE DATABASE IF NOT EXISTS medconsult_db;
USE medconsult_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    user_type ENUM('patient', 'doctor') DEFAULT 'patient',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Doctors table
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    specialization VARCHAR(100),
    experience_years INT,
    clinic_address TEXT,
    consultation_fee DECIMAL(10, 2),
    rating DECIMAL(3, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Doctor availability table
CREATE TABLE doctor_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
    start_time TIME,
    end_time TIME,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    doctor_id INT,
    appointment_date DATE,
    appointment_time TIME,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Sample data for testing

-- Sample users (patients)
INSERT INTO users (full_name, email, phone, password, user_type) VALUES 
('John Doe', 'john@example.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient'),
('Jane Smith', 'jane@example.com', '0987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient');

-- Sample doctors
INSERT INTO users (full_name, email, phone, password, user_type) VALUES 
('Dr. Michael Johnson', 'michael@clinic.com', '1112223333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor'),
('Dr. Sarah Williams', 'sarah@clinic.com', '4445556666', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor');

INSERT INTO doctors (user_id, specialization, experience_years, clinic_address, consultation_fee, rating) VALUES 
(3, 'Cardiologist', 15, '123 Medical Plaza, Downtown', 150.00, 4.8),
(4, 'Dermatologist', 12, '456 Health Street, Uptown', 120.00, 4.9);

-- Doctor availability
INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time) VALUES 
(1, 'Monday', '09:00:00', '17:00:00'),
(1, 'Tuesday', '09:00:00', '17:00:00'),
(1, 'Wednesday', '09:00:00', '17:00:00'),
(1, 'Thursday', '09:00:00', '17:00:00'),
(1, 'Friday', '09:00:00', '17:00:00'),
(2, 'Tuesday', '10:00:00', '18:00:00'),
(2, 'Wednesday', '10:00:00', '18:00:00'),
(2, 'Thursday', '10:00:00', '18:00:00'),
(2, 'Friday', '10:00:00', '18:00:00'),
(2, 'Saturday', '10:00:00', '18:00:00');

-- Sample appointment
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES 
(1, 1, '2024-01-15', '10:30:00', 'scheduled');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_appointments_patient ON appointments(patient_id);
CREATE INDEX idx_appointments_doctor ON appointments(doctor_id);
CREATE INDEX idx_appointments_date ON appointments(appointment_date);
CREATE INDEX idx_doctor_specialization ON doctors(specialization);
