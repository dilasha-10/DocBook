<?php

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