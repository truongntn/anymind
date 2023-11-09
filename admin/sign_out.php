<?php
if (isset($_COOKIE['email'])) {
    unset($_COOKIE['email']);
    setcookie('email', null, -1);
}
if (isset($_COOKIE['admin'])) {
    unset($_COOKIE['admin']);
    setcookie('admin', null, -1);
}
header("Location: ./");
