<?php

function about_page()
{
    $user = auth_user();
    if (($user['role'] ?? '') === 'lab_admin') {
        // Lab admins should not land on patient pages - redirect to their dashboard
        redirect('/lab-admin/dashboard');
    }
    render('about', ['user' => $user]);
}

function contact_page()
{
    $user = auth_user();
    if (($user['role'] ?? '') === 'lab_admin') {
        redirect('/lab-admin/dashboard');
    }
    render('contact', ['user' => $user]);
}