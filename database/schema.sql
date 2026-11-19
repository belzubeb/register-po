-- Jalankan di phpMyAdmin (tab SQL) pada database register_po yang masih kosong.
-- Mesin InnoDB wajib supaya penguncian nomor urut bekerja.
SET NAMES utf8mb4;

CREATE TABLE departments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(10) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin',      -- admin | manager | dev
  department_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_dept FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE documents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(3) NOT NULL,                        -- PRP | PO
  department_id INT UNSIGNED NOT NULL,
  related_department_id INT UNSIGNED NULL,
  seq INT UNSIGNED NULL,
  year SMALLINT UNSIGNED NULL,
  number VARCHAR(40) NULL,
  revision INT UNSIGNED NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  priority VARCHAR(10) NOT NULL DEFAULT 'sedang',
  subject VARCHAR(255) NULL,
  vendor VARCHAR(255) NULL,
  pay_type VARCHAR(10) NULL,
  ref_document_id INT UNSIGNED NULL,
  w5 LONGTEXT NULL,
  created_by INT UNSIGNED NOT NULL,
  local_by INT UNSIGNED NULL,
  local_at DATETIME NULL,
  korea_result VARCHAR(10) NULL,
  korea_by INT UNSIGNED NULL,
  korea_at DATETIME NULL,
  korea_note TEXT NULL,
  reject_reason TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_number (type, department_id, year, seq),
  KEY idx_dept_status (department_id, status),
  CONSTRAINT fk_doc_dept FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_doc_rel FOREIGN KEY (related_department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT fk_doc_ref FOREIGN KEY (ref_document_id) REFERENCES documents(id) ON DELETE SET NULL,
  CONSTRAINT fk_doc_creator FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  document_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  qty DECIMAL(12,2) NOT NULL DEFAULT 1,
  unit VARCHAR(20) NOT NULL DEFAULT 'pcs',
  price DECIMAL(15,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_item_doc FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_revisions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  document_id INT UNSIGNED NOT NULL,
  revision INT UNSIGNED NOT NULL,
  snapshot LONGTEXT NOT NULL,
  reason TEXT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_rev_doc FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  document_id INT UNSIGNED NULL,
  user_id INT UNSIGNED NULL,
  action VARCHAR(255) NOT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL,
  KEY idx_created (created_at),
  CONSTRAINT fk_log_doc FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL,
  CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE number_counters (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  department_id INT UNSIGNED NOT NULL,
  type VARCHAR(3) NOT NULL,
  year SMALLINT UNSIGNED NOT NULL,
  last_seq INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_counter (department_id, type, year),
  CONSTRAINT fk_cnt_dept FOREIGN KEY (department_id) REFERENCES departments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
