-- DocBook Database Schema
-- Doctor Appointment System

-- Create database
CREATE DATABASE IF NOT EXISTS docbook;
USE docbook;

-- Doctors table
CREATE TABLE IF NOT EXISTS doctors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    specialty VARCHAR(100),
    years_experience INT DEFAULT 0,
    fee DECIMAL(10,2) DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    bio TEXT,
    photo_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Rejected', 'Completed', 'Cancelled') DEFAULT 'Pending',
    visit_reason TEXT,
    duration_minutes INT DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    INDEX idx_doctor_date (doctor_id, appointment_date),
    INDEX idx_patient (patient_id)
);

-- Comments table (doctor notes)
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    INDEX idx_appointment (appointment_id)
);

-- Doctor availability table
CREATE TABLE IF NOT EXISTS doctor_availability (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_minutes INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    UNIQUE KEY unique_doctor_day (doctor_id, day_of_week)
);

-- Time slots table (generated slots)
CREATE TABLE IF NOT EXISTS time_slots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NOT NULL,
    slot_date DATE NOT NULL,
    slot_time TIME NOT NULL,
    is_break BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'booked', 'blocked') DEFAULT 'available',
    appointment_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id),
    UNIQUE KEY unique_slot (doctor_id, slot_date, slot_time),
    INDEX idx_doctor_date_status (doctor_id, slot_date, status)
);

-- Insert sample data for doctors
INSERT INTO doctors (name, email, password_hash, specialty, years_experience, fee, rating, review_count, bio) VALUES
('Dr. Sarah Lim', 'sarah.lim@docbook.com', '$2y$10$abcdefghijklmnopqrstuv', 'General Practitioner', 8, 75.00, 4.8, 124, 'Experienced general practitioner with a focus on preventive care and patient education.'),
('Dr. Michael Chen', 'michael.chen@docbook.com', '$2y$10$abcdefghijklmnopqrstuv', 'Cardiologist', 15, 150.00, 4.9, 89, 'Board-certified cardiologist specializing in heart disease prevention and treatment.'),
('Dr. Emily Watson', 'emily.watson@docbook.com', '$2y$10$abcdefghijklmnopqrstuv', 'Pediatrician', 10, 85.00, 4.7, 156, 'Dedicated pediatrician with expertise in childhood development and common pediatric conditions.');

-- Insert sample data for patients
INSERT INTO patients (name, email, password_hash, phone, date_of_birth, address) VALUES
('John Patient', 'john.patient@email.com', '$2y$10$abcdefghijklmnopqrstuv', '555-0101', '1985-06-15', '123 Main St, City'),
('Emily Johnson', 'emily.johnson@email.com', '$2y$10$abcdefghijklmnopqrstuv', '555-0102', '1990-03-22', '456 Oak Ave, City'),
('Mark Rivera', 'mark.rivera@email.com', '$2y$10$abcdefghijklmnopqrstuv', '555-0103', '1978-11-08', '789 Pine Rd, City'),
('Sarah Williams', 'sarah.williams@email.com', '$2y$10$abcdefghijklmnopqrstuv', '555-0104', '1992-07-30', '321 Elm St, City'),
('David Brown', 'david.brown@email.com', '$2y$10$abcdefghijklmnopqrstuv', '555-0105', '1980-01-25', '654 Maple Dr, City');

-- Insert sample appointments
INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, visit_reason, duration_minutes) VALUES
(1, 1, '2026-03-31', '09:00:00', 'Confirmed', 'General check-up', 30),
(2, 1, '2026-03-31', '09:30:00', 'Pending', 'Follow-up consultation', 15),
(3, 1, '2026-03-31', '10:30:00', 'Pending', 'New patient consultation', 30),
(4, 1, '2026-03-31', '11:00:00', 'Confirmed', 'Annual physical', 45),
(5, 1, '2026-03-31', '14:00:00', 'Pending', 'Headache evaluation', 30);

-- Insert sample comments
INSERT INTO comments (appointment_id, comment_text, created_at) VALUES
(1, 'Patient showed improvement after medication adjustment. Continue current treatment.', '2026-03-28 10:30:00'),
(1, 'Initial consultation. Prescribed blood work and scheduled follow-up.', '2026-03-21 09:15:00'),
(2, 'Reviewing progress from previous treatment. Patient reports mild improvement.', '2026-03-15 14:00:00');

-- Insert sample availability for Dr. Sarah Lim (doctor_id = 1)
INSERT INTO doctor_availability (doctor_id, day_of_week, is_active, start_time, end_time, break_minutes) VALUES
(1, 'Monday', TRUE, '09:00:00', '17:00:00', 5),
(1, 'Tuesday', TRUE, '09:00:00', '17:00:00', 5),
(1, 'Wednesday', TRUE, '09:00:00', '17:00:00', 5),
(1, 'Thursday', FALSE, '09:00:00', '17:00:00', 5),
(1, 'Friday', TRUE, '09:00:00', '17:00:00', 5);
