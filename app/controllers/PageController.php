<?php

function about_page()
{
    $user = auth_user();
<<<<<<< HEAD
    if (($user['role'] ?? '') === 'lab_admin') {
        // Lab admins should not land on patient pages - redirect to their dashboard
        redirect('/lab-admin/dashboard');
    }
=======
    $role = $user['role'] ?? '';
    if ($role === 'lab_admin') redirect('/lab-admin/dashboard');
    if ($role === 'doctor')    redirect('/doctor/dashboard');
    if ($role === 'admin')     redirect('/admin/dashboard');
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
    render('about', ['user' => $user]);
}

function contact_page()
{
    $user = auth_user();
<<<<<<< HEAD
    if (($user['role'] ?? '') === 'lab_admin') {
        redirect('/lab-admin/dashboard');
    }
=======
    $role = $user['role'] ?? '';
    if ($role === 'lab_admin') redirect('/lab-admin/dashboard');
    if ($role === 'doctor')    redirect('/doctor/dashboard');
    if ($role === 'admin')     redirect('/admin/dashboard');
>>>>>>> 6d104ef (Fixed: notification redirection for patient, doctor, admin and lab admin notification system)
    render('contact', ['user' => $user]);
}