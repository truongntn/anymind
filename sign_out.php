<?php
if (isset($_COOKIE['phone'])) {
    unset($_COOKIE['phone']);
    setcookie('phone', null, -1);
}
if (isset($_COOKIE['admin'])) {
    unset($_COOKIE['admin']);
    setcookie('admin', null, -1);
}
if (isset($_COOKIE['name'])) {
    unset($_COOKIE['name']);
    setcookie('name', null, -1);
}
if (isset($_COOKIE['user'])) {
    unset($_COOKIE['user']);
    setcookie('user', null, -1);
}
header("Location: ./?ref=" . $_COOKIE['ref']);
