<?php
require_once("config.php");
require_once("admin/GoogleCalendarApi.php");

$capi = new GoogleCalendarApi();

$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "select * from admin where access_token<>'' and refresh_token<>''";
if ($result = mysqli_query($conn, $sql)) {
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            //get setting
            $sql_setting = "SELECT * FROM admin where admin_email='" . $row["admin_email"] . "'";
            $result_setting = mysqli_query($conn, $sql_setting);
            $nomitarbeiter = 1;
            if ($result_setting = mysqli_query($conn, $sql_setting)) {
                if ($result_setting && $result_setting->num_rows > 0) {
                    while ($row_setting = $result_setting->fetch_assoc()) {
                        $nomitarbeiter = $row_setting["nomitarbeiter"];
                    }
                }
            } //end get setting

            $refresh_token = $capi->GetAccessTokenRefresh($APPLICATION_ID, $APPLICATION_REDIRECT_URL, $APPLICATION_SECRET, $row['refresh_token']);
            $sql_token = "update admin set access_token='" .  $refresh_token["access_token"] . "' where admin_email='" . $row["admin_email"] . "'";
            mysqli_query($conn, $sql_token);

            $calendar_list = $capi->GetCalendarsList($refresh_token["access_token"]);
            for ($i = 0; $i < count($calendar_list); $i++) {
                try {
                    $calendar_event = $capi->GetCalendarEvent($calendar_list[$i]["id"], $refresh_token["access_token"]);
                    for ($j = 0; $j < count($calendar_event); $j++) {
                        $sql_booking = "SELECT * FROM booking where event_id='" . $calendar_event[$j]["id"] . "'";
                        if ($result_booking = mysqli_query($conn, $sql_booking)) {
                            $start_date  = explode("T", $calendar_event[$j]["start"]["dateTime"])[0];
                            $start_hour  = (int)explode(":", explode("T", $calendar_event[$j]["start"]["dateTime"])[1])[0];
                            $start_minute  = (int)explode(":", explode("T", $calendar_event[$j]["start"]["dateTime"])[1])[1];

                            if ($result_booking && $result_booking->num_rows > 0) {
                                if ($start_minute >= 30) {
                                    $sql_update_booking = "update booking set  booking_date='" . $start_date . "', booking_time='" . ($start_hour + 1) . "' where event_id='" . $calendar_event[$j]["id"] . "'";
                                    mysqli_query($conn, $sql_update_booking);
                                } else {
                                    $sql_update_booking = "update booking set  booking_date='" . $start_date . "', booking_time='" . $start_hour . "' where event_id='" . $calendar_event[$j]["id"] . "'";
                                    mysqli_query($conn, $sql_update_booking);
                                }
                            } else {
                                if ($start_minute >= 30) {
                                    $sql_time = "select booking_time, booking_minute,count(booking_time) as num_booking from booking where admin='" . $row["admin_email"] . "' and booking_date='" . $start_date . "' and booking_time='" . ($start_hour + 1) . "' and booking_status=1 group by booking_time,booking_minute";
                                    if ($result_time = mysqli_query($conn, $sql_time)) {
                                        if ($result_time && $result_time->num_rows > 0) {
                                            while ($row_time = $result_time->fetch_assoc()) {
                                                if ($row_time["num_booking"] < $nomitarbeiter) {
                                                    $sql_new_booking = "insert into booking value (null,'" . $calendar_event[$j]["summary"] . "','','" . $start_date . "','" . ($start_hour + 1) . "','0','" . $row["admin_email"] . "','1','1','" . $calendar_event[$j]["id"] . "')";
                                                    mysqli_query($conn, $sql_new_booking);
                                                }
                                            }
                                        } else {
                                            $sql_new_booking = "insert into booking value (null,'" . $calendar_event[$j]["summary"] . "','','" . $start_date . "','" . ($start_hour + 1) . "','0','" . $row["admin_email"] . "','1','1','" . $calendar_event[$j]["id"] . "')";
                                            mysqli_query($conn, $sql_new_booking);
                                        }
                                    }
                                } else {
                                    $sql_time = "select booking_time, booking_minute,count(booking_time) as num_booking from booking where admin='" . $row["admin_email"] . "' and booking_date='" . $start_date . "' and booking_time='" . $start_hour . "' and booking_status=1 group by booking_time,booking_minute";
                                    if ($result_time = mysqli_query($conn, $sql_time)) {
                                        if ($result_time && $result_time->num_rows > 0) {
                                            while ($row_time = $result_time->fetch_assoc()) {
                                                if ($row_time["num_booking"] < $nomitarbeiter) {
                                                    $sql_new_booking = "insert into booking value (null,'" . $calendar_event[$j]["summary"] . "','','" . $start_date . "','" . $start_hour . "','0','" . $row["admin_email"] . "','1','1','" . $calendar_event[$j]["id"] . "')";
                                                    mysqli_query($conn, $sql_new_booking);
                                                }
                                            }
                                        } else {
                                            $sql_new_booking = "insert into booking value (null,'" . $calendar_event[$j]["summary"] . "','','" . $start_date . "','" . $start_hour . "','0','" . $row["admin_email"] . "','1','1','" . $calendar_event[$j]["id"] . "')";
                                            mysqli_query($conn, $sql_new_booking);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Throwable $t) {
                    echo $t;
                }
            }
        }
        mysqli_close($conn);
    }
}
