<?php
if (!isset($_COOKIE['email']) || !isset($_COOKIE['admin'])) {
    header("Location: ./");
}

require_once("../config.php");
$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

require_once("GoogleCalendarApi.php");
$capi = new GoogleCalendarApi();

$calendar_list = [];
$calendar_id = "";
$intervall = 60;
$nomitarbeiter = 1;
try {
    $sql = "SELECT * FROM admin where admin_id='" . $_COOKIE['admin'] . "'";
    if ($result = mysqli_query($conn, $sql)) {
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['access_token'] != "") {
                    $calendar_list = $capi->GetCalendarsList($row["access_token"]);
                    $calendar_id = $row["calendar_id"];
                    $intervall = $row["intervall"];
                    $nomitarbeiter = $row["nomitarbeiter"];
                }
            }
        }
    }
} catch (Throwable $t) {
    header("Location: setting");
}

if (isset($_POST['kt_setting_submit'])) {
    $sql = "update admin set calendar_id ='" . $_POST["calendar"] . "',intervall='" . $_POST["intervall"] . "',nomitarbeiter='" . $_POST["nomitarbeiter"] . "' where admin_id='" . $_COOKIE["admin"] . "'";
    if ($result = mysqli_query($conn, $sql)) {
        header("Location: setting");
    } else
        echo 'Failed.' . mysqli_error($conn);
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
    <link rel="shortcut icon" href="../assets/media/logos/favicon.ico" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Vendor Stylesheets(used by this page)-->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" style="background-image: url(../assets/media/patterns/header-bg.png)" class="toolbar-enabled">
    <!--begin::Form-->
    <form method="post" class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="calendar" action="">

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
                                                                <?php echo $_COOKIE["email"]; ?>
                                                            </div>
                                                            <!--<a href="#" class="fw-bold text-muted text-hover-primary fs-7">admin@domain.com</a>-->
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
                                                    <a href="calendar" class="menu-link px-5">Meine Termine</a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-5">
                                                    <a href="booking" class="menu-link px-5">Termin Verwaltung</a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-5">
                                                    <a href="user" class="menu-link px-5">Kunden Verwaltung</a>
                                                </div>
                                                <!--end::Menu item-->
                                                <!--begin::Menu item-->
                                                <div class="menu-item px-5">
                                                    <a href="setting" class="menu-link px-5">Einstellungen</a>
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
                            <h3 class="text-white fw-bolder me-5">Einstellungen</h3>
                            <!--begin::Title-->
                            <!--begin::Actions-->
                            <div class="d-flex align-items-center flex-wrap py-2">
                                <!--begin::Button-->
                                <button type="submit" id="kt_setting_submit" name="kt_setting_submit" class="btn btn-lg btn-success my-2" data-kt-stepper-action="submit">
                                    <span class="indicator-label">Speichern</span>
                                </button>
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
                                    <form method="post" class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" id="kt_setting_form" action="">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <!--begin::Input group-->
                                                <div class="fv-row mb-10 fv-plugins-icon-container">
                                                    <!--begin::Label-->
                                                    <label class="form-label fs-6 fw-bold text-dark">Kalender</label>
                                                    <!--end::Label-->

                                                    <select class="form-select form-select-sm" id="calendar" name="calendar" data-control="select2" data-hide-search="true" data-placeholder="Bitte Kalender auswahlen">
                                                        <option></option>
                                                        <?php
                                                        foreach ($calendar_list as $calendar) { ?>
                                                            <?php if ($calendar_id == $calendar["id"]) { ?>
                                                                <option selected value="<?php echo $calendar["id"] ?>"><?php echo $calendar["summary"] ?></option>
                                                            <?php } else { ?>
                                                                <option value="<?php echo $calendar["id"] ?>"><?php echo $calendar["summary"] ?></option>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                            </div>
                                            <div class="col-md-4">
                                                <!--begin::Input group-->
                                                <div class="fv-row mb-10 fv-plugins-icon-container">
                                                    <!--begin::Label-->
                                                    <label class="form-label fs-6 fw-bold text-dark">Intervall</label>
                                                    <!--end::Label-->
                                                    <select class="form-select form-select-sm" id="intervall" name="intervall" data-control="select2" data-hide-search="true" data-placeholder="Intervall">
                                                        <option <?php echo $intervall === "10" || $intervall === 10 ? "selected" : "" ?> value="10">10</option>
                                                        <option <?php echo $intervall === "20" || $intervall === 20 ? "selected" : "" ?> value="20">20</option>
                                                        <option <?php echo $intervall === "30" || $intervall === 30 ? "selected" : "" ?> value="30">30</option>
                                                        <option <?php echo $intervall === "40" || $intervall === 40 ? "selected" : "" ?> value="40">40</option>
                                                        <option <?php echo $intervall === "50" || $intervall === 50 ? "selected" : "" ?> value="50">50</option>
                                                        <option <?php echo $intervall === "60" || $intervall === 60 ? "selected" : "" ?> value="60">60</option>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                            </div>
                                            <div class="col-md-4">
                                                <!--begin::Input group-->
                                                <div class="fv-row mb-10 fv-plugins-icon-container">
                                                    <!--begin::Label-->
                                                    <label class="form-label fs-6 fw-bold text-dark">NoMitarbeiter</label>
                                                    <!--end::Label-->

                                                    <select class="form-select form-select-sm" id="nomitarbeiter" name="nomitarbeiter" data-control="select2" data-hide-search="true" data-placeholder="NoMitarbeiter">
                                                        <option <?php echo $nomitarbeiter === "1" || $nomitarbeiter === 1 ? "selected" : "" ?> value="1">1</option>
                                                        <option <?php echo $nomitarbeiter === "2" || $nomitarbeiter === 2 ? "selected" : "" ?> value="2">2</option>
                                                        <option <?php echo $nomitarbeiter === "3" || $nomitarbeiter === 3 ? "selected" : "" ?> value="3">3</option>
                                                        <option <?php echo $nomitarbeiter === "4" || $nomitarbeiter === 4 ? "selected" : "" ?> value="4">4</option>
                                                        <option <?php echo $nomitarbeiter === "5" || $nomitarbeiter === 5 ? "selected" : "" ?> value="5">5</option>
                                                        <option <?php echo $nomitarbeiter === "6" || $nomitarbeiter === 6 ? "selected" : "" ?> value="6">6</option>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Index-->
                        </div>
                        <!--end::Post-->
                    </div>
                    <!--end::Container-->
                    <!--begin::Footer-->
                    <div class="d-flex flex-center flex-column-auto p-10">
                        <!--begin::Links-->
                        <div class="d-flex align-items-center  fw-normal fs-6">
                            <span class="text-gray-800 text-hover-primary">
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                © Anwendung Center
                            </span>
                            <!--end::Links-->
                        </div>
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Page-->
        </div>
        <!--end::Root-->

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
        <script src="../assets/plugins/global/plugins.bundle.js"></script>
        <script src="../assets/js/scripts.bundle.js"></script>
        <!--end::Global Javascript Bundle-->
        <!--begin::Page Vendors Javascript(used by this page)-->
        <script>
            $('#calendar').on("select2:selecting", function(e) {});
        </script>
        <!--end::Page Vendors Javascript-->
        <!--begin::Page Custom Javascript(used by this page)
  <script src="../assets/js/custom/widgets.js"></script>-->
        <!--end::Page Custom Javascript-->
        <!--end::Javascript-->
    </form>
    <!--end::Form-->
</body>
<!--end::Body-->

</html>