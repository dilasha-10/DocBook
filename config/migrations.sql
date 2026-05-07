-- ============================================================
-- DocBook Admin – Feature migrations
-- D2-01  Departments / Specializations  (uses existing categories table)
-- D2-02  Appointment Auditing
-- D2-03  Announcements
-- D2-05  Support Tickets
-- ============================================================

-- D2-01: Add description column to categories if missing
ALTER TABLE categories ADD COLUMN IF NOT EXISTS description VARCHAR(500) DEFAULT NULL AFTER icon;
ALTER TABLE categories ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER description;

-- D2-01: Specializations table
CREATE TABLE IF NOT EXISTS specializations (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    category_id  INT NOT NULL,
    name         VARCHAR(150) NOT NULL,
    slug         VARCHAR(150) NOT NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_spec_name_cat (category_id, name),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- D2-05: Support Tickets
CREATE TABLE IF NOT EXISTS support_tickets (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    patient_id    INT NOT NULL,
    subject       VARCHAR(255) NOT NULL,
    message       TEXT NOT NULL,
    category      ENUM('login_issue','payment_query','appointment_issue','account_issue','other') NOT NULL DEFAULT 'other',
    status        ENUM('open','in_progress','resolved') NOT NULL DEFAULT 'open',
    admin_reply   TEXT DEFAULT NULL,
    replied_by    INT DEFAULT NULL,
    replied_at    DATETIME DEFAULT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- D2-03: Announcements
CREATE TABLE IF NOT EXISTS announcements (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(255) NOT NULL,
    message       TEXT NOT NULL,
    type          ENUM('info','warning','success','urgent') NOT NULL DEFAULT 'info',
    target_roles  VARCHAR(100) NOT NULL DEFAULT 'all',
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    starts_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at    DATETIME DEFAULT NULL,
    created_by    INT NOT NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
