<?php
session_start();

$action = $_GET['action'] ?? '';

// NO LOGUEADO
if (!isset($_SESSION['user_id'])) {

    $public = [
        'login',
        'register',
        'register_form',
        'forgot',
        'forgot_form',
        'reset',
        'activate'
    ];

    if (!in_array($action, $public)) {
        $_GET['action'] = 'login';
    }

    require 'userManagement.php';
    exit;
}

// LOGOUT
if ($action === 'logout') {
    require 'userManagement.php';
    exit;
}

// ADMIN
if (!empty($_SESSION['is_admin']) && in_array($action, [
    'dashboard',
    'products',
    'create_product_form',
    'edit_product_form',
    'create_product',
    'edit_product',
    'delete_product',
    'users',
    'delete_user',
    'orders',
    'update_order'
])) {
    require 'admin.php';
    exit;
}


// USER.PHP

require 'user.php';
exit;
