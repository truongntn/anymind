<?php
if (!isset($_COOKIE['phone']) || !isset($_COOKIE['admin']) || !isset($_COOKIE['user']) || !isset($_COOKIE['name'])) {
  header("Location: ./");
}
require_once("config.php");
require_once("admin/GoogleCalendarApi.php");

$capi = new GoogleCalendarApi();

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

$available_time = array();
$hour = date('H');
$current_hour = date('H');
if ($hour < 9) $hour = 9;
$available_time[0] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
$minute = 0;
while ($hour < 19) {
  $minute = $minute + $intervall;
  if ($minute < 60)
    array_push($available_time, str_pad($hour, 2, '0', STR_PAD_LEFT) . ":" . str_pad($minute, 2, '0', STR_PAD_LEFT));
  else {
    array_push($available_time, str_pad(($hour + 1), 2, '0', STR_PAD_LEFT) . ":" . str_pad(($minute - 60), 2, '0', STR_PAD_LEFT));
    $minute = $minute - 60;
    $hour++;
  }
}
$booking_time = array();
$sql_time = "select booking_time, booking_minute,count(booking_time) as num_booking from booking where admin='" . $_COOKIE["admin"] . "' and booking_date='" . date("Y-m-d") . "' and booking_status=1 group by booking_time,booking_minute";
if ($result_time = mysqli_query($conn, $sql_time)) {
  if ($result_time && $result_time->num_rows > 0) {
    while ($row_time = $result_time->fetch_assoc()) {
      if ($row_time["num_booking"] >= $nomitarbeiter)
        array_push($booking_time, str_pad($row_time["booking_time"], 2, '0', STR_PAD_LEFT) . ":" . str_pad($row_time["booking_minute"], 2, '0', STR_PAD_LEFT));
    }
  }
}
$available_time = array_diff($available_time, $booking_time);

$booking_list = array();
$sql = "select * from booking where user='" . $_COOKIE["phone"] . "' and admin='" . $_COOKIE["admin"] . "'";
if ($result = mysqli_query($conn, $sql)) {
  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $myObj = new stdClass();
      $myObj->title = $row["name"];
      //$myObj->description =  $row["user"];
      $myObj->start = $row["booking_date"] . "T" . str_pad($row["booking_time"], 2, '0', STR_PAD_LEFT) . ":" . str_pad($row["booking_minute"], 2, '0', STR_PAD_LEFT) . ":00";
      $myObj->status = $row["booking_status"];
      //$myObj->allDay = false;
      if ($row["booking_status"] === "0")
        $myObj->className = "fc-event-solid-danger fc-event-light bg-warning text-white";
      else if ($row["booking_status"] === "1")
        $myObj->className = "fc-event-solid-danger fc-event-light bg-success text-white";
      else if ($row["booking_status"] === "-1")
        $myObj->className = "fc-event-solid-danger fc-event-light bg-danger text-white";
      array_push($booking_list, json_encode($myObj));
    }
  }
}
if (isset($_POST['kt_booking_submit'])) {
  $booking_date = explode(".", $_POST['date'])[2] . "-" . explode(".", $_POST['date'])[1] . "-" . explode(".", $_POST['date'])[0];

  try {
    $sql = "SELECT * FROM user, admin where user.admin=admin.admin_email and user_id='" . $_COOKIE['user'] . "'";
    if ($result = mysqli_query($conn, $sql)) {
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          if ($row['access_token'] != "") {
            $user_timezone = $capi->GetUserCalendarTimezone($row["access_token"]);

            if ($row["calendar_id"] != "")
              $calendar_id = $row["calendar_id"];
            else
              $calendar_id = 'primary';

            $event_title = $_COOKIE['name'] . " - " . $_COOKIE["phone"];
            $full_day_event = 0;

            $hour = explode(":", $_POST['time'])[0];
            $minute = explode(":", $_POST['time'])[1];
            $minute = $minute + $intervall;
            if ($minute >= 60) {
              $hour = $hour + 1;
              $minute = $minute - 60;
            }
            $event_time = ['start_time' => $booking_date . 'T' . explode(":", $_POST['time'])[0] . ':' . explode(":", $_POST['time'])[1] . ':00', 'end_time' => $booking_date . 'T' . $hour . ':' . $minute . ':00'];
            $event_id = $capi->CreateCalendarEvent($calendar_id, $event_title, $full_day_event, $event_time, $user_timezone, $row["access_token"]);

            $sql = "insert into booking value (null,'" . $_COOKIE["phone"] . "','" . $_COOKIE['name'] . "','" . $booking_date . "','" . explode(":", $_POST['time'])[0] . "','" . explode(":", $_POST['time'])[1] . "','" . $_COOKIE['admin'] . "','1','1','" . $event_id . "')";
            mysqli_query($conn, $sql);
            //$inset_id = mysqli_insert_id($conn);

            //$sql = "update booking set is_sync=1 where booking_id='" . $inset_id . "'";
            //mysqli_query($conn, $sql);
          }
        }
      }
    }
  } catch (Throwable $t) {
    header("Location: calendar");
  }

  header("Location: calendar");
  mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
  <base href="" />
  <title>Anwendung Center</title>
  <meta name="description" content="Anwendung Center" />
  <meta name="keywords" content="Anwendung Center" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <meta property="og:locale" content="en_US" />
  <meta property="og:type" content="article" />
  <meta property="og:title" content="Anwendung Center" />
  <link rel="shortcut icon" href="assets/media/logos/favicon.ico" />
  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
  <!--end::Fonts-->
  <!--begin::Page Vendor Stylesheets(used by this page)-->
  <link href="assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
  <!--end::Page Vendor Stylesheets-->
  <!--begin::Global Stylesheets Bundle(used by all pages)-->
  <link href="assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
  <link href="assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
  <link href="assets/plugins/custom/datepicker/datepicker.css" rel="stylesheet" type="text/css" />
  <style>
    .fc-day-grid-event .fc-content {
      white-space: normal !important;
    }
  </style>
  <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" style="background-image: url(assets/media/patterns/header-bg.png)" class="toolbar-enabled">
  <!--begin::Main-->
  <!--begin::Root-->
  <div class="d-flex flex-column flex-root">
    <!--begin::Page-->
    <div class="page d-flex flex-row flex-column-fluid">
      <!--begin::Wrapper-->
      <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
        <!--begin::Header-->
        <div id="kt_header" class="header align-items-stretch" data-kt-sticky="true" data-kt-sticky-name="header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
          <!--begin::Container-->
          <div class="container-xxl d-flex align-items-center">
            <!--begin::Header Logo-->
            <div class="header-logo me-5 me-md-10 flex-grow-1 flex-lg-grow-0">
              <span>
                <!--<img
                    alt="Logo"
                    src="assets/media/logos/logo-light.svg"
                    class="h-15px h-lg-20px logo-default"
                  />
                  <img
                    alt="Logo"
                    src="assets/media/logos/logo-default.svg"
                    class="h-15px h-lg-20px logo-sticky"
                  />-->
                <h2 class="h-15px h-lg-20px menu-title text-white">
                  Anwendung Center
                </h2>
              </span>
            </div>
            <!--end::Header Logo-->
            <!--begin::Wrapper-->
            <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
              <!--begin::Navbar-->
              <div class="d-flex align-items-stretch" id="kt_header_nav">
                <!--begin::Menu wrapper-->
                <div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
                  <!--begin::Menu-->

                  <!--end::Menu-->
                </div>
                <!--end::Menu wrapper-->
              </div>
              <!--end::Navbar-->
              <!--begin::Topbar-->
              <div class="d-flex align-items-stretch flex-shrink-0">
                <!--begin::Toolbar wrapper-->
                <div class="topbar d-flex align-items-stretch flex-shrink-0">
                  <!--begin::User-->
                  <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                    <!--begin::Menu wrapper-->
                    <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                      <i class="bi bi-person-circle fs-2x text-white" title="Menu" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-dismiss="click" data-bs-trigger="hover"></i>
                    </div>
                    <!--begin::Menu-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
                      <!--begin::Menu item-->
                      <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3">
                          <!--begin::Avatar-->
                          <div class="symbol symbol-50px me-5">
                            <i class="bi bi-person-circle fs-2x"></i>
                          </div>
                          <!--end::Avatar-->
                          <!--begin::Username-->
                          <div class="d-flex flex-column">
                            <div class="fw-bolder d-flex align-items-center fs-5">
                              <?php echo $_COOKIE["name"]; ?>
                            </div>
                            <!--<span class="fw-bold text-muted text-hover-primary fs-7"><?php echo $_COOKIE["name"]; ?></span>-->
                          </div>
                          <!--end::Username-->
                        </div>
                      </div>
                      <!--end::Menu item-->
                      <!--begin::Menu separator-->
                      <div class="separator my-2"></div>
                      <!--end::Menu separator-->


                      <!--begin::Menu item-->
                      <div class="menu-item px-5">
                        <a href="sign_out" class="menu-link px-5">Abmelden</a>
                      </div>
                      <!--end::Menu item-->
                    </div>
                    <!--end::Menu-->
                    <!--end::Menu wrapper-->
                  </div>
                  <!--end::User -->
                  <!--begin::Aside mobile toggle-->
                  <!--end::Aside mobile toggle-->
                </div>
                <!--end::Toolbar wrapper-->
              </div>
              <!--end::Topbar-->
            </div>
            <!--end::Wrapper-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::Header-->
        <!--begin::Toolbar-->
        <div class="toolbar py-5 py-lg-15" id="kt_toolbar">
          <!--begin::Container-->
          <div id="kt_toolbar_container" class="container-xxl d-flex flex-stack flex-wrap">
            <!--begin::Title-->
            <h3 class="text-white fw-bolder me-5">Meine Termine</h3>
            <!--begin::Title-->
            <!--begin::Actions-->
            <div class="d-flex align-items-center flex-wrap py-2">
              <!--begin::Button-->
              <a href="#" class="btn btn-success my-2" tooltip="New App" data-bs-toggle="modal" data-bs-target="#kt_modal_new_booking">Neue Termin buchen</a>
              <!--end::Button-->
            </div>
            <!--end::Actions-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::Toolbar-->
        <!--begin::Container-->
        <div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
          <!--begin::Post-->
          <div class="content flex-row-fluid" id="kt_content">
            <!--begin::Index-->
            <div class="card card-page">
              <!--begin::Card body-->
              <div class="card-body">
                <!--begin::Calendar Widget 1-->
                <div class="card card-xxl-stretch">
                  <!--begin::Card body-->
                  <div class="card-body">
                    <!--begin::Calendar-->
                    <div id="kt_calendar_widget_1"></div>
                    <!--end::Calendar-->
                  </div>

                  <div class="d-flex align-items-center px-9 py-5">
                    <!--begin::Item-->
                    <div class="d-flex align-items-center me-6">
                      <!--begin::Bullet-->
                      <span class="rounded-1 bg-warning me-2 h-10px w-10px"></span>
                      <!--end::Bullet-->

                      <!--begin::Label-->
                      <span class="fw-semibold fs-6 text-gray-600">Anstehend</span>
                      <!--end::Label-->
                    </div>
                    <!--ed::Item-->

                    <!--begin::Item-->
                    <div class="d-flex align-items-center me-6">
                      <!--begin::Bullet-->
                      <span class="rounded-1 bg-success me-2 h-10px w-10px"></span>
                      <!--end::Bullet-->

                      <!--begin::Label-->
                      <span class="fw-semibold fs-6 text-gray-600">Genehmigt</span>
                      <!--end::Label-->
                    </div>
                    <!--ed::Item-->

                    <!--begin::Item-->
                    <div class="d-flex align-items-center">
                      <!--begin::Bullet-->
                      <span class="rounded-1 bg-danger me-2 h-10px w-10px"></span>
                      <!--end::Bullet-->

                      <!--begin::Label-->
                      <span class="fw-semibold fs-6 text-gray-600">Storniert</span>
                      <!--end::Label-->
                    </div>
                    <!--ed::Item-->
                  </div>

                  <!--end::Card body-->
                </div>
                <!--end::Calendar Widget 1-->
              </div>
              <!--end::Card body-->
            </div>
            <!--end::Index-->
          </div>
          <!--end::Post-->
        </div>
        <!--end::Container-->
        <?php require_once("footer.php") ?>
      </div>
      <!--end::Wrapper-->
    </div>
    <!--end::Page-->
  </div>
  <!--end::Root-->

  <!--begin::Modal - Create App-->
  <div class="modal fade" id="kt_modal_new_booking" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered">
      <!--begin::Modal content-->
      <div class="modal-content">
        <!--begin::Modal header-->
        <div class="modal-header">
          <!--begin::Modal title-->
          <h2>Neue Termin buchen</h2>
          <!--end::Modal title-->
          <!--begin::Close-->
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr061.svg-->
            <span class="svg-icon svg-icon-1">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
              </svg>
            </span>
            <!--end::Svg Icon-->
          </div>
          <!--end::Close-->
        </div>
        <!--end::Modal header-->
        <!--begin::Modal body-->
        <div class="modal-body py-lg-5 px-lg-5">
          <!--begin::Form-->
          <form method="post" class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" id="kt_booking_form" action="">
            <input readonly="readonly" type="text" class="form-control span2" value="<?php echo date("d.m.Y") ?>" id="date" name="date" placeholder="Pick a date"><br>
            <select class="form-select form-select-lg" id="time" name="time" data-control="select2" data-hide-search="true" data-placeholder="Bitte Uhrzeit auswaehlen">
              <option></option>
              <?php
              foreach ($available_time as $time) { ?>

                <option value="<?php echo $time ?>"><?php echo $time ?></option>
              <?php } ?>
            </select>
            <!--begin::Wrapper-->
            <div class="mt-5">
              <button type="submit" class="btn btn-lg btn-primary my-2" id="kt_booking_submit" name="kt_booking_submit">
                <span class="indicator-label">Buchen
                  <!--begin::Svg Icon | path: icons/duotune/arrows/arr064.svg-->
                  <span class="svg-icon svg-icon-3 ms-2 me-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                      <rect opacity="0.5" x="18" y="13" width="13" height="2" rx="1" transform="rotate(-180 18 13)" fill="black" />
                      <path d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z" fill="black" />
                    </svg>
                  </span>
                  <!--end::Svg Icon--></span>
                <span class="indicator-progress">Please wait...
                  <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
              </button>
            </div>
            <!--end::Wrapper-->
          </form>
          <!--end::Form-->
        </div>
        <!--end::Modal body-->
      </div>
      <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
  </div>
  <!--end::Modal - Create App-->

  <!--begin::Scrolltop-->
  <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
    <span class="svg-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
        <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
        <path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
      </svg>
    </span>
    <!--end::Svg Icon-->
  </div>
  <!--end::Scrolltop-->
  <!--end::Main-->
  <script>
    var hostUrl = "assets/";
  </script>
  <!--begin::Javascript-->
  <!--begin::Global Javascript Bundle(used by all pages)-->
  <script src="assets/plugins/global/plugins.bundle.js"></script>
  <script src="assets/js/scripts.bundle.js"></script>
  <!--end::Global Javascript Bundle-->
  <!--begin::Page Vendors Javascript(used by this page)-->
  <script src="assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
  <script src="assets/plugins/custom/datepicker/bootstrap-datepicker.js"></script>
  <!--end::Page Vendors Javascript-->
  <!--begin::Page Custom Javascript(used by this page)
  <script src="assets/js/custom/widgets.js"></script>-->
  <script>
    $(function() {
      window.prettyPrint && prettyPrint();
      //$.fn.datepicker.defaults.format = "dd/mm/YYYY";
      $('#date').datepicker({
        //format: 'yyyy-mm-dd',
        format: 'dd.mm.yyyy',
        todayBtn: 'linked',
        autoclose: true,
        todayBtn: false,
        todayHighlight: true,
        enableOnReadonly: true,
        startDate: "now()",
        daysOfWeekDisabled: [0]
      }).on('changeDate', function(ev) {
        var selected_date = moment(ev.date);

        var available_time = [];
        if (selected_date <= new Date())
          var hour = <?php echo $current_hour; ?>;
        else
          var hour = 9;
        if (hour < 9) hour = 9;
        available_time[0] = (hour + '').padStart(2, '0') + ":00";
        minute = 0;
        while (hour < 19) {
          minute = minute + <?php echo $intervall; ?>;
          if (minute < 60)
            available_time.push((hour + '').padStart(2, '0') + ":" + (minute + '').padStart(2, '0'));
          else {
            available_time.push(((hour + 1) + '').padStart(2, '0') + ":" + ((minute - 60) + '').padStart(2, '0'));
            minute = minute - 60;
            hour++;
          }
        }
        var booking_time = [];
        //console.log(available_time);
        document.getElementById("time").disabled = true;
        fetch('fetch_booking?date=' + selected_date.format('YYYY-MM-DD'), {
            method: 'get',
          }).then(function(response) {
            if (response.status >= 200 && response.status < 300) {
              return response.text()
            }
            throw new Error(response.statusText)
          })
          .then(function(response) {
            booking_time = response.split(",");
            //console.log(booking_time);
            available_time = available_time.filter(x => !booking_time.includes(x));
            var select2_data = "<option></option>";
            for (var i = 0; i < available_time.length; i++) {
              select2_data = select2_data + "<option value='" + available_time[i] + "'>" + available_time[i] + "</option>";
            }
            document.getElementById("time").innerHTML = select2_data;
            document.getElementById("time").disabled = false;
          })


      });
    });
    var KTWidgets = function() {
      // Calendar
      var initCalendarWidget1 = function() {
        if (typeof FullCalendar === 'undefined' || !document.querySelector('#kt_calendar_widget_1')) {
          return;
        }

        var booking_list = <?php echo json_encode($booking_list); ?>;
        for (var i = 0; i < booking_list.length; i++) {
          booking_list[i] = JSON.parse(booking_list[i]);
        }

        var todayDate = moment().startOf('day');
        var YM = todayDate.format('YYYY-MM');
        var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
        var TODAY = todayDate.format('YYYY-MM-DD');
        var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');

        var calendarEl = document.getElementById('kt_calendar_widget_1');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
          },
          locale: 'de',
          height: 800,
          contentHeight: 780,
          aspectRatio: 3, // see: https://fullcalendar.io/docs/aspectRatio

          nowIndicator: true,
          //now: TODAY + 'T09:25:00', // just for demo

          views: {
            dayGridMonth: {
              buttonText: 'Monat'
            },
            timeGridWeek: {
              buttonText: 'Woche'
            },
            timeGridDay: {
              buttonText: 'Tag'
            }
          },

          initialView: 'dayGridMonth',
          initialDate: TODAY,

          editable: true,
          dayMaxEvents: true, // allow "more" link when too many events
          navLinks: true,
          eventTimeFormat: { // like '14:30:00'
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            meridiem: true
          },
          events: booking_list,
          eventDidMount: function(info) { // If a description exists add as second line to title
            if (info.event.extendedProps.description != '' && typeof info.event.extendedProps.description !== "undefined") {
              $(info.el).find(".fc-event-title").append("<br/><b>" + info.event.extendedProps.description + "</b>");
              $(info.el).find(".fc-list-event-title").append(" - " + info.event.extendedProps.description);
            }
          },
        });

        calendar.render();
      }

      // Public methods
      return {
        init: function() {
          initCalendarWidget1();
        }
      }
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
      KTWidgets.init();
    });

    $('#time').on("select2:selecting", function(e) {});
    $(document).ready(function() {
      $('#time').select2({
        minimumResultsForSearch: -1,
        language: {
          noResults: function() {
            return 'Kein Termin mehr';
          },
        },
        escapeMarkup: function(markup) {
          return markup;
        },
      });
    });
  </script>
  <!--end::Page Custom Javascript-->
  <!--end::Javascript-->
</body>
<!--end::Body-->

</html>