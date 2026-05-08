<?php
// session_start(); // Handled by Laravel middleware in PageController
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
// Bridge Laravel session to $_SESSION for legacy compatibility
if (function_exists('session') && session()->isStarted()) {
    foreach (session()->all() as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

date_default_timezone_set('Asia/Jakarta');

// Global action variable from GET or POST
$action = $_REQUEST['ajax'] ?? $_REQUEST['action'] ?? null;

// Production-optimized PHP settings
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php-error.log');
ini_set('log_errors_max_len', '1024'); // Limit error log entry size
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never show errors in production

// Increase limits for large datasets (production hosting)
@ini_set('memory_limit', '256M'); // Increase from default 128M
@ini_set('max_execution_time', '60'); // Prevent infinite hangs

error_log('bootstrap: core.php started');

// Include helpers
require_once __DIR__ . '/backup_helper.php';

// Load Composer autoloader for Google Authenticator
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// ----- CONFIG -----
// Change if needed for your XAMPP/MySQL setup
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'laravel_absen_db';

// Include database backup functions (if exists)
if (file_exists('database_backup.php')) {
    require_once 'database_backup.php';
}

// Default admin (seeded if not exists)
$DEFAULT_ADMIN_EMAIL = 'admin@example.com';
$DEFAULT_ADMIN_PASSWORD = 'admin123';

/**
 * Cleanup old attendance photos after 10 days to save storage.
 * Keeps expressions for historical visualization.
 */
function cleanupOldAttendancePhotos(PDO $pdo): int {
    $tenDaysAgo = date('Y-m-d', strtotime('-14 days'));
    
    // Set photos, screenshots, and landmarks to NULL if date is older than 14 days (approx 10 working days)
    // We keep the expression to show labels
    $stmt = $pdo->prepare("
        UPDATE attendance 
        SET foto_masuk = NULL, 
            screenshot_masuk = NULL,
            landmark_masuk = NULL, 
            foto_pulang = NULL, 
            screenshot_pulang = NULL,
            landmark_pulang = NULL 
        WHERE DATE(jam_masuk_iso) < :date
        AND (foto_masuk IS NOT NULL OR screenshot_masuk IS NOT NULL OR foto_pulang IS NOT NULL OR screenshot_pulang IS NOT NULL OR landmark_masuk IS NOT NULL OR landmark_pulang IS NOT NULL)
    ");
    $stmt->execute([':date' => $tenDaysAgo]);
    
    return $stmt->rowCount();
}

/**
 * Translate face expressions to Indonesian labels
 */
function translateExpression(?string $expression): string {
    if (empty($expression) || $expression === 'neutral') return 'Netral';
    
    $map = [
        'neutral' => 'Netral',
        'happy' => 'Senang',
        'sad' => 'Sedih',
        'angry' => 'Marah',
        'fearful' => 'Takut',
        'disgusted' => 'Jijik',
        'surprised' => 'Terkejut'
    ];
    
    $lower = strtolower($expression);
    return $map[$lower] ?? ucfirst($expression);
}

/**
 * Get CSS classes for expression labels
 */
function getExpressionClass(?string $expression): string {
    if (empty($expression) || $expression === 'neutral') return 'bg-blue-50 text-blue-600 border border-blue-100';
    
    $map = [
        'neutral' => 'bg-blue-50 text-blue-600 border border-blue-100',
        'happy' => 'bg-green-50 text-green-600 border border-green-100',
        'sad' => 'bg-gray-50 text-gray-600 border border-gray-100',
        'angry' => 'bg-red-50 text-red-600 border border-red-100',
        'fearful' => 'bg-purple-50 text-purple-600 border border-purple-100',
        'disgusted' => 'bg-orange-50 text-orange-600 border border-orange-100',
        'surprised' => 'bg-yellow-50 text-yellow-600 border border-yellow-100'
    ];
    
    $lower = strtolower($expression);
    return $map[$lower] ?? 'bg-gray-50 text-gray-600 border border-gray-100';
}

// ----- DB SETUP -----
function getPdo(): PDO {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    
    // Use env() if available (Laravel environment), otherwise fallback to globals
    $host = function_exists('env') ? env('DB_HOST', $DB_HOST) : $DB_HOST;
    $name = function_exists('env') ? env('DB_DATABASE', $DB_NAME) : $DB_NAME;
    $user = function_exists('env') ? env('DB_USERNAME', $DB_USER) : $DB_USER;
    $pass = function_exists('env') ? env('DB_PASSWORD', $DB_PASS) : $DB_PASS;

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Gagal terhubung ke database. Silakan periksa konfigurasi database Anda.");
    }
}

function ensureSchema(PDO $pdo): void {
    // users: role admin/pegawai, foto disimpan base64 data URL
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role ENUM('admin','pegawai') NOT NULL DEFAULT 'pegawai',
            email VARCHAR(255) NOT NULL UNIQUE,
            nim VARCHAR(100) NULL UNIQUE,
            nama VARCHAR(255) NOT NULL,
            prodi VARCHAR(255) NULL,
            startup VARCHAR(255) NULL,
                    foto_base64 LONGTEXT NULL,
                    face_embedding LONGTEXT NULL,
                    face_embedding_updated TIMESTAMP NULL,
                    advanced_features LONGTEXT NULL,
                    facial_geometry LONGTEXT NULL,
                    feature_vector LONGTEXT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // attendance
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            jam_masuk VARCHAR(20) NULL,
            jam_masuk_iso DATETIME NULL,
            ekspresi_masuk VARCHAR(50) NULL,
            foto_masuk LONGTEXT NULL,
            landmark_masuk LONGTEXT NULL,
            lokasi_masuk VARCHAR(255) NULL,
            lat_masuk DECIMAL(10,7) NULL,
            lng_masuk DECIMAL(10,7) NULL,
            jam_pulang VARCHAR(20) NULL,
            jam_pulang_iso DATETIME NULL,
            ekspresi_pulang VARCHAR(50) NULL,
            foto_pulang LONGTEXT NULL,
            landmark_pulang LONGTEXT NULL,
            lokasi_pulang VARCHAR(255) NULL,
            lat_pulang DECIMAL(10,7) NULL,
            lng_pulang DECIMAL(10,7) NULL,
            status ENUM('ontime','terlambat') DEFAULT 'ontime',
            ket ENUM('wfo','izin','sakit','alpha','wfa') DEFAULT 'wfo',
            alasan_wfa TEXT NULL,
            alasan_overtime TEXT NULL,
            lokasi_overtime VARCHAR(255) NULL,
            alasan_izin_sakit TEXT NULL,
            bukti_izin_sakit LONGTEXT NULL,
            daily_report_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            CONSTRAINT fk_att_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // settings table for admin configuration
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // manual_holidays table for admin-defined off days (e.g., demo/disaster)
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS manual_holidays (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date DATE NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(date),
            CONSTRAINT fk_manual_holidays_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // employee_work_schedule table for individual work schedules
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS employee_work_schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
            is_working_day BOOLEAN DEFAULT TRUE,
            start_time TIME DEFAULT '08:00:00',
            end_time TIME DEFAULT '17:00:00',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX(user_id),
            CONSTRAINT fk_schedule_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_day (user_id, day_of_week)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // Add missing columns if they don't exist (for existing databases)
    $requiredColumns = [
        'ekspresi_masuk' => "ALTER TABLE attendance ADD COLUMN ekspresi_masuk VARCHAR(50) NULL AFTER jam_masuk_iso",
        'ekspresi_pulang' => "ALTER TABLE attendance ADD COLUMN ekspresi_pulang VARCHAR(50) NULL AFTER jam_pulang_iso",
        'foto_masuk' => "ALTER TABLE attendance ADD COLUMN foto_masuk LONGTEXT NULL AFTER ekspresi_masuk",
        'foto_pulang' => "ALTER TABLE attendance ADD COLUMN foto_pulang LONGTEXT NULL AFTER ekspresi_pulang",
        'status' => "ALTER TABLE attendance ADD COLUMN status ENUM('ontime','terlambat') DEFAULT 'ontime' AFTER ekspresi_pulang",
        'ket' => "ALTER TABLE attendance ADD COLUMN ket ENUM('wfo','izin','sakit','alpha','wfa','overtime') DEFAULT 'wfo' AFTER status",
        'lokasi_masuk' => "ALTER TABLE attendance ADD COLUMN lokasi_masuk VARCHAR(255) NULL AFTER foto_masuk",
        'lat_masuk' => "ALTER TABLE attendance ADD COLUMN lat_masuk DECIMAL(10,7) NULL AFTER lokasi_masuk",
        'lng_masuk' => "ALTER TABLE attendance ADD COLUMN lng_masuk DECIMAL(10,7) NULL AFTER lat_masuk",
        'lokasi_pulang' => "ALTER TABLE attendance ADD COLUMN lokasi_pulang VARCHAR(255) NULL AFTER foto_pulang",
        'lat_pulang' => "ALTER TABLE attendance ADD COLUMN lat_pulang DECIMAL(10,7) NULL AFTER lokasi_pulang",
        'lng_pulang' => "ALTER TABLE attendance ADD COLUMN lng_pulang DECIMAL(10,7) NULL AFTER lat_pulang",
        'alasan_wfa' => "ALTER TABLE attendance ADD COLUMN alasan_wfa TEXT NULL AFTER ket",
        'alasan_overtime' => "ALTER TABLE attendance ADD COLUMN alasan_overtime TEXT NULL AFTER alasan_wfa",
        'lokasi_overtime' => "ALTER TABLE attendance ADD COLUMN lokasi_overtime VARCHAR(255) NULL AFTER alasan_overtime",
        'alasan_izin_sakit' => "ALTER TABLE attendance ADD COLUMN alasan_izin_sakit TEXT NULL AFTER lokasi_overtime",
        'bukti_izin_sakit' => "ALTER TABLE attendance ADD COLUMN bukti_izin_sakit LONGTEXT NULL AFTER alasan_izin_sakit",
        'daily_report_id' => "ALTER TABLE attendance ADD COLUMN daily_report_id INT NULL AFTER ket",
        'alasan_pulang_awal' => "ALTER TABLE attendance ADD COLUMN alasan_pulang_awal TEXT NULL AFTER bukti_izin_sakit",
        'alasan_lokasi_berbeda' => "ALTER TABLE attendance ADD COLUMN alasan_lokasi_berbeda TEXT NULL AFTER alasan_pulang_awal"
    ];
    
            // Add FaceNet embedding columns to users table
            $userColumns = [
                'face_embedding' => "ALTER TABLE users ADD COLUMN face_embedding LONGTEXT NULL AFTER foto_base64",
                'face_embedding_updated' => "ALTER TABLE users ADD COLUMN face_embedding_updated TIMESTAMP NULL AFTER face_embedding",
                'advanced_features' => "ALTER TABLE users ADD COLUMN advanced_features LONGTEXT NULL AFTER face_embedding_updated",
                'facial_geometry' => "ALTER TABLE users ADD COLUMN facial_geometry LONGTEXT NULL AFTER advanced_features",
                'feature_vector' => "ALTER TABLE users ADD COLUMN feature_vector LONGTEXT NULL AFTER facial_geometry",
                'google_authenticator_secret' => "ALTER TABLE users ADD COLUMN google_authenticator_secret VARCHAR(255) NULL AFTER password_hash",
                'password_reset_token' => "ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(255) NULL AFTER google_authenticator_secret",
                'password_reset_expires' => "ALTER TABLE users ADD COLUMN password_reset_expires DATETIME NULL AFTER password_reset_token"
            ];
    
    foreach ($requiredColumns as $column => $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Column already exists, ignore error
        }
    }
    
    // Add FaceNet embedding columns to users table
    foreach ($userColumns as $column => $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Column already exists, ignore error
        }
    }
    
    // Update ket column enum to include 'overtime'
    try { 
        $pdo->exec("ALTER TABLE attendance MODIFY ket ENUM('wfo','izin','sakit','alpha','wfa','overtime') DEFAULT 'wfo'"); 
    } catch (PDOException $e) {
        // Ignore error if column doesn't exist or enum is already correct
    }

    // Fix manual_holidays table structure if needed
    try {
        // Check if table exists
        $checkTable = $pdo->query("SHOW TABLES LIKE 'manual_holidays'");
        if ($checkTable->rowCount() > 0) {
            // Check if created_by column exists
            $checkColumn = $pdo->query("SHOW COLUMNS FROM manual_holidays LIKE 'created_by'");
            if ($checkColumn->rowCount() == 0) {
                // Add created_by column if it doesn't exist
                $pdo->exec("ALTER TABLE manual_holidays ADD COLUMN created_by INT NULL AFTER name");
                $pdo->exec("ALTER TABLE manual_holidays ADD CONSTRAINT fk_manual_holidays_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
            }
        } else {
            // Table doesn't exist, create it
            $pdo->exec("
                CREATE TABLE manual_holidays (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    date DATE NOT NULL UNIQUE,
                    name VARCHAR(255) NOT NULL,
                    created_by INT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX(date),
                    CONSTRAINT fk_manual_holidays_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }
    } catch (PDOException $e) {
        error_log("Error fixing manual_holidays table: " . $e->getMessage());
    }

    // Admin help requests table
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS admin_help_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            request_type ENUM('past_attendance', 'late_attendance', 'bug_report') NOT NULL,
            tanggal DATE NULL,
            jam_masuk TIME NULL,
            jam_pulang TIME NULL,
            alasan_izin TEXT NULL,
            jenis_izin ENUM('izin', 'sakit') NULL,
            bukti_izin LONGTEXT NULL,
            bukti_presensi LONGTEXT NULL,
            lokasi_presensi VARCHAR(255) NULL,
            bug_description TEXT NULL,
            bug_proof LONGTEXT NULL,
            status ENUM('pending', 'approved', 'disapproved', 'solved') DEFAULT 'pending',
            admin_note TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            INDEX(status),
            CONSTRAINT fk_ahr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Migration for status ENUM in admin_help_requests
    try {
        $pdo->exec("ALTER TABLE admin_help_requests MODIFY COLUMN status ENUM('pending', 'approved', 'disapproved', 'solved') DEFAULT 'pending'");
    } catch (PDOException $e) {}

    // Add is_read_by_user column for employee notifications
    try {
        $pdo->exec("ALTER TABLE admin_help_requests ADD COLUMN is_read_by_user BOOLEAN DEFAULT FALSE AFTER admin_note");
    } catch (PDOException $e) {}

    // Migration: add attendance_type and attendance_reason columns
    try {
        $pdo->exec("ALTER TABLE admin_help_requests ADD COLUMN attendance_type ENUM('wfo', 'wfa', 'overtime') DEFAULT 'wfo' AFTER request_type");
    } catch (PDOException $e) {} // Ignore if already exists
    try {
        $pdo->exec("ALTER TABLE admin_help_requests ADD COLUMN attendance_reason TEXT NULL AFTER attendance_type");
    } catch (PDOException $e) {} // Ignore if already exists

    // Attendance notes table
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS attendance_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            type ENUM('izin','sakit') NOT NULL,
            keterangan TEXT NOT NULL,
            bukti LONGTEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            UNIQUE KEY unique_user_date (user_id, date),
            CONSTRAINT fk_an_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Monthly reports table
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS monthly_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            year INT NOT NULL,
            month INT NOT NULL,
            summary TEXT NULL,
            achievements JSON NULL,
            obstacles JSON NULL,
            status ENUM('draft','belum di approve','approved','disapproved') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            UNIQUE KEY uniq_user_month (user_id, year, month),
            CONSTRAINT fk_mr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    
    // Update existing monthly_reports table to use new ENUM values
    try {
        $pdo->exec("ALTER TABLE monthly_reports MODIFY COLUMN status ENUM('draft','belum di approve','approved','disapproved') DEFAULT 'draft'");
        // Update any existing 'submitted' status to 'belum di approve'
        $pdo->exec("UPDATE monthly_reports SET status = 'belum di approve' WHERE status = 'submitted'");
    } catch (PDOException $e) {
        // Ignore if column doesn't exist or already updated
        error_log("Monthly reports table update: " . $e->getMessage());
    }
}

function verifyAttendanceTable(PDO $pdo): bool {
    try {
        // Check if attendance table exists and has required columns
        $stmt = $pdo->query("DESCRIBE attendance");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $requiredColumns = ['id', 'user_id', 'jam_masuk', 'jam_masuk_iso', 'ekspresi_masuk', 'foto_masuk', 'jam_pulang', 'jam_pulang_iso', 'ekspresi_pulang', 'foto_pulang', 'status', 'ket'];
        $missingColumns = array_diff($requiredColumns, $columns);
        
        if (!empty($missingColumns)) {
            error_log("Missing columns in attendance table: " . implode(', ', $missingColumns));
            return false;
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error verifying attendance table: " . $e->getMessage());
        return false;
    }
}

function seedAdmin(PDO $pdo, string $email, string $password): void {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
    $stmt->execute();
    $existing = $stmt->fetch();
    if (!$existing) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (role, email, nim, nama, prodi, startup, foto_base64, password_hash) VALUES ('admin', :email, NULL, 'Administrator', NULL, NULL, NULL, :hash)");
        $stmt->execute([':email' => $email, ':hash' => $hash]);
    }
}

function seedDefaultSettings(PDO $pdo): void {
    $defaultSettings = [
        ['max_ontime_hour', '08', 'Jam maksimal untuk dianggap ontime (format 24 jam)'],
        ['min_checkout_hour', '17', 'Jam minimal untuk bisa presensi pulang (format 24 jam)'],
        ['wfo_address', 'Fakultas Ilmu Terapan, Jl. Telekomunikasi, Bandung', 'Nama alamat pusat WFO (akan di-geocode)'],
        ['wfo_lat', '-6.9738', 'Latitude pusat WFO'],
        ['wfo_lng', '107.6300', 'Longitude pusat WFO'],
        ['wfo_radius_m', '1200', 'Radius wilayah WFO dalam meter'],
        // WFO detection via IP API settings
        ['wfo_mode', 'api', 'Mode deteksi WFO: api atau coordinate'],
        ['wfo_api_provider', 'ipinfo', 'Provider IP API: ipinfo | ipapi | ip-api'],
        ['wfo_api_token', '', 'Token API (opsional tergantung provider)'],
        ['wfo_api_org_keywords', 'Telkom University, Yayasan Pendidikan Telkom, Telkom University Bandung', 'Daftar kata kunci organisasi yang dianggap WFO (dipisah koma)'],
        ['wfo_api_asn_list', '', 'Daftar ASN yang dianggap WFO (contoh: AS7713), dipisah koma'],
        ['wfo_api_cidr_list', '', 'Daftar CIDR yang dianggap WFO (contoh: 103.23.44.0/22), dipisah koma'],
        ['wfo_wifi_ssids', 'Telkom University,TelU,WiFi Telkom University,WiFi-TelU,Telkom-University,TelU-Connect,TelU-Guest', 'Daftar SSID WiFi yang valid untuk WFO (dipisah koma)'],
        ['wfo_require_wifi', '1', 'Wajib menggunakan WiFi Telkom University untuk presensi WFO (1=Ya, 0=Tidak)'],
        ['attendance_period_end', date('Y-12-31'), 'Tanggal akhir periode perhitungan absen (YYYY-MM-DD)'],
        ['kpi_late_penalty_per_minute', '1', 'Pengurangan KPI per menit terlambat (%)'],
        ['kpi_izin_sakit_score', '85', 'Nilai KPI untuk izin/sakit (%)'],
        ['kpi_alpha_score', '0', 'Nilai KPI untuk alpha (%)'],
        ['kpi_overtime_bonus', '5', 'Bonus KPI untuk overtime (%)'],
        ['max_daily_report_days_back', '5', 'Maksimal hari kebelakang untuk isi laporan harian (default: 5)'],
        ['max_monthly_report_months_back', '999', 'Maksimal bulan kebelakang untuk isi laporan bulanan (default: 999 = tidak terbatas)'],
        ['monthly_report_end_year', '2026', 'Tahun akhir untuk laporan bulanan (default: 2026)'],
        ['face_recognition_threshold', '0.38', 'Threshold untuk face recognition (0.0-1.0, semakin rendah semakin ketat, default: 0.38)'],
        ['face_recognition_input_size', '416', 'Ukuran input untuk face detection (semakin besar semakin akurat tapi lebih lambat, default: 416)'],
        ['face_recognition_score_threshold', '0.35', 'Score threshold untuk face detection (0.0-1.0, default: 0.35)'],
        ['face_recognition_quality_threshold', '0.55', 'Quality threshold untuk validasi wajah (0.0-1.0, default: 0.55)'],
        ['geocode_timeout', '3', 'Timeout untuk reverse geocoding dalam detik (default: 3)'],
        ['geocode_accuracy_radius', '50', 'Radius akurasi GPS dalam meter untuk validasi lokasi (default: 50)']
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = :key LIMIT 1");
        $stmt->execute([':key' => $setting[0]]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES (:key, :value, :desc)");
            $stmt->execute([':key' => $setting[0], ':value' => $setting[1], ':desc' => $setting[2]]);
        }
    }
}

/**
 * Robust HTTP request helper that tries cURL first and file_get_contents as fallback.
 */
function httpRequest(string $url, array $headers = [], int $timeout = 10): ?string {
    // Try cURL
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $resp) return $resp;
    }
    
    // Try file_get_contents as fallback
    if (ini_get('allow_url_fopen')) {
        $headerStr = "";
        foreach ($headers as $h) {
            $headerStr .= $h . "\r\n";
        }
        
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => $headerStr ?: "User-Agent: AbsenApp/1.0\r\n",
                "timeout" => $timeout
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ];
        $context = stream_context_create($opts);
        $resp = @file_get_contents($url, false, $context);
        if ($resp) return $resp;
    }
    
    return null;
}

/**
 * Search for addresses using Google Geocoding API.
 * Returns an array of results with display_name, lat, and lon.
 */
function searchAddressGoogle(string $query): array {
    $apiKey = 'AIzaSyCTdOHXg5hSu_2fneyBP9mItCLyG5VQ-x0';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($query) . "&key={$apiKey}&language=id&region=id";
    
    $resp = httpRequest($url);
    
    if (!$resp) {
        // Fallback to Nominatim if Google fails
        return searchAddressNominatim($query);
    }
    
    $data = json_decode($resp, true);
    if (!isset($data['status']) || ($data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') || empty($data['results'])) {
        return searchAddressNominatim($query);
    }
    
    $results = [];
    foreach ($data['results'] as $res) {
        $results[] = [
            'display_name' => $res['formatted_address'],
            'lat' => $res['geometry']['location']['lat'],
            'lon' => $res['geometry']['location']['lng'],
            'place_id' => $res['place_id'],
            'type' => 'google'
        ];
    }
    
    return $results;
}

/**
 * Search for addresses using Nominatim (fallback).
 */
function searchAddressNominatim(string $query): array {
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&addressdetails=1&countrycodes=id&q=' . urlencode($query);
    $headers = ['User-Agent: AbsenApp/1.0 (XAMPP PHP)'];
    
    $resp = httpRequest($url, $headers, 5);
    
    if (!$resp) return [];
    
    $data = json_decode($resp, true);
    if (!is_array($data)) return [];
    
    $results = [];
    foreach ($data as $res) {
        $results[] = [
            'display_name' => $res['display_name'],
            'lat' => $res['lat'],
            'lon' => $res['lon'],
            'place_id' => $res['place_id'],
            'type' => 'nominatim'
        ];
    }
    
    return $results;
}

/**
 * Geocode a free-form address string to [lat, lng] using OpenStreetMap Nominatim.
 * Returns ['lat' => float, 'lng' => float] or null on failure.
 */
function geocodeAddress(string $address): ?array {
    // Try Google first for better accuracy
    $googleResults = searchAddressGoogle($address);
    if (!empty($googleResults)) {
        return ['lat' => (float)$googleResults[0]['lat'], 'lng' => (float)$googleResults[0]['lon']];
    }
    
    $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=0&q=' . urlencode($address);
    $headers = ['User-Agent: AbsenApp/1.0 (XAMPP PHP)'];
    
    $resp = httpRequest($url, $headers, 4);
    if (!$resp) return null;
    
    $data = json_decode($resp, true);
    if (!is_array($data) || empty($data) || !isset($data[0]['lat'], $data[0]['lon'])) return null;
    return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
}

/**
 * Reverse geocode coordinates to address using MULTIPLE providers for maximum accuracy.
 * ENHANCED VERSION with RT/RW extraction and detailed Indonesian address parsing.
 * Returns complete address with street name, number, RT/RW, kelurahan, postal code.
 */
function reverseGeocodeAddress(float $lat, float $lng): ?string {
    // TIER 1: Try Google Maps API (MOST ACCURATE - PRIMARY)
    $googleAddress = reverseGeocodeGoogle($lat, $lng);
    if ($googleAddress && !isGenericAddress($googleAddress)) {
        error_log("Geocoding SUCCESS: Google Maps - $googleAddress");
        return $googleAddress;
    }
    
    // TIER 2: Try with zoom 18 (maximum detail)
    $detailedAddress = reverseGeocodeNominatim($lat, $lng, 18);
    if ($detailedAddress && !isGenericAddress($detailedAddress)) {
        error_log("Geocoding SUCCESS: OSM Zoom 18 - $detailedAddress");
        return $detailedAddress;
    }
    
    // TIER 3: Try with zoom 17 (slightly broader, might have more data)
    $mediumAddress = reverseGeocodeNominatim($lat, $lng, 17);
    if ($mediumAddress && !isGenericAddress($mediumAddress)) {
        error_log("Geocoding SUCCESS: OSM Zoom 17 - $mediumAddress");
        return $mediumAddress;
    }
    
    // TIER 4: Fallback to coordinates
    error_log("All geocoding methods failed for lat=$lat, lng=$lng, using coordinates fallback");
    return "Koordinat: " . round($lat, 6) . ", " . round($lng, 6);
}

/**
 * Google Maps Geocoding API - PRIMARY PROVIDER (Most Accurate for Indonesian Addresses)
 */
function reverseGeocodeGoogle(float $lat, float $lng): ?string {
    $apiKey = 'AIzaSyCTdOHXg5hSu_2fneyBP9mItCLyG5VQ-x0';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&key={$apiKey}&language=id&result_type=street_address|route|sublocality|premise";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$resp) {
        error_log("Google Geocoding API request failed: HTTP $httpCode, Error: $curlError");
        return null;
    }
    
    $data = json_decode($resp, true);
    
    if (!isset($data['status']) || $data['status'] !== 'OK') {
        $errorMsg = $data['error_message'] ?? $data['status'] ?? 'UNKNOWN';
        error_log("Google Geocoding API error: $errorMsg");
        return null;
    }
    
    if (empty($data['results'])) {
        error_log("Google Geocoding API: No results");
        return null;
    }
    
    // Get first result (most accurate)
    $result = $data['results'][0];
    $addressComponents = $result['address_components'] ?? [];
    $formattedAddress = $result['formatted_address'] ?? '';
    
    // Parse for Indonesian address format
    $houseNumber = '';
    $street = '';
    $rt = '';
    $rw = '';
    $kelurahan = '';
    $kecamatan = '';
    $city = '';
    $province = '';
    $postalCode = '';
    
    foreach ($addressComponents as $component) {
        $types = $component['types'];
        $longName = $component['long_name'];
        
        if (in_array('street_number', $types)) {
            $houseNumber = $longName;
        } elseif (in_array('route', $types)) {
            $street = $longName;
        } elseif (in_array('premise', $types) || in_array('establishment', $types)) {
            // Building name
            if (empty($houseNumber)) {
                $houseNumber = $longName;
            }
        } elseif (in_array('sublocality_level_4', $types) || in_array('neighborhood', $types)) {
            // Check for RT/RW
            if (preg_match('/RT[\s.]*0*([0-9]{1,3})[\s\/]*RW[\s.]*0*([0-9]{1,3})/i', $longName, $matches)) {
                $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT);
                $rw = str_pad($matches[2], 3, '0', STR_PAD_LEFT);
            } elseif (empty($kelurahan)) {
                $kelurahan = $longName;
            }
        } elseif (in_array('sublocality_level_3', $types) || in_array('administrative_area_level_4', $types)) {
            $kelurahan = $longName;
        } elseif (in_array('sublocality_level_2', $types) || in_array('administrative_area_level_3', $types)) {
            $kecamatan = $longName;
        } elseif (in_array('administrative_area_level_2', $types) || in_array('locality', $types)) {
            $city = $longName;
        } elseif (in_array('administrative_area_level_1', $types)) {
            $province = $longName;
        } elseif (in_array('postal_code', $types)) {
            $postalCode = $longName;
        }
    }
    
    // Build Indonesian address
    $parts = [];
    
    // Street with number
    if ($houseNumber && $street) {
        $parts[] = "No. $houseNumber, Jl. $street";
    } elseif ($street) {
        $parts[] = "Jl. $street";
    } elseif ($houseNumber) {
        $parts[] = $houseNumber;
    }
    
    // RT/RW
    if ($rt && $rw) {
        $parts[] = "RT $rt/RW $rw";
    }
    
    // Kelurahan
    if ($kelurahan) {
        $parts[] = $kelurahan;
    }
    
    // Kecamatan
    if ($kecamatan) {
        $parts[] = $kecamatan;
    }
    
    // City
    if ($city) {
        $parts[] = $city;
    }
    
    // Province  
    if ($province) {
        $parts[] = $province;
    }
    
    // Postal code
    if ($postalCode) {
        $parts[] = $postalCode;
    }
    
    // Return detailed address if we have enough components
    if (!empty($parts) && count($parts) >= 3) {
        return implode(', ', $parts);
    }
    
    // Fallback to Google's formatted address
    if ($formattedAddress) {
        $cleanAddress = preg_replace('/,\s*Indonesia\s*$/', '', $formattedAddress);
        return $cleanAddress;
    }
    
    return null;
}

/**
 * Helper: Check if address is too generic (just city + postal)
 */
function isGenericAddress(string $address): bool {
    // If address only has 1-2 components (just city and postal code), it's too generic
    $parts = explode(', ', $address);
    return count($parts) <= 2;
}

/**
 * Core reverse geocoding using Nominatim with specified zoom
 */
function reverseGeocodeNominatim(float $lat, float $lng, int $zoom): ?string {
    $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $lat . '&lon=' . $lng . '&addressdetails=1&accept-language=id&zoom=' . $zoom . '&extratags=1&namedetails=1';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // INCREASED from 1 to 5 seconds for better accuracy
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Connection timeout 3 seconds
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: AbsenApp/1.0 (XAMPP PHP)'
    ]);
    
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code !== 200 || !$resp) {
        // Fallback to coordinates if geocoding fails
        error_log("Reverse geocoding failed for lat=$lat, lng=$lng");
        return "Koordinat: " . round($lat, 6) . ", " . round($lng, 6);
    }
    
    $data = json_decode($resp, true);
    if (!is_array($data) || !isset($data['address'])) {
        return "Koordinat: " . round($lat, 6) . ", " . round($lng, 6);
    }
    
    $address = $data['address'];
    $displayName = $data['display_name'] ?? '';
    
    // ENHANCED: Extract RT/RW from various address components
    $rt = '';
    $rw = '';
    
    // Pattern untuk mencari RT/RW dalam format: "RT 001/RW 002", "RT.01 RW.02", "RT 1 RW 2", etc
    $rtRwPattern = '/RT[\s.]*0*([0-9]{1,3})[\s\/]*RW[\s.]*0*([0-9]{1,3})/i';
    
    // Check dalam berbagai field yang mungkin mengandung RT/RW
    $searchFields = ['suburb', 'neighbourhood', 'hamlet', 'quarter', 'city_district', 'residential'];
    foreach ($searchFields as $field) {
        if (isset($address[$field]) && $address[$field]) {
            if (preg_match($rtRwPattern, $address[$field], $matches)) {
                $rt = str_pad($matches[1], 3, '0', STR_PAD_LEFT); // Format: 001, 002, etc
                $rw = str_pad($matches[2], 3, '0', STR_PAD_LEFT);
                break;
            }
        }
    }
    
    // Build DETAILED address from components with proper Indonesian order
    $parts = [];
    
    // 1. Building name atau house name (paling spesifik)
    if (isset($address['building']) && $address['building']) {
        $parts[] = $address['building'];
    } elseif (isset($address['house_name']) && $address['house_name']) {
        $parts[] = $address['house_name'];
    } elseif (isset($address['amenity']) && $address['amenity']) {
        $parts[] = $address['amenity'];
    }
    
    // 2. Road/Street dengan house number jika ada
    $roadParts = [];
    if (isset($address['house_number']) && $address['house_number']) {
        $roadParts[] = 'No. ' . $address['house_number'];
    }
    if (isset($address['road']) && $address['road']) {
        $roadParts[] = 'Jl. ' . $address['road'];
    } elseif (isset($address['pedestrian']) && $address['pedestrian']) {
        $roadParts[] = 'Jl. ' . $address['pedestrian'];
    } elseif (isset($address['footway']) && $address['footway']) {
        $roadParts[] = 'Jl. ' . $address['footway'];
    } elseif (isset($address['path']) && $address['path']) {
        $roadParts[] = $address['path'];
    }
    if (!empty($roadParts)) {
        $parts[] = implode(' ', $roadParts);
    }
    
    // 3. RT/RW jika ditemukan
    if ($rt && $rw) {
        $parts[] = "RT $rt/RW $rw";
    }
    
    // 4. Kelurahan/Desa (suburb/neighbourhood)
    if (isset($address['suburb']) && $address['suburb']) {
        // Skip jika suburb sama dengan RT/RW pattern (sudah diambil di atas)
        if (!preg_match($rtRwPattern, $address['suburb'])) {
            $parts[] = $address['suburb'];
        }
    } elseif (isset($address['neighbourhood']) && $address['neighbourhood']) {
        if (!preg_match($rtRwPattern, $address['neighbourhood'])) {
            $parts[] = $address['neighbourhood'];
        }
    } elseif (isset($address['hamlet']) && $address['hamlet']) {
        $parts[] = $address['hamlet'];
    } elseif (isset($address['village']) && $address['village']) {
        $parts[] = $address['village'];
    }
    
    // 5. Kecamatan (city_district)
    if (isset($address['city_district']) && $address['city_district']) {
        $parts[] = $address['city_district'];
    } elseif (isset($address['municipality']) && $address['municipality']) {
        $parts[] = $address['municipality'];
    }
    
    // 6. Kota/Kabupaten
    if (isset($address['city']) && $address['city']) {
        $parts[] = $address['city'];
    } elseif (isset($address['town']) && $address['town']) {
        $parts[] = $address['town'];
    } elseif (isset($address['county']) && $address['county']) {
        $parts[] = $address['county'];
    }
    
    // 7. Provinsi
    if (isset($address['state']) && $address['state']) {
        $parts[] = $address['state'];
    }
    
    // 8. Postal code (PENTING untuk alamat lengkap)
    if (isset($address['postcode']) && $address['postcode']) {
        $parts[] = $address['postcode'];
    }
    
    // If we have good parts, join them
    if (!empty($parts)) {
        $detailedAddress = implode(', ', $parts);
        
        // Log untuk debugging
        error_log("Reverse geocoding success: $detailedAddress (RT: $rt, RW: $rw)");
        
        return $detailedAddress;
    }
    
    // Fallback to display_name if no parts extracted
    if ($displayName) {
        // Clean up the display name
        $cleanName = preg_replace('/,\s*Indonesia$/', '', $displayName);
        
        // Try to append postal code if available
        if (isset($address['postcode']) && $address['postcode']) {
            if (strpos($cleanName, $address['postcode']) === false) {
                $cleanName .= ', ' . $address['postcode'];
            }
        }
        
        error_log("Reverse geocoding fallback to display_name: $cleanName");
        return $cleanName;
    }
    
    // Final fallback to coordinates
    error_log("Reverse geocoding no address found, using coordinates");
    return "Koordinat: " . round($lat, 6) . ", " . round($lng, 6);
}

/** Check if IP within CIDR */
function ipInCidr(string $ip, string $cidr): bool {
    if (!str_contains($cidr, '/')) return false;
    [$subnet, $mask] = explode('/', $cidr, 2);
    $mask = (int)$mask;
    if (!filter_var($ip, FILTER_VALIDATE_IP) || !filter_var($subnet, FILTER_VALIDATE_IP)) return false;
    $ipLong = ip2long($ip);
    $subnetLong = ip2long($subnet);
    $maskLong = -1 << (32 - $mask);
    $subnetBase = $subnetLong & $maskLong;
    return ($ipLong & $maskLong) === $subnetBase;
}

/**
 * Fetch public IP info from provider
 */
function fetchPublicIpInfo(string $ip, string $provider, string $token = ''): array {
    $url = '';
    $headers = ['User-Agent: AbsenApp/1.0 (XAMPP PHP)'];
    if ($provider === 'ipinfo') {
        $url = 'https://ipinfo.io/' . urlencode($ip) . '/json' . ($token ? ('?token=' . urlencode($token)) : '');
    } elseif ($provider === 'ipapi') {
        $url = 'https://ipapi.co/' . urlencode($ip) . '/json/';
        if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    } else { // ip-api
        $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,message,org,as,asname,query';
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3); // Reduced from 5 to 3 seconds for faster response
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // Connection timeout 2 seconds
    if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$resp) return [];
    $data = json_decode($resp, true);
    if (!is_array($data)) return [];

    // Normalize fields
    $org = '';
    $asn = '';
    if ($provider === 'ipinfo') {
        $org = $data['company']['name'] ?? ($data['org'] ?? '');
        $asn = isset($data['org']) ? strtoupper(strtok($data['org'], ' ')) : '';
    } elseif ($provider === 'ipapi') {
        $org = $data['org'] ?? ($data['company'] ?? '');
        $asn = strtoupper($data['asn'] ?? ($data['as'] ?? ''));
    } else { // ip-api
        $org = $data['org'] ?? ($data['asname'] ?? '');
        $asn = strtoupper($data['as'] ?? '');
        if ($asn && !str_starts_with($asn, 'AS')) {
            $asn = strtoupper(strtok($asn, ' '));
        }
    }

    return [
        'org' => (string)$org,
        'asn' => (string)$asn,
        'raw' => $data,
    ];
}

/**
 * Check if IP is in Telkom University private IP range
 * Telkom University uses private IP ranges: 10.x.x.x
 */
function isTelkomUniversityPrivateIp(string $ip): bool {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    
    // Check if it's a private IP (10.x.x.x, 172.16-31.x.x, 192.168.x.x)
    $isPrivate = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    
    if (!$isPrivate) {
        return false; // Not a private IP
    }
    
    // Check if it's in Telkom University private IP range (10.x.x.x)
    // Based on screenshots: 10.60.43.33 (TelU-Connect) and 10.30.114.48 (TelU-Guest)
    // Telkom University uses 10.x.x.x range
    if (strpos($ip, '10.') === 0) {
        return true; // IP starts with 10. - likely Telkom University network
    }
    
    return false;
}

/**
 * Detect WFO by external IP information API or private IP range
 * Returns true if IP belongs to allowed org/ASN/CIDR list or Telkom University private IP range
 */
function isWfoByApi(PDO $pdo, ?string $publicIp = null): bool {
    // PERFORMANCE: Cache WFO check to avoid slow API calls
    $cacheKey = 'wfo_check_' . md5($publicIp ?? 'auto');
    if (isset($_SESSION[$cacheKey]) && $_SESSION[$cacheKey]['time'] > time() - 300) {
        return $_SESSION[$cacheKey]['result'];
    }
    
    $result = _isWfoByApiInternal($pdo, $publicIp);
    
    $_SESSION[$cacheKey] = ['time' => time(), 'result' => $result];
    return $result;
}

function _isWfoByApiInternal(PDO $pdo, ?string $publicIp = null): bool {
    $provider = strtolower(trim(getSetting($pdo, 'wfo_api_provider', 'ipinfo')));
    $token = trim(getSetting($pdo, 'wfo_api_token', ''));
    $orgKeywords = array_filter(array_map('trim', explode(',', getSetting($pdo, 'wfo_api_org_keywords', 'Telkom University'))));
    $asnList = array_filter(array_map('trim', explode(',', getSetting($pdo, 'wfo_api_asn_list', ''))));
    $cidrList = array_filter(array_map('trim', explode(',', getSetting($pdo, 'wfo_api_cidr_list', ''))));

    // Determine client public IP if not provided
    if (!$publicIp) {
        $publicIp = $_POST['public_ip'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if ($publicIp && strpos($publicIp, ',') !== false) {
            $parts = explode(',', $publicIp);
            $publicIp = trim($parts[0]);
        }
    }
    if (!$publicIp || !filter_var($publicIp, FILTER_VALIDATE_IP)) {
        return false; // cannot determine
    }

    // CRITICAL FIX: Check private IP range first (for laptops on Telkom University network)
    // This is important because laptops often get private IP (10.x.x.x) which cannot be validated via external API
    if (isTelkomUniversityPrivateIp($publicIp)) {
        error_log("WFO Private IP Check - IP: $publicIp, Result: VALID (Telkom University private IP range)");
        return true; // Private IP in Telkom University range - valid WFO
    }

    // For public IPs, check via external API
    // Skip API check for private IPs (they won't work with external APIs anyway)
    $isPrivate = filter_var($publicIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    if ($isPrivate) {
        // Private IP but not in Telkom University range
        return false;
    }

    // Check public IP via external API
    $info = fetchPublicIpInfo($publicIp, $provider, $token);
    $org = strtolower($info['org'] ?? '');
    $asn = strtoupper($info['asn'] ?? '');

    // Match org keywords
    foreach ($orgKeywords as $kw) {
        if ($kw !== '' && str_contains($org, strtolower($kw))) return true;
    }

    // Match ASN
    foreach ($asnList as $a) {
        if ($a !== '' && strtoupper(trim($a)) === $asn) return true;
    }

    // Match CIDR ranges
    foreach ($cidrList as $cidr) {
        if ($cidr !== '' && ipInCidr($publicIp, $cidr)) return true;
    }

    return false;
}

function getSetting(PDO $pdo, string $key, string $default = ''): string {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
    $stmt->execute([':key' => $key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function setSetting(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([':key' => $key, ':value' => $value]);
}

/**
 * Format bytes menjadi string yang mudah dibaca (KB, MB, GB, dsb.)
 */
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2): string {
        if ($bytes === null || $bytes === false) return '0 B';
        $bytes = (int)$bytes;
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = floor(log($bytes, 1024));
        $exp = min($exp, count($units) - 1);
        return round($bytes / pow(1024, $exp), $precision) . ' ' . $units[$exp];
    }
}

/**
 * Helper function untuk memanggil backup database setelah operasi yang mengubah data
 */
function triggerDatabaseBackup(): void {
    try {
        // Check if backup functions are available
        if (!function_exists('createDatabaseBackup')) {
            error_log("Backup functions not available");
            return;
        }
        
        // Create backup
        $backupResult = createDatabaseBackup();
        
        if (!($backupResult['ok'] ?? $backupResult['success'] ?? false)) {
            error_log("Backup gagal: " . $backupResult['message']);
        } else {
            $sizeStr = isset($backupResult['size']) ? formatBytes($backupResult['size']) : 'N/A';
            error_log("Backup berhasil: " . $backupResult['message'] . " (Size: " . $sizeStr . ")");
        }
    } catch (Exception $e) {
        error_log("Error dalam backup: " . $e->getMessage());
    }
}

try {
    $pdo = getPdo();
    // PERFORMANCE: Only run schema verification if explicitly requested
    if (isset($_GET['install_db'])) {
        ensureSchema($pdo);
        
        // Verify that the attendance table has all required columns
        if (!verifyAttendanceTable($pdo)) {
            error_log("Attendance table verification failed - attempting to fix schema");
            ensureSchema($pdo); // Try to fix the schema again
            if (!verifyAttendanceTable($pdo)) {
                throw new Exception("Failed to create proper attendance table schema");
            }
        }
        
        seedAdmin($pdo, $DEFAULT_ADMIN_EMAIL, $DEFAULT_ADMIN_PASSWORD);
        seedDefaultSettings($pdo);
    }
} catch (Exception $e) {
    error_log("Database initialization failed: " . $e->getMessage());
    if (isset($_GET['ajax'])) {
        jsonResponse(['error' => 'Database connection failed'], 500);
    }
    // For non-AJAX requests, we'll let the page load but show an error
}

// Helper function for JSON response
function jsonResponse($data, $status = 200) {
    if (ob_get_length()) ob_clean(); // Clear any previous output (BOM, whitespace)
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    if (session_id()) session_write_close();
    echo json_encode($data);
    exit;
}

function requireAuth(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: ?page=login');
        exit;
    }
}

function isAdmin(): bool { return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'; }
function isPegawai(): bool { return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'pegawai'; }

// Function to check if base64 image data is too large
function checkImageSize($dataUrl, $maxSizeMB = 5) {
    if (!$dataUrl || strpos($dataUrl, 'data:image/') !== 0) {
        return ['valid' => true, 'message' => '']; // Not a valid image data URL, skip check
    }
    
    // Extract base64 data from data URL
    $data = explode(',', $dataUrl, 2);
    if (count($data) !== 2) {
        return ['valid' => true, 'message' => '']; // Invalid format, skip check
    }
    
    $imageData = base64_decode($data[1]);
    if ($imageData === false) {
        return ['valid' => true, 'message' => '']; // Failed to decode, skip check
    }
    
    $sizeMB = strlen($imageData) / (1024 * 1024);
    if ($sizeMB > $maxSizeMB) {
        return ['valid' => false, 'message' => "Ukuran gambar terlalu besar ($sizeMB MB). Maksimal $maxSizeMB MB."];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Menyimpan gambar Base64 ke sistem file untuk mengurangi beban database.
 * @param string $base64String - Data URL gambar
 * @param string $subDir - Subdirektori di dalam public/storage/
 * @return string - Path relatif ke file yang disimpan, atau string asli jika gagal
 */
function saveBase64Image($base64String, $subDir) {
    if (!$base64String || strpos($base64String, 'data:image/') !== 0) {
        return $base64String; // Bukan base64 atau kosong
    }
    
    try {
        $parts = explode(',', $base64String, 2);
        if (count($parts) !== 2) {
            error_log("saveBase64Image: Invalid base64 format (missing comma)");
            return $base64String;
        }
        
        $data = base64_decode($parts[1]);
        if (!$data) {
            error_log("saveBase64Image: Failed to decode base64 data");
            return $base64String;
        }
        
        // Tentukan ekstensi
        $extension = 'jpg';
        if (strpos($parts[0], 'image/png') !== false) $extension = 'png';
        elseif (strpos($parts[0], 'image/webp') !== false) $extension = 'webp';
        
        $fileName = uniqid() . '_' . time() . '.' . $extension;
        $targetDir = public_path('storage/' . $subDir);
        
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log("saveBase64Image: Failed to create directory $targetDir");
                return $base64String;
            }
        }
        
        $filePath = $targetDir . '/' . $fileName;
        if (file_put_contents($filePath, $data) === false) {
            error_log("saveBase64Image: Failed to write file to $filePath");
            return $base64String;
        }
        
        $relativePath = 'storage/' . $subDir . '/' . $fileName;
        error_log("saveBase64Image: Success! Saved to $relativePath");
        return $relativePath;
    } catch (Exception $e) {
        error_log("saveBase64Image: Exception - " . $e->getMessage());
        return $base64String;
    }
}

/**
 * Helper to get the correct URL for a user's avatar
 * Supports: Data URL, storage path, raw filename, and raw base64.
 */
function getAvatarUrl($foto, $nama = 'A') {
    $default = 'https://ui-avatars.com/api/?background=4f46e5&color=fff&name=' . urlencode($nama) . '&size=128';
    if (empty($foto)) return $default;
    
    // If it's a data URL (Base64)
    if (strpos($foto, 'data:') === 0) return $foto;
    
    // If it's a path starting with storage/
    if (strpos($foto, 'storage/') === 0) {
        return '/' . $foto;
    }
    
    // If it's just a filename, assume it's in storage/users/
    if (strpos($foto, '.') !== false && strpos($foto, '/') === false) {
        return '/storage/users/' . $foto;
    }
    
    // If it's raw Base64 without data prefix (backward compatibility)
    if (strlen($foto) > 500) {
        return 'data:image/png;base64,' . $foto;
    }
    
    return $default;
}

// Function to get first name (first word) from full name
function getFirstName($fullName) {
    if (empty($fullName)) return '';
    $nameParts = explode(' ', trim($fullName));
    return $nameParts[0];
}

// Helper function to convert memory limit string to bytes
function return_bytes($val) {
    $val = trim($val);
    if (empty($val)) return 0;
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// Google Authenticator Helper Functions
function generateGoogleAuthenticatorSecret() {
    if (!class_exists('\Sonata\GoogleAuthenticator\GoogleAuthenticator')) {
        return null;
    }
    $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
    return $g->generateSecret();
}

function getGoogleAuthenticatorQRCode($secret, $email, $issuer = 'Sistem Presensi') {
    if (!class_exists('\Sonata\GoogleAuthenticator\GoogleQrUrl')) {
        return null;
    }
    try {
        // Generate QR code URL for Google Authenticator
        // Format: otpauth://totp/ISSUER:EMAIL?secret=SECRET&issuer=ISSUER
        $qrContent = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            urlencode($issuer),
            urlencode($email),
            urlencode($secret),
            urlencode($issuer)
        );
        
        // Use Google Charts API to generate QR code image
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrContent);
        
        return $qrUrl;
    } catch (Exception $e) {
        error_log("Error generating QR code: " . $e->getMessage());
        return null;
    }
}

function verifyGoogleAuthenticatorOTP($secret, $code) {
    if (!class_exists('\Sonata\GoogleAuthenticator\GoogleAuthenticator')) {
        return false;
    }
    if (empty($secret) || empty($code)) {
        return false;
    }
    $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();
    return $g->checkCode($secret, $code);
}

// Email Helper Functions
function sendPasswordResetEmail($email, $resetToken) {
    try {
        // Build reset URL - handle both localhost and production
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = dirname($scriptName);
        
        // Clean up base path - remove trailing slash and normalize
        $basePath = rtrim($basePath, '/');
        if ($basePath === '.') {
            $basePath = '';
        }
        if (!empty($basePath) && $basePath !== '/') {
            $basePath = '/' . ltrim($basePath, '/');
        }
        
        $resetUrl = $protocol . '://' . $host . $basePath . '/index.php?page=verify-otp&token=' . urlencode($resetToken);
        
        $subject = "Reset Password - Sistem Presensi";
        
        // Professional email template
        $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 20px 0; text-align: center; background-color: #ffffff;">
                <table role="presentation" style="width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">Reset Password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px; background-color: #ffffff;">
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Halo,</p>
                            <p style="margin: 0 0 20px 0; color: #333333; font-size: 16px; line-height: 1.6;">Kami menerima permintaan untuk mereset password akun Anda di Sistem Presensi Berbasis Wajah.</p>
                            <p style="margin: 0 0 30px 0; color: #333333; font-size: 16px; line-height: 1.6;">Untuk melanjutkan proses reset password, silakan verifikasi dengan kode OTP dari Google Authenticator Anda terlebih dahulu.</p>
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="' . htmlspecialchars($resetUrl) . '" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">Verifikasi OTP</a>
                            </div>
                            <p style="margin: 30px 0 10px 0; color: #666666; font-size: 14px; line-height: 1.6;">Atau salin link berikut ke browser Anda:</p>
                            <p style="margin: 0 0 30px 0; color: #667eea; font-size: 14px; word-break: break-all; line-height: 1.6;">' . htmlspecialchars($resetUrl) . '</p>
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 30px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;"><strong>Penting:</strong> Link ini akan kedaluwarsa dalam 1 jam. Jika Anda tidak meminta reset password, abaikan email ini.</p>
                            </div>
                            <p style="margin: 30px 0 0 0; color: #666666; font-size: 14px; line-height: 1.6;">Terima kasih,<br><strong>Tim Sistem Presensi</strong></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 40px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">&copy; ' . date('Y') . ' Sistem Presensi Berbasis Wajah. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
        
        $textBody = "Reset Password\n\n";
        $textBody .= "Kami menerima permintaan untuk mereset password akun Anda.\n\n";
        $textBody .= "Untuk melanjutkan, silakan verifikasi dengan kode OTP dari Google Authenticator Anda:\n";
        $textBody .= $resetUrl . "\n\n";
        $textBody .= "Link ini akan kedaluwarsa dalam 1 jam.\n\n";
        $textBody .= "Jika Anda tidak meminta reset password, abaikan email ini.\n\n";
        $textBody .= "Terima kasih,\nTim Sistem Presensi";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Sistem Presensi <noreply@presensi.local>" . "\r\n";
        $headers .= "Reply-To: noreply@presensi.local" . "\r\n";
        
        // Try to send email
        $result = @mail($email, $subject, $htmlBody, $headers);
        
        // Log email attempt
        error_log("Password reset email sent to: $email, URL: $resetUrl, Result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        // For development/testing: if mail() fails, log but don't fail completely
        // In production, you should configure SMTP properly
        if (!$result) {
            error_log("Warning: mail() function returned false for $email. Check PHP mail configuration.");
            // For development: we'll still allow the reset to proceed
            // In production, you should configure SMTP properly or use PHPMailer
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error in sendPasswordResetEmail: " . $e->getMessage());
        return false;
    }
}

// FaceNet Integration Functions
function generateFaceEmbedding($base64Image) {
    try {
        $data = [
            'action' => 'generate_embedding',
            'image' => $base64Image
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data']['embedding'];
            }
        }
        
        error_log("FaceNet embedding generation failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error generating face embedding: " . $e->getMessage());
        return null;
    }
}

function recognizeFace($base64Image, $threshold = 1.0) {
    try {
        $data = [
            'action' => 'recognize_face',
            'image' => $base64Image,
            'threshold' => $threshold
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("FaceNet recognition failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error recognizing face: " . $e->getMessage());
        return null;
    }
}

function saveFaceEmbedding($userId, $embedding) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET face_embedding = ?, face_embedding_updated = NOW() WHERE id = ?");
        $stmt->execute([json_encode($embedding), $userId]);
        return true;
    } catch (Exception $e) {
        error_log("Error saving face embedding: " . $e->getMessage());
        return false;
    }
}

function getFaceEmbeddings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT id, nim, nama, face_embedding FROM users WHERE role='pegawai' AND face_embedding IS NOT NULL");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $embeddings = [];
        foreach ($users as $user) {
            $embedding = json_decode($user['face_embedding'], true);
            if ($embedding) {
                $embeddings[$user['nim']] = $embedding;
            }
        }
        
        return $embeddings;
    } catch (Exception $e) {
        error_log("Error getting face embeddings: " . $e->getMessage());
        return [];
    }
}

function processAttendanceWithFaceNet($base64Image) {
    try {
        $data = [
            'action' => 'process_attendance',
            'image' => $base64Image,
            'threshold' => 1.0
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("FaceNet attendance processing failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error processing attendance with FaceNet: " . $e->getMessage());
        return null;
    }
}

// Enhanced FaceNet Functions
function generateEnhancedFaceEmbedding($base64Image) {
    try {
        $data = [
            'action' => 'generate_enhanced_embedding',
            'image' => $base64Image
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_enhanced_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Enhanced FaceNet embedding generation failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error generating enhanced face embedding: " . $e->getMessage());
        return null;
    }
}

// High Accuracy FaceNet Functions
function processHighAccuracyAttendance($base64Image, $userId = null) {
    try {
        $data = [
            'action' => 'process_high_accuracy_attendance',
            'image' => $base64Image
        ];
        
        if ($userId !== null) {
            $data['user_id'] = $userId;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_high_accuracy_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("High accuracy attendance processing failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error processing high accuracy attendance: " . $e->getMessage());
        return null;
    }
}

// Optimized FaceNet Functions - iPhone-like Performance
function processOptimizedAttendance($base64Image, $threshold = 0.5) {
    try {
        $data = [
            'action' => 'process_attendance_optimized',
            'image' => $base64Image,
            'threshold' => $threshold
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_optimized_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Faster timeout for optimized service
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Optimized attendance processing failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error processing optimized attendance: " . $e->getMessage());
        return null;
    }
}

function recognizeFaceOptimized($base64Image, $threshold = 0.5) {
    try {
        $data = [
            'action' => 'recognize_face_optimized',
            'image' => $base64Image,
            'threshold' => $threshold
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_optimized_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Optimized face recognition failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error in optimized face recognition: " . $e->getMessage());
        return null;
    }
}

function generateOptimizedEmbedding($base64Image) {
    try {
        $data = [
            'action' => 'generate_embedding_optimized',
            'image' => $base64Image
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_optimized_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Optimized embedding generation failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error generating optimized embedding: " . $e->getMessage());
        return null;
    }
}

function getOptimizedPerformanceStats() {
    try {
        $data = ['action' => 'get_performance_stats'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_optimized_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Failed to get optimized performance stats: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error getting optimized performance stats: " . $e->getMessage());
        return null;
    }
}

// Ultra Accurate FaceNet Functions - Maximum Accuracy with Ultra-Fast Response
function processUltraAccurateAttendance($base64Image, $validationLevel = 'normal') {
    try {
        $data = [
            'action' => 'process_attendance_ultra_accurate',
            'image' => $base64Image,
            'validation_level' => $validationLevel
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_ultra_accurate_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Ultra-fast timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Ultra accurate attendance processing failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error processing ultra accurate attendance: " . $e->getMessage());
        return null;
    }
}

function getUltraAccuratePerformanceStats() {
    try {
        $data = ['action' => 'get_performance_stats'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_ultra_accurate_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Failed to get ultra accurate performance stats: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error getting ultra accurate performance stats: " . $e->getMessage());
        return null;
    }
}

// Direct iPhone-Level Accurate FaceNet Functions - Maximum Accuracy with Direct Processing
function processIPhoneLevelAttendance($base64Image) {
    try {
        // Direct Python execution without API
        $command = "python facenet_iphone_accurate_service.py recognize_face " . escapeshellarg($base64Image);
        
        $startTime = microtime(true);
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        $executionTime = microtime(true) - $startTime;
        
        if ($returnCode === 0 && !empty($output)) {
            $result = json_decode(implode("\n", $output), true);
            if ($result && $result['success']) {
                // Add execution time to result
                $result['execution_time'] = $executionTime;
                return $result;
            }
        }
        
        error_log("Direct iPhone-level processing failed: " . implode("\n", $output));
        return null;
    } catch (Exception $e) {
        error_log("Error in direct iPhone-level processing: " . $e->getMessage());
        return null;
    }
}

function getIPhoneLevelPerformanceStats() {
    try {
        $data = ['action' => 'get_performance_stats'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_iphone_accurate_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Failed to get iPhone-level performance stats: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error getting iPhone-level performance stats: " . $e->getMessage());
        return null;
    }
}

// Ultra Detailed FaceNet Functions - iPhone Face ID Level Accuracy with Super Detailed Features
function processUltraDetailedAttendance($base64Image) {
    try {
        // Direct Python execution without API for maximum speed
        $command = "python facenet_ultra_detailed_service.py process_attendance_ultra_detailed " . escapeshellarg($base64Image);
        
        $startTime = microtime(true);
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        $executionTime = microtime(true) - $startTime;
        
        if ($returnCode === 0 && !empty($output)) {
            $result = json_decode(implode("\n", $output), true);
            if ($result && $result['success']) {
                // Add execution time to result
                $result['execution_time'] = $executionTime;
                return $result;
            }
        }
        
        error_log("Ultra detailed attendance processing failed: " . implode("\n", $output));
        return null;
    } catch (Exception $e) {
        error_log("Error processing ultra detailed attendance: " . $e->getMessage());
        return null;
    }
}

function getUltraDetailedPerformanceStats() {
    try {
        $command = "python facenet_ultra_detailed_service.py get_performance_stats";
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode === 0 && !empty($output)) {
            $result = json_decode(implode("\n", $output), true);
            if ($result) {
                return $result;
            }
        }
        
        error_log("Failed to get ultra detailed performance stats: " . implode("\n", $output));
        return null;
    } catch (Exception $e) {
        error_log("Error getting ultra detailed performance stats: " . $e->getMessage());
        return null;
    }
}

function generateHighAccuracyEmbedding($base64Image, $userId) {
    try {
        $data = [
            'action' => 'generate_high_accuracy_embedding',
            'image' => $base64Image,
            'user_id' => $userId
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_high_accuracy_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("High accuracy embedding generation failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error generating high accuracy embedding: " . $e->getMessage());
        return null;
    }
}

function getHighAccuracyPerformanceStats() {
    try {
        $data = ['action' => 'get_performance_stats'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_high_accuracy_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Failed to get high accuracy performance stats: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error getting high accuracy performance stats: " . $e->getMessage());
        return null;
    }
}

function recognizeEnhancedFace($base64Image, $threshold = 1.0) {
    try {
        $data = [
            'action' => 'recognize_enhanced_face',
            'image' => $base64Image,
            'threshold' => $threshold
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_enhanced_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Enhanced FaceNet recognition failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error recognizing enhanced face: " . $e->getMessage());
        return null;
    }
}

function saveEnhancedFaceEmbedding($userId, $enhancedEmbedding) {
    global $pdo;
    try {
        $baseEmbedding = json_encode($enhancedEmbedding['base_embedding'] ?? []);
        $advancedFeatures = json_encode($enhancedEmbedding['advanced_features'] ?? []);
        $facialGeometry = json_encode($enhancedEmbedding['advanced_features']['geometry'] ?? []);
        $featureVector = json_encode($enhancedEmbedding['advanced_features']['feature_vector'] ?? []);
        
        $stmt = $pdo->prepare("
            UPDATE users SET 
                face_embedding = ?, 
                advanced_features = ?,
                facial_geometry = ?,
                feature_vector = ?,
                face_embedding_updated = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$baseEmbedding, $advancedFeatures, $facialGeometry, $featureVector, $userId]);
        return true;
    } catch (Exception $e) {
        error_log("Error saving enhanced face embedding: " . $e->getMessage());
        return false;
    }
}

function processEnhancedAttendance($base64Image) {
    try {
        $data = [
            'action' => 'process_enhanced_attendance',
            'image' => $base64Image,
            'threshold' => 1.0
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/facenet_enhanced_api.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1); // ULTRA-FAST: 1 second timeout
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return $result['data'];
            }
        }
        
        error_log("Enhanced FaceNet attendance processing failed: " . $response);
        return null;
    } catch (Exception $e) {
        error_log("Error processing enhanced attendance: " . $e->getMessage());
        return null;
    }
}

// KPI Calculation Functions
function calculateKPIForEmployee(PDO $pdo, $userId, $periodStart = null, $periodEnd = null) {
    try {
        // Get KPI settings
        $latePenaltyPerMinute = (float)getSetting($pdo, 'kpi_late_penalty_per_minute', '1');
        $izinSakitScore = (float)getSetting($pdo, 'kpi_izin_sakit_score', '85');
        $alphaScore = (float)getSetting($pdo, 'kpi_alpha_score', '0');
        $overtimeBonus = (float)getSetting($pdo, 'kpi_overtime_bonus', '5');
        $maxOntimeHour = (int)getSetting($pdo, 'max_ontime_hour', '8');
        
        // Get employee data
        $stmt = $pdo->prepare("SELECT nama, created_at, nim, startup, foto_base64 FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $employee = $stmt->fetch();
        if (!$employee) return null;
        
        // Get employee registration date
        $employeeRegDate = $employee['created_at'];
        
        // Determine KPI start: use per-employee start setting if available, else registration date
        if (!$periodStart) {
            // Try settings override
            try{
                $k = 'work_start_date_user_'.$userId;
                $st = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key=:k LIMIT 1");
                $st->execute([':k'=>$k]);
                $val = $st->fetchColumn();
                if($val){ $periodStart = $val; } else { $periodStart = $employeeRegDate; }
            }catch(Exception $e){ $periodStart = $employeeRegDate; }
        }
        if (!$periodEnd) {
            $periodEnd = date('Y-m-d');
        }
        
        // Debug logging for period
        error_log("KPI Debug - User $userId: Period start: $periodStart, Period end: $periodEnd");
        
        // Get employee registration date only
        $employeeRegDateOnly = date('Y-m-d', strtotime($employeeRegDate));
        
        // Get attendance records for the period (WFO, WFA, Overtime only)
        // Store late records with their minutes for per-occurrence calculation
        // Use jam_masuk (time format) instead of jam_masuk_iso for late_minutes calculation
        // Use max_ontime_hour from settings instead of hardcoded 08:00
        $st = $pdo->prepare("
            SELECT 
                DATE(jam_masuk_iso) as attendance_date,
                jam_masuk_iso,
                jam_masuk,
                status,
                ket,
                CASE 
                    WHEN status = 'terlambat' AND jam_masuk IS NOT NULL THEN 
                        GREATEST(0, 
                            FLOOR(
                                TIMESTAMPDIFF(MINUTE, 
                                    CONCAT('2000-01-01 ', LPAD(:max_ontime_hour1, 2, '0'), ':00:00'),
                                    CONCAT('2000-01-01 ', 
                                        CASE 
                                            WHEN LENGTH(jam_masuk) = 5 THEN CONCAT(jam_masuk, ':00')
                                            ELSE jam_masuk
                                        END
                                    )
                                )
                            )
                        )
                    WHEN status = 'terlambat' AND jam_masuk IS NULL THEN 
                        GREATEST(0, TIMESTAMPDIFF(MINUTE, 
                            CONCAT(DATE(jam_masuk_iso), ' ', LPAD(:max_ontime_hour2, 2, '0'), ':00:00'), 
                            jam_masuk_iso
                        ))
                    ELSE 0 
                END as late_minutes
            FROM attendance 
            WHERE user_id = :user_id 
            AND jam_masuk_iso IS NOT NULL 
            AND DATE(jam_masuk_iso) BETWEEN DATE(:period_start) AND DATE(:period_end)
            AND ket IN ('wfo', 'wfa', 'overtime')
            ORDER BY attendance_date
        ");
        $st->execute([
            'user_id' => $userId, 
            'period_start' => $periodStart, 
            'period_end' => $periodEnd,
            'max_ontime_hour1' => $maxOntimeHour,
            'max_ontime_hour2' => $maxOntimeHour
        ]);
        $attendanceRecords = $st->fetchAll();
        
        // Debug: log attendance records to see late_minutes values
        error_log("KPI Debug - User $userId: Found " . count($attendanceRecords) . " attendance records");
        foreach ($attendanceRecords as $idx => $rec) {
            if ($rec['status'] === 'terlambat') {
                error_log("KPI Debug - User $userId: Record $idx - Date: {$rec['attendance_date']}, Status: {$rec['status']}, jam_masuk: {$rec['jam_masuk']}, jam_masuk_iso: {$rec['jam_masuk_iso']}, late_minutes: {$rec['late_minutes']}");
            }
        }
        
        // Get izin/sakit records from attendance_notes table
        $stmt = $pdo->prepare("
            SELECT date as izin_date, type as status
            FROM attendance_notes 
            WHERE user_id = :user_id 
            AND type IN ('izin', 'sakit')
            AND date BETWEEN :period_start AND :period_end
            ORDER BY izin_date
        ");
        $stmt->execute([
            'user_id' => $userId, 
            'period_start' => $periodStart, 
            'period_end' => $periodEnd
        ]);
        $izinNotesRecords = $stmt->fetchAll();
        
        // Debug logging for izin/sakit records
        error_log("KPI Debug - User $userId: Found " . count($izinNotesRecords) . " izin/sakit records in period $periodStart to $periodEnd");
        error_log("KPI Debug - Employee registration date: $employeeRegDateOnly");
        
        // Get overtime records (attendance marked as 'overtime')
        $stmt = $pdo->prepare("
            SELECT DATE(jam_masuk_iso) as overtime_date, status, jam_masuk_iso, jam_masuk
            FROM attendance 
            WHERE user_id = :user_id 
            AND DATE(jam_masuk_iso) BETWEEN :period_start AND :period_end
            AND ket = 'overtime'
            ORDER BY jam_masuk_iso ASC
        ");
        $stmt->execute([
            'user_id' => $userId, 
            'period_start' => $periodStart, 
            'period_end' => $periodEnd
        ]);
        $overtimeRecords = $stmt->fetchAll();
        
        // Get daily reports for the period
        $dailyReportsStmt = $pdo->prepare("
            SELECT report_date 
            FROM daily_reports 
            WHERE user_id = :user_id 
            AND report_date BETWEEN :period_start AND :period_end
        ");
        $dailyReportsStmt->execute([
            'user_id' => $userId,
            'period_start' => $periodStart,
            'period_end' => $periodEnd
        ]);
        $dailyReportsRecords = $dailyReportsStmt->fetchAll();
        
        // Create maps for quick lookup
        $attendanceMap = [];
        foreach ($attendanceRecords as $record) {
            $attendanceMap[$record['attendance_date']] = $record;
        }
        
        $dailyReportsMap = [];
        foreach ($dailyReportsRecords as $record) {
            $dailyReportsMap[$record['report_date']] = true;
        }
        
        $izinDates = [];
        foreach ($izinNotesRecords as $record) {
            // Only add if date is after or on registration date AND within the period
            if ($record['izin_date'] >= $employeeRegDateOnly && $record['izin_date'] >= $periodStart && $record['izin_date'] <= $periodEnd) {
                $izinDates[$record['izin_date']] = $record['status'];
            }
        }
        
        error_log("KPI Debug - User $userId: Total izin/sakit dates in map: " . count($izinDates));
        
        $overtimeDates = [];
        foreach ($overtimeRecords as $record) {
            $overtimeDates[$record['overtime_date']] = $record;
        }
        
        // Generate working days for this specific employee in the period
        $workingDays = getEmployeeWorkingDaysInPeriod($pdo, $userId, $periodStart, $periodEnd);
        
        // Get current date for comparison
        $currentDate = date('Y-m-d');
        
        $ontimeCount = 0;
        $lateCount = 0;
        $wfoCount = 0; // NEW: Count WFO attendance
        $wfaCount = 0; // NEW: Count WFA attendance
        $totalLateMinutes = 0; // Keep for backward compatibility/reporting
        $lateRecords = []; // Store late records with minutes for per-occurrence calculation
        $izinSakitCount = 0;
        $alphaCount = 0;
        $overtimeCount = 0;
        $actualWorkingDays = 0; // Count actual working days for this employee (only past dates)
        $totalWorkingDaysInPeriod = 0; // Count all working days in period for this employee
        $missingDailyReportsCount = 0; // Count days with attendance but no daily report
        $daysWithoutReport = []; // Store dates that need daily report penalty
        
        // Process each working day
        foreach ($workingDays as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Skip dates before employee registration
            if ($dateStr < $employeeRegDateOnly) {
                continue;
            }
            
            // Count this as a working day for this employee (regardless of whether it's past or future)
            $totalWorkingDaysInPeriod++;
            
            // Only count as actual working day if the date has already passed
            if ($dateStr <= $currentDate) {
                $actualWorkingDays++;
            }
            
            // Check if there's an attendance record for this date
            // Use attendanceMap for faster lookup instead of looping
            $attendanceRecord = isset($attendanceMap[$dateStr]) ? $attendanceMap[$dateStr] : null;
            
            // Only process dates that have already passed for KPI calculation
            if ($dateStr <= $currentDate) {
                // Check if it's izin/sakit first (from attendance_notes table)
                if (isset($izinDates[$dateStr])) {
                    $izinSakitCount++;
                    error_log("KPI Debug - User $userId: Found izin/sakit on $dateStr, count now: $izinSakitCount");
                } else if ($attendanceRecord) {
                    // Check if daily report exists for this date
                    $hasDailyReport = isset($dailyReportsMap[$dateStr]);
                    
                    // If attendance exists but no daily report, mark for penalty
                    if (!$hasDailyReport && ($attendanceRecord['ket'] === 'wfo' || $attendanceRecord['ket'] === 'wfa')) {
                        $missingDailyReportsCount++;
                        $daysWithoutReport[] = $dateStr;
                        error_log("KPI Debug - User $userId: Missing daily report on $dateStr");
                    }
                    
                    // Check attendance status (only WFO, WFA, Overtime)
                    if ($attendanceRecord['status'] === 'ontime') {
                        $ontimeCount++;
                        // Count WFO and WFA separately
                        if ($attendanceRecord['ket'] === 'wfo') {
                            $wfoCount++;
                        } else if ($attendanceRecord['ket'] === 'wfa') {
                            $wfaCount++;
                        }
                        error_log("KPI Debug - User $userId: Found ontime on $dateStr");
                    } else {
                        $lateCount++;
                        // Count WFO and WFA even if late
                        if ($attendanceRecord['ket'] === 'wfo') {
                            $wfoCount++;
                        } else if ($attendanceRecord['ket'] === 'wfa') {
                            $wfaCount++;
                        }
                        $lateMinutes = (int)$attendanceRecord['late_minutes'];
                        $totalLateMinutes += $lateMinutes;
                        // Store late record with minutes for per-occurrence calculation
                        $lateRecords[] = $lateMinutes;
                        error_log("KPI Debug - User $userId: Found late on $dateStr, late_minutes from DB: {$attendanceRecord['late_minutes']}, jam_masuk: {$attendanceRecord['jam_masuk']}, jam_masuk_iso: {$attendanceRecord['jam_masuk_iso']}, status: {$attendanceRecord['status']}");
                    }
                } else {
                    // No attendance and no izin/sakit = alpha (only for past dates)
                    // If this date is a manual holiday, do not penalize as alpha
                    if (!isManualHoliday($pdo, $dateStr)) {
                        $alphaCount++;
                    }
                }
            }
        }
        
        // Count overtime days (including weekends and holidays)
        foreach ($overtimeDates as $overtimeDate => $overtimeRecord) {
            $overtimeCount++;
        }
        
        // Count izin/sakit directly from the records (more reliable)
        $currentDate = date('Y-m-d');
        $directIzinSakitCount = 0;
        foreach ($izinNotesRecords as $record) {
            if ($record['izin_date'] >= $employeeRegDateOnly && 
                $record['izin_date'] >= $periodStart && 
                $record['izin_date'] <= $periodEnd &&
                $record['izin_date'] <= $currentDate) {
                $directIzinSakitCount++;
            }
        }
        
        // Use the direct count if it's different from the loop count
        if ($directIzinSakitCount != $izinSakitCount) {
            error_log("KPI Debug - User $userId: Correcting izin/sakit count from $izinSakitCount to $directIzinSakitCount");
            $izinSakitCount = $directIzinSakitCount;
        }
        
        // Debug logging for final counts
        error_log("KPI Debug - User $userId: Final counts - Ontime: $ontimeCount, Late: $lateCount, Izin/Sakit: $izinSakitCount, Alpha: $alphaCount, Overtime: $overtimeCount");
        error_log("KPI Debug - User $userId: actualWorkingDays from loop: $actualWorkingDays");
        error_log("KPI Debug - User $userId: lateRecords count: " . count($lateRecords) . ", lateRecords: " . print_r($lateRecords, true));
        
        // Calculate actual working days based on days with actual data
        // This should be the sum of all days with attendance records (ontime, late, alpha, izin/sakit)
        // NOT the total working days in period, because we only calculate KPI for days with data
        $daysWithData = (int)$ontimeCount + (int)$lateCount + (int)$izinSakitCount + (int)$alphaCount;
        
        error_log("KPI Debug - User $userId: daysWithData calculation: $ontimeCount + $lateCount + $izinSakitCount + $alphaCount = $daysWithData");
        
        // IMPORTANT: Always use daysWithData as divisor if it's greater than 0
        // This ensures KPI is calculated correctly: total score / days with data
        // Only fallback to actualWorkingDays if daysWithData is 0 (shouldn't happen in normal cases)
        if ($daysWithData > 0) {
            $actualDaysForKPI = $daysWithData;
            error_log("KPI Debug - User $userId: Using daysWithData ($daysWithData) as divisor");
        } else {
            // Fallback: use actualWorkingDays only if no data at all
            $actualDaysForKPI = $actualWorkingDays > 0 ? $actualWorkingDays : 1; // Prevent division by zero
            error_log("KPI Debug - User $userId: WARNING - daysWithData is 0, using actualWorkingDays ($actualDaysForKPI) as fallback");
        }
        
        error_log("KPI Debug - User $userId: Final divisor (actualDaysForKPI): $actualDaysForKPI");
        
        // Calculate KPI score using new per-occurrence method
        // Formula: 
        // - On-time: 100% each
        // - Late: 100% - (minutes late) for each occurrence
        // - Alpha: 0% each
        // - Izin/Sakit: use setting score (default 85%)
        // - Overtime: bonus (default 5%)
        // Total = sum of all scores / days with actual data
        $kpiScore = 0;
        
        // On-time: 100% each
        $ontimeScore = $ontimeCount * 100;
        $kpiScore += $ontimeScore;
        error_log("KPI Debug - User $userId: Ontime score: $ontimeScore (count: $ontimeCount)");
        
        // Late: calculate per occurrence (100% - minutes late)
        $lateTotalScore = 0;
        foreach ($lateRecords as $lateMinutes) {
            // Formula: 100% - (minutes late)
            // Example: terlambat 10 menit = 100 - 10 = 90%
            // Example: terlambat 9 menit = 100 - 9 = 91%
            $lateScore = 100 - $lateMinutes; // 100% - minutes late
            $lateScore = max(0, $lateScore); // Ensure not negative (if terlambat > 100 menit, score = 0)
            $lateTotalScore += $lateScore;
            error_log("KPI Debug - User $userId: Late occurrence: $lateMinutes minutes late = $lateScore score (100 - $lateMinutes = $lateScore)");
        }
        $kpiScore += $lateTotalScore;
        error_log("KPI Debug - User $userId: Late total score: $lateTotalScore (count: $lateCount, records: " . print_r($lateRecords, true) . ")");
        
        // Alpha: 0% each (no need to add, already 0)
        // $kpiScore += ($alphaCount * 0); // Not needed
        error_log("KPI Debug - User $userId: Alpha count: $alphaCount (score: 0)");
        
        // Izin/Sakit: use setting score (default 85%)
        $izinSakitScoreTotal = $izinSakitCount * $izinSakitScore;
        $kpiScore += $izinSakitScoreTotal;
        error_log("KPI Debug - User $userId: Izin/Sakit score: $izinSakitScoreTotal (count: $izinSakitCount, per occurrence: $izinSakitScore)");
        
        // Overtime: bonus (default 5% per occurrence)
        $overtimeScoreTotal = $overtimeCount * $overtimeBonus;
        $kpiScore += $overtimeScoreTotal;
        error_log("KPI Debug - User $userId: Overtime score: $overtimeScoreTotal (count: $overtimeCount, per occurrence: $overtimeBonus)");
        
        // Apply daily report penalty: reduce 50% per day without report
        // This penalty is applied per day, not from total score
        $dailyReportPenalty = 0;
        if (isset($daysWithoutReport) && is_array($daysWithoutReport)) {
            foreach ($daysWithoutReport as $dateWithoutReport) {
                // Find the score for that day
                $dayScore = 0;
                if (isset($attendanceMap[$dateWithoutReport])) {
                    $dayRecord = $attendanceMap[$dateWithoutReport];
                    if ($dayRecord['status'] === 'ontime') {
                        $dayScore = 100;
                    } else {
                        // Late: 100 - minutes late
                        $lateMinutes = (int)$dayRecord['late_minutes'];
                        $dayScore = max(0, 100 - $lateMinutes);
                    }
                }
                // Reduce 50% of that day's score
                $penaltyForDay = $dayScore * 0.5;
                $dailyReportPenalty += $penaltyForDay;
                error_log("KPI Debug - User $userId: Daily report penalty for $dateWithoutReport: $penaltyForDay (day score: $dayScore)");
            }
        }
        $kpiScore -= $dailyReportPenalty;
        error_log("KPI Debug - User $userId: Total daily report penalty: $dailyReportPenalty, score after penalty: $kpiScore");

        // Calculate average based on days with actual data
        error_log("KPI Debug - User $userId: Total score before division: $kpiScore, Divided by: $actualDaysForKPI");
        $kpiScore = $kpiScore / $actualDaysForKPI;
        error_log("KPI Debug - User $userId: KPI score after division: $kpiScore");
        
        // Ensure score is between 0 and 100
        $kpiScore = max(0, min(100, $kpiScore));
        error_log("KPI Debug - User $userId: Final KPI score: $kpiScore");
        
        // Determine KPI status
        $status = 'Very Poor';
        if ($kpiScore >= 90) $status = 'Excellent';
        elseif ($kpiScore >= 80) $status = 'Good';
        elseif ($kpiScore >= 70) $status = 'Fair';
        elseif ($kpiScore >= 60) $status = 'Poor';
        
        return [
            'user_id' => $userId,
            'nama' => $employee['nama'],
            'nim' => $employee['nim'] ?? '-',
            'startup' => $employee['startup'] ?? '-',
            'foto_base64' => $employee['foto_base64'] ?? '',
            'total_working_days' => $totalWorkingDaysInPeriod, // Total working days in period
            'actual_working_days' => $actualWorkingDays, // Days that have passed for KPI calculation
            'ontime_count' => $ontimeCount,
            'wfo_count' => $wfoCount, // NEW: Add WFO count
            'wfa_count' => $wfaCount, // NEW: Add WFA count
            'late_count' => $lateCount,
            'izin_sakit_count' => $izinSakitCount,
            'alpha_count' => $alphaCount,
            'overtime_count' => $overtimeCount,
            'missing_daily_reports_count' => $missingDailyReportsCount,
            'total_late_minutes' => $totalLateMinutes,
            'kpi_score' => round($kpiScore, 2),
            'status' => $status,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'employee_registration_date' => $employeeRegDate
        ];
        
    } catch (Exception $e) {
        error_log("KPI calculation error: " . $e->getMessage());
        return null;
    }
}

// Function to get Indonesian national holidays for a given year
function getIndonesianNationalHolidays($year) {
    $holidays = [];
    
    // Fixed holidays (same date every year)
    $fixedHolidays = [
        '01-01' => 'Tahun Baru',
        '02-14' => 'Valentine Day',
        '03-22' => 'Hari Raya Nyepi',
        '04-18' => 'Wafat Isa Almasih',
        '05-01' => 'Hari Buruh Internasional',
        '05-09' => 'Kenaikan Isa Almasih',
        '05-20' => 'Hari Kebangkitan Nasional',
        '06-01' => 'Hari Lahir Pancasila',
        '06-17' => 'Hari Raya Idul Adha',
        '08-17' => 'Hari Kemerdekaan RI',
        '09-16' => 'Maulid Nabi Muhammad SAW',
        '10-02' => 'Hari Batik Nasional',
        '11-10' => 'Hari Pahlawan',
        '12-25' => 'Hari Raya Natal'
    ];
    
    // Islamic holidays (calculated based on Islamic calendar - simplified)
    // Note: These dates are approximate and should be updated yearly
    $islamicHolidays = [
        // Idul Fitri (2 days) - dates vary each year
        // Idul Adha - dates vary each year
        // Islamic New Year - dates vary each year
        // Maulid Nabi - dates vary each year
    ];
    
    // Add fixed holidays
    foreach ($fixedHolidays as $date => $name) {
        $holidays[] = [
            'date' => $year . '-' . $date,
            'name' => $name,
            'type' => 'fixed'
        ];
    }
    
    // Add Islamic holidays for specific years (2024-2025)
    if ($year == 2024) {
        $islamicHolidays2024 = [
            '2024-04-10' => 'Hari Raya Idul Fitri 1445 H',
            '2024-04-11' => 'Hari Raya Idul Fitri 1445 H (Hari Kedua)',
            '2024-06-16' => 'Hari Raya Idul Adha 1445 H',
            '2024-07-07' => 'Tahun Baru Islam 1446 H',
            '2024-09-15' => 'Maulid Nabi Muhammad SAW 1446 H'
        ];
        foreach ($islamicHolidays2024 as $date => $name) {
            $holidays[] = [
                'date' => $date,
                'name' => $name,
                'type' => 'islamic'
            ];
        }
    } elseif ($year == 2025) {
        $islamicHolidays2025 = [
            '2025-03-30' => 'Hari Raya Idul Fitri 1446 H',
            '2025-03-31' => 'Hari Raya Idul Fitri 1446 H (Hari Kedua)',
            '2025-06-06' => 'Hari Raya Idul Adha 1446 H',
            '2025-06-26' => 'Tahun Baru Islam 1447 H',
            '2025-09-05' => 'Maulid Nabi Muhammad SAW 1447 H'
        ];
        foreach ($islamicHolidays2025 as $date => $name) {
            $holidays[] = [
                'date' => $date,
                'name' => $name,
                'type' => 'islamic'
            ];
        }
    }
    
    return $holidays;
}

// Function to check if a date is a national holiday
function isNationalHoliday($date) {
    $year = date('Y', strtotime($date));
    $holidays = getIndonesianNationalHolidays($year);
    
    foreach ($holidays as $holiday) {
        if ($holiday['date'] === $date) {
            return true;
        }
    }
    
    return false;
}

// Manual holiday helpers
function isManualHoliday(PDO $pdo, $date){
    try{
        $stmt = $pdo->prepare("SELECT 1 FROM manual_holidays WHERE date = :d LIMIT 1");
        $stmt->execute([':d'=>$date]);
        return (bool)$stmt->fetchColumn();
    }catch(PDOException $e){
        error_log('isManualHoliday error: '.$e->getMessage());
        return false;
    }
}

function getManualHolidaysInRange(PDO $pdo, $startDate, $endDate){
    try{
        $stmt = $pdo->prepare("SELECT * FROM manual_holidays WHERE date BETWEEN :s AND :e ORDER BY date");
        $stmt->execute([':s'=>$startDate, ':e'=>$endDate]);
        return $stmt->fetchAll();
    }catch(PDOException $e){
        error_log('getManualHolidaysInRange error: '.$e->getMessage());
        return [];
    }
}

// Function to get employee's work schedule
function getEmployeeWorkSchedule(PDO $pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM employee_work_schedule WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        $schedules = $stmt->fetchAll();
        
        $scheduleMap = [];
        foreach ($schedules as $schedule) {
            $scheduleMap[$schedule['day_of_week']] = [
                'is_working_day' => (bool)$schedule['is_working_day'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time']
            ];
        }
        
        return $scheduleMap;
    } catch (PDOException $e) {
        error_log("Error getting employee work schedule: " . $e->getMessage());
        return [];
    }
}

// Function to check if a specific date is a working day for an employee
function isEmployeeWorkingDay(PDO $pdo, $userId, $date) {
    $dateObj = new DateTime($date);
    $dayOfWeek = strtolower($dateObj->format('l')); // monday, tuesday, etc.
    
    $schedule = getEmployeeWorkSchedule($pdo, $userId);
    
    // If no specific schedule found, use default (Monday-Friday)
    if (empty($schedule)) {
        $dayNumber = $dateObj->format('N');
        return $dayNumber < 6 && !isNationalHoliday($date) && !isManualHoliday($pdo, $date);
    }
    
    // Check if employee works on this day
    if (isset($schedule[$dayOfWeek])) {
        return $schedule[$dayOfWeek]['is_working_day'] && !isNationalHoliday($date) && !isManualHoliday($pdo, $date);
    }
    
    return false;
}

// Function to get working days for a specific employee in a period
function getEmployeeWorkingDaysInPeriod(PDO $pdo, $userId, $startDate, $endDate) {
    $workingDays = [];
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    while ($start <= $end) {
        $dateStr = $start->format('Y-m-d');
        
        if (isEmployeeWorkingDay($pdo, $userId, $dateStr)) {
            $workingDays[] = clone $start;
        }
        
        $start->add(new DateInterval('P1D'));
    }
    
    return $workingDays;
}

function getWorkingDaysInPeriod($startDate, $endDate) {
    $workingDays = [];
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    
    while ($start <= $end) {
        $dateStr = $start->format('Y-m-d');
        $dayOfWeek = $start->format('N');
        
        // Skip weekends (Saturday = 6, Sunday = 0)
        if ($dayOfWeek < 6) {
            // Check if it's not a national or manual holiday
            if (!isNationalHoliday($dateStr) && !(isset($GLOBALS['pdo']) ? isManualHoliday($GLOBALS['pdo'], $dateStr) : false)) {
                $workingDays[] = clone $start;
            }
        }
        $start->add(new DateInterval('P1D'));
    }
    
    return $workingDays;
}

function getWorkingDaysInMonth($year, $month) {
    $workingDays = 0;
    $start = new DateTime("$year-$month-01");
    $end = new DateTime("$year-$month-" . $start->format('t')); // Last day of month
    
    while ($start <= $end) {
        $dateStr = $start->format('Y-m-d');
        $dayOfWeek = $start->format('N');
        
        // Skip weekends (Saturday = 6, Sunday = 0)
        if ($dayOfWeek < 6) {
            // Check if it's not a national or manual holiday
            if (!isNationalHoliday($dateStr) && !(isset($GLOBALS['pdo']) ? isManualHoliday($GLOBALS['pdo'], $dateStr) : false)) {
                $workingDays++;
            }
        }
        $start->add(new DateInterval('P1D'));
    }
    
    return $workingDays;
}

function getWorkingDaysInMonthUpToDate($year, $month, $day) {
    $workingDays = 0;
    $start = new DateTime("$year-$month-01");
    $end = new DateTime("$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT));
    
    // Subtract 1 day from end to exclude today (don't count today for alpha calculation)
    $end->sub(new DateInterval('P1D'));
    
    while ($start <= $end) {
        $dateStr = $start->format('Y-m-d');
        $dayOfWeek = $start->format('N');
        
        // Skip weekends (Saturday = 6, Sunday = 0)
        if ($dayOfWeek < 6) {
            // Check if it's not a national or manual holiday
            if (!isNationalHoliday($dateStr) && !(isset($GLOBALS['pdo']) ? isManualHoliday($GLOBALS['pdo'], $dateStr) : false)) {
                $workingDays++;
            }
        }
        $start->add(new DateInterval('P1D'));
    }
    
    return $workingDays;
}

function getEarliestEmployeeRegistrationDate(PDO $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT MIN(created_at) as earliest_date FROM users WHERE role = 'pegawai'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['earliest_date'] : date('Y-01-01');
    } catch (PDOException $e) {
        error_log("Error getting earliest employee registration date: " . $e->getMessage());
        return date('Y-01-01');
    }
}

function getEmployeeRegistrationDate(PDO $pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = :user_id AND role = 'pegawai'");
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ? $result['created_at'] : null;
    } catch (PDOException $e) {
        error_log("Error getting employee registration date: " . $e->getMessage());
        return null;
    }
}

function getAllKPIData(PDO $pdo, $customPeriodStart = null, $customPeriodEnd = null) {
    try {
        $periodStart = $customPeriodStart ?? getEarliestEmployeeRegistrationDate($pdo);
        $periodEnd = $customPeriodEnd ?? date('Y-m-d'); // Use current date instead of period end
        
        // Get all employees
        $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE role = 'pegawai' ORDER BY nama");
        $stmt->execute();
        $employees = $stmt->fetchAll();
        
        // If no employees, return empty data
        if (empty($employees)) {
            return [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'kpi_data' => []
            ];
        }
        
        $kpiData = [];
        foreach ($employees as $employee) {
            $kpi = calculateKPIForEmployee($pdo, $employee['id'], $periodStart, $periodEnd);
            if ($kpi) {
                $kpiData[] = $kpi;
            }
        }
        
        // Sort by KPI score descending
        usort($kpiData, function($a, $b) {
            return $b['kpi_score'] <=> $a['kpi_score'];
        });
        
        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'kpi_data' => $kpiData
        ];
        
    } catch (Exception $e) {
        error_log("Get all KPI data error: " . $e->getMessage());
        return null;
    }
}
