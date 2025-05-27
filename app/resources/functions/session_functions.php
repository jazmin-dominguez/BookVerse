<?php

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function endSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

function destroySession() {
    startSession();
    session_destroy();
}

function isAdmin() {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireAdmin() {
    startSession();
    if (!isAdmin()) {
        header('Location: /BookVerse/auth/login');
        exit;
    }
}

function requireLogin() {
    startSession();
    if (!isLoggedIn()) {
        header('Location: /BookVerse/auth/login');
        exit;
    }
}
