<?php
/**
 * ARSITEKTUR VIDEO PLATFORM - BAGIAN 5: DEPLOYMENT & DIAGRAM
 */

// ============================================================================
// BAGIAN 18: DEPLOYMENT GUIDE UNTUK SHARED HOSTING
// ============================================================================

/**
 * LANGKAH DEPLOYMENT KE SHARED HOSTING (cPanel)
 * 
 * 1. PERSIAPAN LOKAL
 *    - composer install --optimize-autoloader --no-dev
 *    - php artisan config:cache
 *    - php artisan route:cache
 *    - php artisan view:cache
 * 
 * 2. UPLOAD KE SHARED HOSTING
 *    - Upload semua file ke folder di luar public_html (misal: /home/user/video-platform/)
 *    - Pindahkan isi folder public/ ke public_html/
 *    - Edit public_html/index.php:
 *      
 *      // Ubah path ini
 *      require __DIR__.'/../video-platform/vendor/autoload.php';
 *      $app = require_once __DIR__.'/../video-platform/bootstrap/app.php';
 * 
 * 3. KONFIGURASI .HTACCESS (public_html/.htaccess)
 *    
 *    <IfModule mod_rewrite.c>
 *        RewriteEngine On
 *        RewriteRule ^(.*)$ public/$1 [L]
 *    </IfModule>
 * 
 * 4. CREATE DATABASE
 *    - Buat database di cPanel MySQL
 *    - Buat user database
 *    - Update .env dengan kredensial database
 * 
 * 5. JALANKAN MIGRASI
 *    - Akses SSH atau gunakan File Manager -> Terminal
 *    - cd /home/user/video-platform
 *    - php artisan migrate --force
 *    - php artisan db:seed --force
 * 
 * 6. SETUP CRON (cPanel -> Cron Jobs)
 *    * * * * * cd /home/user/video-platform && php artisan schedule:run >> /dev/null 2>&1
 * 
 * 7. SETUP STORAGE LINK
 *    php artisan storage:link
 *    
 *    Jika tidak bisa, buat manual:
 *    ln -s /home/user/video-platform/storage/app/public /home/user/public_html/storage
 * 
 * 8. PERMISSIONS
 *    chmod -R 755 storage
 *    chmod -R 755 bootstrap/cache
 */

// ============================================================================
// BAGIAN 19: STRUKTUR FOLDER FINAL (SHARED HOSTING)
// ============================================================================

/**
 * /home/username/
 * ├── video-platform/                  <- Laravel App (DI LUAR public_html!)
 * │   ├── app/
 * │   ├── bootstrap/
 * │   ├── config/
 * │   ├── database/
 * │   ├── resources/
 * │   ├── routes/
 * │   ├── storage/
 * │   │   └── app/
 * │   │       ├── private/
 * │   │       │   └── videos/          <- VIDEO AMAN DI SINI
 * │   │       └── public/
 * │   │           └── thumbnails/
 * │   ├── vendor/
 * │   ├── .env
 * │   └── artisan
 * │
 * └── public_html/                     <- Document Root
 *     ├── index.php                    <- Entry point (dimodifikasi)
 *     ├── .htaccess
 *     ├── storage -> ../video-platform/storage/app/public  <- Symlink
 *     ├── css/
 *     ├── js/
 *     └── images/
 */

// ============================================================================
// BAGIAN 20: DIAGRAM ARSITEKTUR
// ============================================================================

/**
 * FLOW DIAGRAM - STREAMING VIDEO DENGAN IKLAN
 * ============================================
 * 
 *  ┌─────────┐     ┌──────────────┐     ┌─────────────────┐
 *  │  User   │────▶│ GET /v/slug  │────▶│ VideoController │
 *  │ Browser │     │              │     │ show()          │
 *  └─────────┘     └──────────────┘     └────────┬────────┘
 *                                                 │
 *                          ┌──────────────────────┴──────────────────────┐
 *                          │                                             │
 *                          ▼                                             ▼
 *                   ┌──────────────┐                             ┌──────────────┐
 *                   │ Member/Owner │                             │  Non-Member  │
 *                   │  skipAds=T   │                             │  skipAds=F   │
 *                   └──────┬───────┘                             └──────┬───────┘
 *                          │                                            │
 *                          ▼                                            ▼
 *                   ┌──────────────┐                             ┌──────────────┐
 *                   │ Direct Play  │                             │ Show Ad 5s   │
 *                   └──────┬───────┘                             └──────┬───────┘
 *                          │                                            │
 *                          └────────────────┬───────────────────────────┘
 *                                           │
 *                                           ▼
 *                                   ┌───────────────┐
 *                                   │ POST /token   │──────Generate Token
 *                                   │ (AJAX)        │      (30 min expire)
 *                                   └───────┬───────┘
 *                                           │
 *                                           ▼
 *                                   ┌───────────────┐
 *                                   │ GET /stream/  │
 *                                   │ {token}       │
 *                                   └───────┬───────┘
 *                                           │
 *                          ┌────────────────┴────────────────┐
 *                          │                                 │
 *                          ▼                                 ▼
 *                   ┌──────────────┐                 ┌──────────────┐
 *                   │ Validate     │  FAIL           │ PASS         │
 *                   │ Token+IP+    │─────────────────│ Record View  │
 *                   │ Session      │                 └──────┬───────┘
 *                   └──────────────┘                        │
 *                                                           ▼
 *                                                   ┌───────────────┐
 *                                                   │ PHP Stream    │
 *                                                   │ (Range Req)   │
 *                                                   └───────┬───────┘
 *                                                           │
 *                                                           ▼
 *                                                   ┌───────────────┐
 *                                                   │ storage/app/  │
 *                                                   │ private/      │
 *                                                   │ videos/       │
 *                                                   └───────────────┘
 * 
 * 
 * FLOW DIAGRAM - EARNING CALCULATION (CRON)
 * ==========================================
 * 
 *  ┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
 *  │ cPanel Cron     │────▶│ php artisan      │────▶│ EarningService  │
 *  │ Every 1:00 AM   │     │ earnings:calc    │     │ calculate()     │
 *  └─────────────────┘     └──────────────────┘     └────────┬────────┘
 *                                                             │
 *                                                             ▼
 *                                                   ┌─────────────────┐
 *                                                   │ Query video_    │
 *                                                   │ views WHERE     │
 *                                                   │ is_counted=0    │
 *                                                   │ is_member=0     │
 *                                                   └────────┬────────┘
 *                                                             │
 *                                                             ▼
 *                                                   ┌─────────────────┐
 *                                                   │ Calculate:      │
 *                                                   │ (views/1000)    │
 *                                                   │ * CPM_RATE      │
 *                                                   └────────┬────────┘
 *                                                             │
 *                                          ┌──────────────────┴──────────────────┐
 *                                          │                                     │
 *                                          ▼                                     ▼
 *                                   ┌──────────────┐                     ┌──────────────┐
 *                                   │ Create       │                     │ Update User  │
 *                                   │ earning      │                     │ balance      │
 *                                   │ record       │                     │ += amount    │
 *                                   └──────────────┘                     └──────────────┘
 */

// ============================================================================
// BAGIAN 21: SECURITY CHECKLIST
// ============================================================================

/**
 * SECURITY MEASURES IMPLEMENTED:
 * 
 * [✓] 1. VIDEO DI LUAR PUBLIC FOLDER
 *     - storage/app/private/videos/ tidak bisa diakses langsung
 *     - .htaccess "Deny from all" di folder private
 * 
 * [✓] 2. TOKEN-BASED STREAMING
 *     - Token random 64 karakter
 *     - Expire dalam 30 menit
 *     - Tied to IP address
 *     - Tied to Session ID
 * 
 * [✓] 3. RATE LIMITING
 *     - 100 request per menit untuk streaming
 *     - 60 request per menit untuk API
 *     - Log security events
 * 
 * [✓] 4. CSRF PROTECTION
 *     - Semua POST request dilindungi CSRF
 *     - Token refresh di setiap request
 * 
 * [✓] 5. XSS PROTECTION
 *     - Blade auto-escape dengan {{ }}
 *     - Content Security Policy headers
 * 
 * [✓] 6. SQL INJECTION PROTECTION
 *     - Eloquent ORM parameterized queries
 *     - No raw SQL tanpa binding
 * 
 * [✓] 7. PASSWORD SECURITY
 *     - Bcrypt hashing (Laravel default)
 *     - password_hash() dengan cost factor 12
 * 
 * [✓] 8. SESSION SECURITY
 *     - Session di database (shared hosting)
 *     - HTTPS only cookies
 *     - SameSite=Lax
 * 
 * [✓] 9. FILE UPLOAD SECURITY
 *     - Validasi mime type
 *     - Validasi ukuran file
 *     - Random filename (tidak pakai original)
 *     - Simpan di luar public folder
 * 
 * [✓] 10. ACCESS CONTROL
 *      - Role-based middleware
 *      - Policy untuk authorization
 *      - Gate untuk fine-grained control
 * 
 * [✓] 11. LOGGING
 *      - Security events logged
 *      - Failed login attempts
 *      - Invalid token attempts
 *      - Rate limit violations
 * 
 * [✓] 12. HEADERS SECURITY
 *      - X-Content-Type-Options: nosniff
 *      - X-Frame-Options: SAMEORIGIN
 *      - X-XSS-Protection: 1; mode=block
 *      - Referrer-Policy: strict-origin-when-cross-origin
 */

// ============================================================================
// BAGIAN 22: RINGKASAN URL STRUCTURE
// ============================================================================

/**
 * URL STRUCTURE:
 * 
 * PUBLIC:
 * - GET  /                           -> Halaman utama (list video)
 * - GET  /v/{slug}                   -> Tonton video (domain.com/v/a8a6sha)
 * - GET  /stream/{token}             -> Stream video file
 * 
 * AUTH REQUIRED:
 * - POST /videos/{video}/token       -> Generate stream token
 * - GET  /memberships                -> Pilih paket membership
 * - POST /memberships                -> Beli membership
 * - POST /memberships/validate-voucher -> Validasi voucher
 * 
 * UPLOADER:
 * - GET  /uploader/dashboard         -> Dashboard uploader
 * - GET  /uploader/videos/create     -> Form upload
 * - POST /uploader/videos            -> Submit upload
 * - GET  /uploader/payouts           -> Riwayat payout
 * - POST /uploader/payouts           -> Request payout
 * 
 * ADMIN:
 * - GET  /admin/dashboard            -> Dashboard admin
 * - GET  /admin/payouts              -> Kelola payout
 * - POST /admin/payouts/{id}/process -> Proses payout
 */

// ============================================================================
// BAGIAN 23: QUICK START COMMANDS
// ============================================================================

/**
 * INSTALASI FRESH PROJECT:
 * 
 * # 1. Create project
 * composer create-project laravel/laravel video-platform
 * cd video-platform
 * 
 * # 2. Install dependencies (tidak butuh yang lain untuk shared hosting)
 * # Laravel sudah include semuanya
 * 
 * # 3. Setup database
 * php artisan migrate
 * php artisan db:seed
 * 
 * # 4. Create storage link
 * php artisan storage:link
 * 
 * # 5. Create folders
 * mkdir -p storage/app/private/videos
 * mkdir -p storage/app/public/thumbnails
 * 
 * # 6. Set permissions
 * chmod -R 755 storage
 * chmod -R 755 bootstrap/cache
 * 
 * # 7. Create .htaccess di storage/app/private
 * echo "Deny from all" > storage/app/private/.htaccess
 * 
 * # 8. Run dev server (local)
 * php artisan serve
 * 
 * # 9. Test cron manually
 * php artisan earnings:calculate
 * php artisan tokens:clean
 * php artisan memberships:refresh
 */
