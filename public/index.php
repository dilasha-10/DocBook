<?php

session_start();

// BASE_PATH = the project root (one level up from public/)
define('BASE_PATH', realpath(__DIR__ . '/..'));

// BASE_URL = the URL prefix for assets
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$base   = rtrim(dirname($script), '/');
define('BASE_URL', $scheme . '://' . $host . $base);
define('BASE_PREFIX', $base);

require_once BASE_PATH . '/config/database.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

function render($view, $data = []) {
    extract($data);
    $file = BASE_PATH . '/app/views/pages/' . $view . '.php';
    if (!file_exists($file)) {
        http_response_code(404);
        echo '<h1>404 — View not found: ' . htmlspecialchars($view) . '</h1>';
        exit;
    }
    include $file;
    exit;
}

function redirect($url) {
    if (substr($url, 0, 1) === '/') {
        $url = BASE_PREFIX . $url;
    }
    header('Location: ' . $url);
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function request_is($path) {
    global $uri;
    return $uri === $path;
}

// ── Controllers ───────────────────────────────────────────────────────────────

require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/PatientController.php';
require_once BASE_PATH . '/app/controllers/PageController.php';

// ── Routing ───────────────────────────────────────────────────────────────────

$rawUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$uri    = (BASE_PREFIX !== '' && strpos($rawUri, BASE_PREFIX) === 0)
    ? substr($rawUri, strlen(BASE_PREFIX))
    : $rawUri;
$uri = $uri === '' ? '/' : $uri;
$method = $_SERVER['REQUEST_METHOD'];

// Home
if ($uri === '/')                              { about_page(); }
if ($uri === '/about'   && $method === 'GET') { about_page(); }
if ($uri === '/contact' && $method === 'GET') { contact_page(); }

// Auth
if ($uri === '/login'  && $method === 'GET')  { login_get();   }
if ($uri === '/login'  && $method === 'POST') { login_post();  }
if ($uri === '/signup' && $method === 'GET')  { signup_get();  }
if ($uri === '/signup' && $method === 'POST') { signup_post(); }
if ($uri === '/logout' && $method === 'GET')  { logout();      }

// Patient pages
if ($uri === '/categories'      && $method === 'GET')  { categories_page();      }
if ($uri === '/dashboard'       && $method === 'GET')  { dashboard_page();       }
if ($uri === '/profile'         && $method === 'GET')  { profile_page();         }
if ($uri === '/booking/confirm' && $method === 'GET')  { booking_confirm_page(); }
if (preg_match('#^/doctors/(\d+)$#', $uri, $m) && $method === 'GET') { doctor_booking_page((int)$m[1]); }
if (preg_match('#^/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'GET') { reschedule_page((int)$m[1]); }
if (preg_match('#^/chat/(\d+)$#', $uri, $m) && $method === 'GET') { chat_page((int)$m[1]); }

// Patient API routes
if ($uri === '/api/categories'        && $method === 'GET')  { api_get_categories();  }
if ($uri === '/api/slots'             && $method === 'GET')  { api_get_slots();        }
if ($uri === '/api/doctors'           && $method === 'GET')  { api_get_doctors();      }
if ($uri === '/api/appointments'      && $method === 'POST') { api_book_appointment(); }
if ($uri === '/api/profile'           && $method === 'POST') { api_update_profile();   }
if ($uri === '/api/settings/password' && $method === 'POST') { api_change_password();  }

if (preg_match('#^/api/appointments/(\d+)/cancel$#',     $uri, $m) && $method === 'PATCH') { api_cancel_appointment((int)$m[1]);     }
if (preg_match('#^/api/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'POST')  { api_reschedule_appointment((int)$m[1]); }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'GET')   { api_get_comments((int)$m[1]);           }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'POST')  { api_post_comment((int)$m[1]);           }
if (preg_match('#^/api/appointments/(\d+)$#',            $uri, $m) && $method === 'GET')   { api_get_appointment_detail((int)$m[1]); }
if ($uri === '/api/patient/appointments'                 && $method === 'GET') { api_patient_appointments(); }

if (preg_match('#^/api/messages/(\d+)$#', $uri, $m) && $method === 'GET')  { api_get_messages((int)$m[1]); }
if (preg_match('#^/api/messages/(\d+)$#', $uri, $m) && $method === 'POST') { api_send_message((int)$m[1]); }

// 404
http_response_code(404);
echo '<h1>404 Not Found</h1>';