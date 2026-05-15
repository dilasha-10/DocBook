<?php

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/models/AdminModel.php';

function about_page()
{
    $user = auth_user();
    render('about', [
        'user' => $user,
    ]);
}

function contact_page()
{
    $user = auth_user(); 
    render('contact', [
        'user' => $user,
    ]);
}

function admin_page()
{
    $user = require_admin();
    $dashboard = admin_get_dashboard_data();
    render('admin/index', [
        'user' => $user,
        'dashboard' => $dashboard,
    ]);
}