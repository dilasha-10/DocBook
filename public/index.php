<?php

session_start();

// BASE_PATH = the project root (one level up from public/)
define('BASE_PATH', realpath(__DIR__ . '/..'));

// BASE_URL = the URL prefix for assets (handles any subfolder or server setup)
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = $_SERVER['SCRIPT_NAME'] ?? '/index.php';       // e.g. /subdir/index.php
$base     = rtrim(dirname($script), '/');                  // e.g. /subdir  or ''
define('BASE_URL', $scheme . '://' . $host . $base);       // e.g. http://localhost:8000

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
    return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === $path;
}

// ── Controllers ───────────────────────────────────────────────────────────────

require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/controllers/PatientController.php';
require_once BASE_PATH . '/app/controllers/DoctorController.php';

// ── Routing ───────────────────────────────────────────────────────────────────

$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

// Home
if ($uri === '/')                              { redirect('/categories'); }

// Auth
if ($uri === '/login'           && $method === 'GET')  { render('login');  }
if ($uri === '/login'           && $method === 'POST') { login_post();      }
if ($uri === '/signup'          && $method === 'GET')  { render('signup'); }
if ($uri === '/signup'          && $method === 'POST') { signup_post();     }
if ($uri === '/logout'          && $method === 'GET')  { logout();          }

// Patient pages
if ($uri === '/categories'      && $method === 'GET')  { categories_page();      }
if ($uri === '/dashboard'       && $method === 'GET')  { dashboard_page();       }
if ($uri === '/profile'         && $method === 'GET')  { profile_page();         }
if ($uri === '/booking/confirm' && $method === 'GET')  { booking_confirm_page(); }
if (preg_match('#^/doctors/(\d+)$#', $uri, $m) && $method === 'GET') { doctor_booking_page((int)$m[1]); }

// Doctor pages
if ($uri === '/doctor/dashboard' && $method === 'GET') { doctor_dashboard_page(); }
if ($uri === '/doctor/profile'   && $method === 'GET') { doctor_profile_page();   }

// API routes
if ($uri === '/api/categories'        && $method === 'GET')  { api_get_categories();  }
if ($uri === '/api/slots'             && $method === 'GET')  { api_get_slots();        }
if ($uri === '/api/doctors'           && $method === 'GET')  { api_get_doctors();      }
if ($uri === '/api/appointments'      && $method === 'POST') { api_book_appointment(); }
if ($uri === '/api/profile'           && $method === 'POST') { api_update_profile();   }
if ($uri === '/api/settings/password' && $method === 'POST') { api_change_password();  }

if (preg_match('#^/api/appointments/(\d+)/cancel$#',     $uri, $m) && $method === 'PATCH') { api_cancel_appointment($m[1]);     }
if (preg_match('#^/api/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'POST')  { api_reschedule_appointment($m[1]); }

// 404
http_response_code(404);
echo '<h1>404 Not Found</h1>';