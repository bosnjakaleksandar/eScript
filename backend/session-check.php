<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function is_admin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function get_user_id()
{
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function get_user_role()
{
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
