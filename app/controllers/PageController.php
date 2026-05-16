<?php

function about_page()
{
    $user = auth_user();
    $role = $user['role'] ?? '';
    if ($role === 'lab_admin') redirect('/lab-admin/dashboard');
    if ($role === 'doctor')    redirect('/doctor/dashboard');
    if ($role === 'admin')     redirect('/admin/dashboard');
    render('about', ['user' => $user]);
}

function contact_page()
{
    $user = auth_user();
    $role = $user['role'] ?? '';
    if ($role === 'lab_admin') redirect('/lab-admin/dashboard');
    if ($role === 'doctor')    redirect('/doctor/dashboard');
    if ($role === 'admin')     redirect('/admin/dashboard');
    render('contact', ['user' => $user]);
}