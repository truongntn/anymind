<?php
if (!isset($_COOKIE['phone']) || !isset($_COOKIE['admin'])) {
    header("Location: ./");
}

require_once("config.php");
$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//get setting
$sql_setting = "SELECT * FROM admin where admin_email='" . $_COOKIE['admin'] . "'";
$result_setting = mysqli_query($conn, $sql_setting);
$intervall = 60;
$nomitarbeiter = 1;
if ($result_setting = mysqli_query($conn, $sql_setting)) {
    if ($result_setting && $result_setting->num_rows > 0) {
        while ($row_setting = $result_setting->fetch_assoc()) {
            $intervall = $row_setting["intervall"];
            $nomitarbeiter = $row_setting["nomitarbeiter"];
        }
    }
} //end get setting

$booking_time = array();
$sql_time = "select booking_time, booking_minute,count(booking_time) as num_booking from booking where admin='" . $_COOKIE["admin"] . "' and booking_date='" . $_GET["date"] . "' and booking_status=1 group by booking_time,booking_minute";
if ($result_time = mysqli_query($conn, $sql_time)) {
    if ($result_time && $result_time->num_rows > 0) {
        while ($row_time = $result_time->fetch_assoc()) {
            if ($row_time["num_booking"] >= $nomitarbeiter)
                array_push($booking_time, str_pad($row_time["booking_time"], 2, '0', STR_PAD_LEFT) . ":" . str_pad($row_time["booking_minute"], 2, '0', STR_PAD_LEFT));
            //array_push($booking_time, $row_time["booking_time"]);
        }
    }
}

echo implode(",", $booking_time);
