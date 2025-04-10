<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function is_admin()
{
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function get_user_id()
{
    return isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
}

function get_user_role()
{
    return isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : null;
}
