-- Jalankan SETELAH schema.sql. Password semua akun contoh: password  (GANTI sebelum dipakai sungguhan)
INSERT INTO departments (code, name) VALUES
 ('AKT','Akunting'),('HRU','HR dan Utility'),('MAT','Material'),('EXIM','Exim'),
 ('SLS','Sales'),('STP','Stamping'),('QA','QA'),('RND','RnD');

INSERT INTO users (name, email, password, role, department_id) VALUES
 ('Developer','dev@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','dev',NULL),
 ('Admin HRU','admin.hru@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',(SELECT id FROM departments WHERE code='HRU')),
 ('Manager HRU','manager.hru@example.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','manager',(SELECT id FROM departments WHERE code='HRU'));
