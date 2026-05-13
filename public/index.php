<?php

session_start();

define('BASE_PATH', realpath(__DIR__ . '/..'));

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$base   = rtrim(dirname($script), '/');
define('BASE_URL', $scheme . '://' . $host . $base);
define('BASE_PREFIX', $base);

require_once BASE_PATH . '/config/database.php';

// Helpers

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
require_once BASE_PATH . '/app/controllers/DoctorController.php';
require_once BASE_PATH . '/app/controllers/PageController.php';

// ── Routing ───────────────────────────────────────────────────────────────────

$rawUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$uri    = (BASE_PREFIX !== '' && strpos($rawUri, BASE_PREFIX) === 0)
    ? substr($rawUri, strlen(BASE_PREFIX))
    : $rawUri;
$uri = $uri === '' ? '/' : $uri;
$method = $_SERVER['REQUEST_METHOD'];

// Home — show about/home page by default
if ($uri === '/')                              { about_page(); }
if ($uri === '/about'   && $method === 'GET') { about_page(); }
if ($uri === '/contact' && $method === 'GET') { contact_page(); }

// Auth
if ($uri === '/login'  && $method === 'GET')  { login_get();    }
if ($uri === '/login'  && $method === 'POST') { login_post();   }
if ($uri === '/signup' && $method === 'GET')  { signup_get();   }
if ($uri === '/signup' && $method === 'POST') { signup_post();  }
if ($uri === '/logout' && $method === 'GET')  { logout();       }

// Patient pages
if ($uri === '/categories'      && $method === 'GET')  { categories_page();      }
if ($uri === '/dashboard'       && $method === 'GET')  { dashboard_page();       }
if ($uri === '/profile'         && $method === 'GET')  { profile_page();         }
if ($uri === '/booking/confirm' && $method === 'GET')  { booking_confirm_page(); }
if (preg_match('#^/doctors/(\d+)$#', $uri, $m) && $method === 'GET') { doctor_booking_page((int)$m[1]); }
if (preg_match('#^/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'GET') { reschedule_page((int)$m[1]); }
if (preg_match('#^/chat/(\d+)$#', $uri, $m) && $method === 'GET') { chat_page((int)$m[1]); }

// Doctor portal pages
if ($uri === '/doctor/dashboard'    && $method === 'GET') { doctor_dashboard_page();    }
if ($uri === '/doctor/schedule'     && $method === 'GET') { doctor_schedule_page();     }
if ($uri === '/doctor/patients'     && $method === 'GET') { doctor_patients_page();     }
if ($uri === '/doctor/availability' && $method === 'GET') { doctor_availability_page(); }
if ($uri === '/doctor/profile'      && $method === 'GET') { doctor_profile_page();      }

// Doctor chat page
if (preg_match('#^/doctor/chat/(\d+)$#', $uri, $m) && $method === 'GET') { doctor_chat_page((int)$m[1]); }

// Doctor API routes (/doctor/api/*)
if ($uri === '/doctor/api/appointments'       && $method === 'GET')                { api_doctor_appointments();       }
if ($uri === '/doctor/api/stats'              && $method === 'GET')                { api_doctor_stats();              }
if ($uri === '/doctor/api/appointment-detail' && $method === 'GET')                { api_doctor_appointment_detail(); }
if ($uri === '/doctor/api/update-status'      && $method === 'POST')               { api_doctor_update_status();      }
if ($uri === '/doctor/api/availability'       && in_array($method, ['GET','POST'])) { api_doctor_availability();       }
if ($uri === '/doctor/api/profile'            && in_array($method, ['GET','POST'])) { api_doctor_profile();            }
if ($uri === '/doctor/api/patients'           && $method === 'GET')                { api_doctor_patients();           }
if ($uri === '/doctor/api/comment'            && $method === 'POST')               { api_doctor_comment();            }
if ($uri === '/doctor/api/slots'              && $method === 'GET')                { api_doctor_slots();              }
if (preg_match('#^/doctor/api/messages/(\d+)$#', $uri, $m) && $method === 'GET')  { api_doctor_get_messages((int)$m[1]); }
if (preg_match('#^/doctor/api/messages/(\d+)$#', $uri, $m) && $method === 'POST') { api_doctor_send_message((int)$m[1]); }

// API routes
if ($uri === '/api/categories'        && $method === 'GET')  { api_get_categories();  }
if ($uri === '/api/slots'             && $method === 'GET')  { api_get_slots();        }
if ($uri === '/api/doctors'           && $method === 'GET')  { api_get_doctors();      }
if ($uri === '/api/appointments'      && $method === 'POST') { api_book_appointment(); }
if ($uri === '/api/profile'           && $method === 'POST') { api_update_profile();   }
if ($uri === '/api/settings/password' && $method === 'POST') { api_change_password();  }

if (preg_match('#^/api/appointments/(\d+)/cancel$#',     $uri, $m) && $method === 'PATCH') { api_cancel_appointment((int)$m[1]);     }
if (preg_match('#^/api/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'POST')  { api_reschedule_appointment((int)$m[1]); }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'GET')   { api_get_comments((int)$m[1]);           }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'POST')  { api_post_comment((int)$m[1]);            }
if (preg_match('#^/api/appointments/(\d+)$#',            $uri, $m) && $method === 'GET')   { api_get_appointment_detail((int)$m[1]); }
if ($uri === '/api/patient/appointments'                 && $method === 'GET') { api_patient_appointments(); }

if (preg_match('#^/api/messages/(\d+)$#', $uri, $m) && $method === 'GET')  { api_get_messages((int)$m[1]); }
if (preg_match('#^/api/messages/(\d+)$#', $uri, $m) && $method === 'POST') { api_send_message((int)$m[1]); }

// 404
http_response_code(404);
echo '<h1>404 Not Found</h1>';