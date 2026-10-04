-- Fresh database setup for Elegance Salon Management.
-- Import this file in phpMyAdmin. It recreates salon_management from scratch.
DROP DATABASE IF EXISTS `salon_management`;
CREATE DATABASE `salon_management` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `salon_management`;

CREATE TABLE users (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  address VARCHAR(300) NOT NULL DEFAULT '',
  phone VARCHAR(30) NOT NULL DEFAULT '',
  gender VARCHAR(30) NOT NULL DEFAULT '',
  city VARCHAR(100) NOT NULL DEFAULT '',
  role ENUM('admin','receptionist','stylist','client') NOT NULL DEFAULT 'client',
  status ENUM('pending','approved') NOT NULL DEFAULT 'approved',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE appointments (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  stylist_id INT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  service VARCHAR(100) NOT NULL,
  status ENUM('scheduled','confirmed','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_appointments_date (appointment_date),
  INDEX idx_appointments_client (client_id),
  CONSTRAINT fk_appointments_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_appointments_stylist FOREIGN KEY (stylist_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE payments (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  client_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date DATE NOT NULL,
  status ENUM('paid','pending','refunded') NOT NULL DEFAULT 'paid',
  service VARCHAR(100) NOT NULL,
  payment_method VARCHAR(30) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payments_date (payment_date),
  INDEX idx_payments_client (client_id),
  CONSTRAINT fk_payments_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE inventory (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(100) NOT NULL,
  category VARCHAR(100) NOT NULL,
  supplier VARCHAR(150) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  min_quantity INT NOT NULL DEFAULT 0,
  unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE inventory_catalog (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(100) NOT NULL UNIQUE,
  category VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE inventory_suppliers (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  supplier_name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO inventory_catalog (item_name, category) VALUES
('Shampoo', 'Hair Products'), ('Conditioner', 'Hair Products'), ('Hair Color', 'Hair Products'),
('Hair Developer', 'Hair Products'), ('Hair Serum', 'Hair Products'), ('Hair Mask', 'Hair Products'),
('Heat Protectant', 'Hair Products'), ('Hair Spray', 'Hair Products'),
('Facial Cleanser', 'Beauty Supplies'), ('Moisturizer', 'Beauty Supplies'), ('Face Mask', 'Beauty Supplies'),
('Cotton Pads', 'Beauty Supplies'), ('Nail Polish', 'Beauty Supplies'), ('Nail Polish Remover', 'Beauty Supplies'),
('Disinfectant', 'Beauty Supplies'), ('Gloves', 'Beauty Supplies'),
('Hair Dryer', 'Tools & Equipment'), ('Flat Iron', 'Tools & Equipment'), ('Curling Iron', 'Tools & Equipment'),
('Hair Clippers', 'Tools & Equipment'), ('Hair Scissors', 'Tools & Equipment'), ('Manicure Kit', 'Tools & Equipment'),
('Facial Steamer', 'Tools & Equipment');

CREATE TABLE feedback (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  username VARCHAR(100) NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comments TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_feedback_rating CHECK (rating BETWEEN 1 AND 5),
  CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE contact_messages (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  CONSTRAINT fk_contact_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE message_status (
  user_id INT NOT NULL,
  message_id INT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  is_cleared TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id, message_id),
  CONSTRAINT fk_message_status_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_status_message FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notifications (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  notification_type VARCHAR(40) NOT NULL,
  reference_id INT NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  is_cleared TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY user_notice (user_id, notification_type, reference_id),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE site_content (
  content_key VARCHAR(60) NOT NULL PRIMARY KEY,
  content_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO site_content (content_key, content_value) VALUES
('hero_title', 'Discover Your Signature Style'),
('hero_subtitle', 'Thoughtful care and expert artistry for every visit.'),
('about_paragraph', 'Welcome to Elegance Salon. Our team offers personalized beauty services in a warm, comfortable setting.');

-- Optional first administrator after importing:
-- Register a normal account in the app, then run (replace the email):
-- UPDATE users SET role='admin', status='approved' WHERE email='you@example.com';
