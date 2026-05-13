<?php

// Use a project-local session directory so the dev server doesn't need
// write access to /var/lib/php/sessions.
$_sessionPath = realpath(__DIR__ . '/../tmp/sessions');
if ($_sessionPath && is_dir($_sessionPath)) {
    session_save_path($_sessionPath);
}

session_start();

define('BASE_PATH', realpath(__DIR__ . '/..'));

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$base   = rtrim(dirname($script), '/');

// If the base includes /public, we also want to support the root (the parent of public)
// This is important for .htaccess rewrites that point to the public folder.
$baseRoot = rtrim(str_replace('/public', '', $base), '/');

define('BASE_URL', $scheme . '://' . $host . $base);
define('BASE_PREFIX', $base);
define('BASE_PREFIX_ROOT', $baseRoot);


require_once BASE_PATH . '/config/database.php';

// ── Helpers ──────────────────────────────────────────────────

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

// ── Controllers ───────────────────────────────────────────────

require_once BASE_PATH . '/app/controllers/AuthController.php';
require_once BASE_PATH . '/app/models/AuditLogModel.php';
require_once BASE_PATH . '/app/controllers/PaymentController.php';
require_once BASE_PATH . '/app/controllers/PatientController.php';
require_once BASE_PATH . '/app/controllers/DoctorController.php';
require_once BASE_PATH . '/app/controllers/PageController.php';
require_once BASE_PATH . '/app/controllers/AdminController.php';
require_once BASE_PATH . '/app/controllers/ChatbotController.php';
require_once BASE_PATH . '/app/controllers/LabAdminController.php';
require_once BASE_PATH . '/app/controllers/NotificationController.php';
require_once BASE_PATH . '/app/controllers/DepartmentController.php';
require_once BASE_PATH . '/app/controllers/SupportTicketController.php';
require_once BASE_PATH . '/app/controllers/AnnouncementController.php';
require_once BASE_PATH . '/app/controllers/AppointmentAuditController.php';

// ── Routing ───────────────────────────────────────────────────

$rawUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$uri    = $rawUri;

// Strip prefixes: try the longer one (BASE_PREFIX) first, then the root one
if (BASE_PREFIX !== '' && strpos($uri, BASE_PREFIX) === 0) {
    $uri = substr($uri, strlen(BASE_PREFIX));
} elseif (BASE_PREFIX_ROOT !== '' && strpos($uri, BASE_PREFIX_ROOT) === 0) {
    $uri = substr($uri, strlen(BASE_PREFIX_ROOT));
}

$uri    = ($uri === '' || $uri === false) ? '/' : $uri;
$method = $_SERVER['REQUEST_METHOD'];


// ── Home ──────────────────────────────────────────────────────

if ($uri === '/')                              { about_page(); }
if ($uri === '/about'   && $method === 'GET') { about_page(); }
if ($uri === '/contact' && $method === 'GET') { contact_page(); }

// ── Auth ──────────────────────────────────────────────────────

if ($uri === '/login'  && $method === 'GET')  { login_get();   }
if ($uri === '/login'  && $method === 'POST') { login_post();  }
if ($uri === '/signup' && $method === 'GET')  { signup_get();  }
if ($uri === '/signup' && $method === 'POST') { signup_post(); }
if ($uri === '/logout' && $method === 'GET')  { logout();      }

// ── Patient pages ─────────────────────────────────────────────

if ($uri === '/categories'      && $method === 'GET') { categories_page();      }
if ($uri === '/dashboard'       && $method === 'GET') { dashboard_page();       }
if ($uri === '/profile'         && $method === 'GET') { profile_page();         }
if ($uri === '/booking/confirm' && $method === 'GET') { booking_confirm_page(); }
if (preg_match('#^/doctors/(\d+)$#', $uri, $m)                    && $method === 'GET') { doctor_booking_page((int)$m[1]); }
if (preg_match('#^/appointments/(\d+)/reschedule$#', $uri, $m)    && $method === 'GET') { reschedule_page((int)$m[1]);     }
if (preg_match('#^/lab-report/(\d+)$#', $uri, $m)                 && $method === 'GET') { lab_report_download((int)$m[1]); }

// ── Doctor portal pages ───────────────────────────────────────

if ($uri === '/doctor/dashboard'    && $method === 'GET') { doctor_dashboard_page();    }
if ($uri === '/doctor/schedule'     && $method === 'GET') { doctor_schedule_page();     }
if ($uri === '/doctor/patients'     && $method === 'GET') { doctor_patients_page();     }
if ($uri === '/doctor/availability' && $method === 'GET') { doctor_availability_page(); }
if ($uri === '/doctor/profile'        && $method === 'GET') { doctor_profile_page();        }
if ($uri === '/doctor/notifications'  && $method === 'GET') { doctor_notifications_page();  }

// ── Doctor API routes ─────────────────────────────────────────

if ($uri === '/doctor/api/lab-report'        && $method === 'POST')                { api_doctor_lab_report();         }
if ($uri === '/doctor/api/appointments'      && $method === 'GET')                 { api_doctor_appointments();       }
if ($uri === '/doctor/api/stats'             && $method === 'GET')                 { api_doctor_stats();              }
if ($uri === '/doctor/api/appointment-detail'&& $method === 'GET')                 { api_doctor_appointment_detail(); }
if ($uri === '/doctor/api/update-status'     && $method === 'POST')                { api_doctor_update_status();      }
if ($uri === '/doctor/api/availability'      && in_array($method, ['GET','POST'])) { api_doctor_availability();       }
if ($uri === '/doctor/api/profile'           && in_array($method, ['GET','POST'])) { api_doctor_profile();            }
if ($uri === '/doctor/api/patients'          && $method === 'GET')                 { api_doctor_patients();           }
if ($uri === '/doctor/api/comment'           && $method === 'POST')                { api_doctor_comment();            }
if ($uri === '/doctor/api/slots'             && $method === 'GET')                 { api_doctor_slots();              }

// ── Patient API routes ────────────────────────────────────────

if ($uri === '/api/categories'        && $method === 'GET')  { api_get_categories(); }
if ($uri === '/api/slots'             && $method === 'GET')  { api_get_slots();       }
if ($uri === '/api/doctors'           && $method === 'GET')  { api_get_doctors();     }
if ($uri === '/api/appointments'      && $method === 'POST') { api_book_appointment(); }
if ($uri === '/api/profile'           && $method === 'POST') { api_update_profile();  }
if ($uri === '/api/settings/password' && $method === 'POST') { api_change_password(); }
if ($uri === '/api/patient/appointments' && $method === 'GET') { api_patient_appointments(); }

if (preg_match('#^/api/appointments/(\d+)/cancel$#',     $uri, $m) && $method === 'PATCH') { api_cancel_appointment((int)$m[1]);     }
if (preg_match('#^/api/appointments/(\d+)/reschedule$#', $uri, $m) && $method === 'POST')  { api_reschedule_appointment((int)$m[1]); }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'GET')   { api_get_comments((int)$m[1]);           }
if (preg_match('#^/api/appointments/(\d+)/comments$#',   $uri, $m) && $method === 'POST')  { api_post_comment((int)$m[1]);           }
if (preg_match('#^/api/appointments/(\d+)$#',            $uri, $m) && $method === 'GET')   { api_get_appointment_detail((int)$m[1]); }

// ── Notification API routes (all roles) ───────────────────────

if ($uri === '/notifications'                  && $method === 'GET')  { render('patient/notifications', ['user' => auth_user()]); }
if ($uri === '/api/notifications'              && $method === 'GET')  { api_user_notifications();              }
if ($uri === '/api/notifications/read'         && $method === 'POST') { api_user_notifications_read();         }
if ($uri === '/api/notifications/unread-count' && $method === 'GET')  { api_user_notifications_unread_count(); }

// ── Admin pages ───────────────────────────────────────────────

if ($uri === '/admin/dashboard'           && $method === 'GET') { admin_dashboard_page();           }
if ($uri === '/admin/transactions'        && $method === 'GET') { admin_transactions_page();         }
if ($uri === '/admin/chatbot-escalations' && $method === 'GET') { admin_chatbot_escalations_page();  }
if ($uri === '/admin/notifications'       && $method === 'GET') { admin_notifications_page();        }
if ($uri === '/admin/audit-trail'         && $method === 'GET') { admin_audit_trail_page();          }
if ($uri === '/admin/departments'         && $method === 'GET') { admin_departments_page();          }
if ($uri === '/admin/support-tickets'     && $method === 'GET') { admin_support_tickets_page();      }
if ($uri === '/admin/appointments'        && $method === 'GET') { admin_appointments_page();         }
if ($uri === '/admin/announcements'       && $method === 'GET') { admin_announcements_page();        }

// ── Admin API routes ──────────────────────────────────────────

if ($uri === '/admin/api/transactions'              && $method === 'GET')  { api_admin_transactions();           }
if ($uri === '/admin/api/chatbot/escalations'       && $method === 'GET')  { api_admin_chatbot_escalations();    }
if ($uri === '/admin/api/notifications/broadcast'   && $method === 'POST') { api_admin_notifications_broadcast();  }
if ($uri === '/admin/api/notifications/targeted'    && $method === 'POST') { api_admin_notifications_targeted();   }
if ($uri === '/admin/api/notifications/search-users'&& $method === 'GET')  { api_admin_notifications_search_users(); }
if ($uri === '/admin/api/notifications/broadcasts'  && $method === 'GET')  { api_admin_notifications_broadcasts();  }
if ($uri === '/admin/api/audit-trail'               && $method === 'GET')  { api_admin_audit_trail();            }
if ($uri === '/admin/api/audit-trail/export'        && $method === 'GET')  { api_admin_audit_trail_export();     }
if ($uri === '/admin/api/audit-trail/filters'       && $method === 'GET')  { api_admin_audit_trail_filters();    }

if (preg_match('#^/admin/api/chatbot/escalations/(\d+)$#', $uri, $m) && $method === 'PATCH') { api_admin_chatbot_update((int)$m[1]); }

// ── Admin: Department API routes ──────────────────────────────

if ($uri === '/admin/api/departments'              && $method === 'GET')  { api_admin_departments_list();       }
if ($uri === '/admin/api/departments'              && $method === 'POST') { api_admin_department_create();      }
if ($uri === '/admin/api/specializations'           && $method === 'GET')  { api_admin_specializations_list();   }
if ($uri === '/admin/api/specializations'           && $method === 'POST') { api_admin_specialization_create();  }

if (preg_match('#^/admin/api/departments/(\d+)$#', $uri, $m) && $method === 'PUT')    { api_admin_department_update((int)$m[1]); }
if (preg_match('#^/admin/api/departments/(\d+)$#', $uri, $m) && $method === 'DELETE') { api_admin_department_delete((int)$m[1]); }
if (preg_match('#^/admin/api/specializations/(\d+)$#', $uri, $m) && $method === 'PUT')    { api_admin_specialization_update((int)$m[1]); }
if (preg_match('#^/admin/api/specializations/(\d+)$#', $uri, $m) && $method === 'DELETE') { api_admin_specialization_delete((int)$m[1]); }

// ── Admin: Support Ticket API routes ──────────────────────────

if ($uri === '/admin/api/support-tickets'           && $method === 'GET') { api_admin_support_tickets(); }
if (preg_match('#^/admin/api/support-tickets/(\d+)$#', $uri, $m) && $method === 'GET')   { api_admin_support_ticket_detail((int)$m[1]); }
if (preg_match('#^/admin/api/support-tickets/(\d+)$#', $uri, $m) && $method === 'PATCH') { api_admin_support_ticket_update((int)$m[1]); }

// ── Admin: Appointment Audit API routes ───────────────────────

if ($uri === '/admin/api/appointments'              && $method === 'GET') { api_admin_appointments_list(); }
if ($uri === '/admin/api/appointments/doctors'      && $method === 'GET') { api_admin_doctors_list();      }
if (preg_match('#^/admin/api/appointments/(\d+)/cancel$#', $uri, $m) && $method === 'POST') { api_admin_cancel_appointment((int)$m[1]); }

// ── Admin: Announcement API routes ────────────────────────────

if ($uri === '/admin/api/announcements'             && $method === 'GET')  { api_admin_announcements_list();    }
if ($uri === '/admin/api/announcements'             && $method === 'POST') { api_admin_announcement_create();   }
if (preg_match('#^/admin/api/announcements/(\d+)$#', $uri, $m) && $method === 'PUT')    { api_admin_announcement_update((int)$m[1]); }
if (preg_match('#^/admin/api/announcements/(\d+)$#', $uri, $m) && $method === 'DELETE') { api_admin_announcement_delete((int)$m[1]); }
if (preg_match('#^/admin/api/announcements/(\d+)/toggle$#', $uri, $m) && $method === 'POST') { api_admin_announcement_toggle((int)$m[1]); }
if ($uri === '/api/announcements/active'            && $method === 'GET')  { api_active_announcements(); }

// ── Patient: Support Ticket routes ────────────────────────────

if ($uri === '/api/support-tickets'                 && $method === 'POST') { api_patient_submit_ticket(); }
if ($uri === '/api/support-tickets'                 && $method === 'GET')  { api_patient_tickets();       }

// ── Chatbot API routes ────────────────────────────────────────

if ($uri === '/api/chatbot/message'  && $method === 'POST') { api_chatbot_message();  }
if ($uri === '/api/chatbot/escalate' && $method === 'POST') { api_chatbot_escalate(); }

// ── Payment routes ────────────────────────────────────────────

if ($uri === '/api/payment/initiate' && $method === 'POST') { api_payment_initiate(); }
if ($uri === '/payment/success'      && $method === 'GET')  { payment_success_page(); }
if ($uri === '/payment/failure'      && $method === 'GET')  { payment_failure_page(); }

// ── Lab admin routes ──────────────────────────────────────────

if ($uri === '/lab-admin/dashboard'                && $method === 'GET')  { lab_admin_dashboard_page();          }
if ($uri === '/lab-admin/profile'                  && $method === 'GET')  { lab_admin_profile_page();            }
if ($uri === '/lab-admin/api/find-patient'         && $method === 'GET')  { api_lab_admin_find_patient();        }
if ($uri === '/lab-admin/api/patient-appointments' && $method === 'GET')  { api_lab_admin_patient_appointments(); }
if ($uri === '/lab-admin/api/upload-report'        && $method === 'POST') { api_lab_admin_upload_report();       }

// ── 404 ───────────────────────────────────────────────────────

http_response_code(404);
echo '<h1>404 Not Found</h1>';