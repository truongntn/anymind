<?php
if (isset($_COOKIE['super_admin'])) {
    unset($_COOKIE['super_admin']);
    setcookie('super_admin', null, -1);
}
header("Location: ./");
