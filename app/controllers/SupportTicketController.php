<?php

//  SupportTicketController.php
//  Admin: Patient Support & Inquiries

require_once BASE_PATH . '/app/models/SupportTicketModel.php';
require_once BASE_PATH . '/app/models/NotificationModel.php';

// Admin Pages

function admin_support_tickets_page(): void
{
    $user = require_admin();
    render('admin/support_tickets', ['user' => $user]);
}

// Admin API

function api_admin_support_tickets(): void
{
    require_admin_api();

    $tickets = get_all_support_tickets([
        'status'   => $_GET['status']   ?? '',
        'category' => $_GET['category'] ?? '',
        'search'   => $_GET['search']   ?? '',
    ]);

    $stats = get_ticket_stats();

    json_response([
        'success' => true,
        'tickets' => $tickets,
        'stats'   => $stats,
    ]);
}

function api_admin_support_ticket_detail(int $id): void
{
    require_admin_api();
    $ticket = get_support_ticket_by_id($id);
    if (!$ticket) {
        json_response(['success' => false, 'error' => 'Ticket not found.'], 404);
    }
    json_response(['success' => true, 'ticket' => $ticket]);
}

function api_admin_support_ticket_update(int $id): void
{
    $user = require_admin_api();
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $status = trim($data['status'] ?? '');
    $reply  = trim($data['reply']  ?? '');

    if ($status === '' || !in_array($status, ['open', 'in_progress', 'resolved'])) {
        json_response(['success' => false, 'error' => 'Invalid status.'], 422);
    }

    if ($reply !== '') {
        // Reply and update status
        $result = reply_to_ticket($id, (int)$user['id'], $reply, $status);
        if (isset($result['error'])) {
            json_response(['success' => false, 'error' => $result['error']], 404);
        }

        // Send notification to the patient
        $ticket = get_support_ticket_by_id($id);
        if ($ticket) {
            notify_targeted(
                (int)$user['id'],
                (int)$ticket['patient_id'],
                'Support Ticket Update',
                "Your support ticket \"{$ticket['subject']}\" has been updated to: " . ucfirst(str_replace('_', ' ', $status)) . ". Check your support tickets for the admin reply."
            );
        }

        audit_log((int)$user['id'], $user['name'], $user['role'], 'TICKET_REPLIED', 'admin',
            "Replied to ticket #{$id}, status → {$status}");
    } else {
        // Just update status
        update_ticket_status($id, $status);
        audit_log((int)$user['id'], $user['name'], $user['role'], 'TICKET_STATUS_CHANGED', 'admin',
            "Ticket #{$id} status → {$status}");
    }

    json_response(['success' => true]);
}

// Patient API (submit ticket)

function api_patient_submit_ticket(): void
{
    $user = require_auth_api();
    if (($user['role'] ?? '') !== 'patient') {
        json_response(['success' => false, 'error' => 'Only patients can submit support tickets.'], 403);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $subject  = trim($data['subject']  ?? '');
    $message  = trim($data['message']  ?? '');
    $category = trim($data['category'] ?? 'other');

    if ($subject === '' || $message === '') {
        json_response(['success' => false, 'error' => 'Subject and message are required.'], 422);
    }

    $validCategories = ['login_issue', 'payment_query', 'appointment_issue', 'account_issue', 'other'];
    if (!in_array($category, $validCategories)) $category = 'other';

    $id = create_support_ticket((int)$user['id'], $subject, $message, $category);

    json_response(['success' => true, 'ticket_id' => $id]);
}

function api_patient_tickets(): void
{
    $user = require_auth_api();
    $tickets = get_patient_tickets((int)$user['id']);
    json_response(['success' => true, 'tickets' => $tickets]);
}