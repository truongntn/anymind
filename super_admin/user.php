<?php
if (!isset($_COOKIE['super_admin'])) {
    header("Location: ./");
}

require_once("../config.php");
$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql_user = "select * from admin";

if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] === "delete") {
        $sql = "delete from admin where admin_id='" . $_GET["id"] . "'";
        if ($result = mysqli_query($conn, $sql)) {
            header("Location: user");
        } else
            echo 'Failed.' . mysqli_error($conn);
        mysqli_close($conn);
    }
}

function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

if (isset($_POST['kt_new_admin_submit'])) {
    $sql = "insert into admin value (null,'" . $_POST["email"] . "','" .  md5($_POST["password"]) . "','','','','" . gen_uuid() . "','60','1')";
    if ($result = mysqli_query($conn, $sql)) {
        header("Location: user");
    } else
        echo 'Failed.' . mysqli_error($conn);
    mysqli_close($conn);
}

if (isset($_POST['kt_edit_admin_submit'])) {
    if ($_POST["password_edit"] !== '')
        $sql = "update admin set admin_email ='" . $_POST["email_edit"] . "', admin_password='" . md5($_POST["password_edit"]) . "' where admin_id='" . $_POST["admin_edit"] . "'";
    else
        $sql = "update admin set admin_email ='" . $_POST["email_edit"]  . "' where admin_id='" . $_POST["admin_edit"] . "'";
    if ($result = mysqli_query($conn, $sql)) {
        header("Location: user");
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <!--end::Page Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <style>
        table.dataTable tbody td {
            vertical-align: middle;
        }

        table.dataTable tbody tr:hover {
            background-color: #faf1c6 !important;
            cursor: pointer;
        }
    </style>
    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" style="background-image: url(../assets/media/patterns/header-bg.png)" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled">
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
                                        <div class="d-flex align-items-center ms-1 ms-lg-3" id="kt_header_admin_menu_toggle">
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
                                                                <?php echo $_COOKIE["super_admin"]; ?>
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
                            <h3 class="text-white fw-bolder me-5">Kunden Verwaltung</h3>
                            <!--begin::Title-->
                            <!--begin::Actions-->
                            <div class="d-flex align-items-center flex-wrap py-2">
                                <!--begin::Button-->
                                <a href="#" class="btn btn-success my-2" tooltip="Neue Kunde" data-bs-toggle="modal" data-bs-target="#kt_modal_new_user">Neue Kunde</a>
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
                                            <table id="table_user" class="table table-striped hover" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="px-3" style="font-weight:bold">Email</th>
                                                        <th class="px-3" style="font-weight:bold">Passcode</th>
                                                        <th class="px-3" style="font-weight:bold">Reference code</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($result = mysqli_query($conn, $sql_user)) {
                                                        if ($result && $result->num_rows > 0) {
                                                            while ($row = $result->fetch_assoc()) {
                                                    ?>
                                                                <tr>
                                                                    <td class="px-3"><?php echo $row["admin_email"]; ?></td>
                                                                    <td class="px-3"><?php echo $row["admin_password"]; ?></td>
                                                                    <td class="px-3"><?php echo $row["reference_code"]; ?></td>
                                                                    <td class="pe-0 text-center">
                                                                        <a href="#" class="btn btn-sm btn-icon btn-color-gray-500 btn-active-color-primary" data-kt-menu-trigger="click" data-kt-menu-overflow="true" data-kt-menu-placement="bottom-start">
                                                                            <!--begin::Svg Icon | path: icons/duotune/general/gen023.svg-->
                                                                            <span class="svg-icon svg-icon-2x">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                                    <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="4" fill="black"></rect>
                                                                                    <rect x="11" y="11" width="2.6" height="2.6" rx="1.3" fill="black"></rect>
                                                                                    <rect x="15" y="11" width="2.6" height="2.6" rx="1.3" fill="black"></rect>
                                                                                    <rect x="7" y="11" width="2.6" height="2.6" rx="1.3" fill="black"></rect>
                                                                                </svg>
                                                                            </span>
                                                                            <!--end::Svg Icon-->
                                                                        </a>
                                                                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true" style="">
                                                                            <!--begin::Heading-->
                                                                            <div class="menu-item px-3">
                                                                                <div class="menu-content text-muted pb-2 px-3 fs-7">Actions</div>
                                                                            </div>
                                                                            <!--end::Heading-->
                                                                            <!--begin::Menu item-->
                                                                            <div class="menu-item px-3">
                                                                                <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_edit_user" onclick="javascript:editUser('<?php echo $row['admin_id']; ?>','<?php echo $row['admin_email']; ?>','<?php echo $row['admin_password']; ?>')">Edit user</a>
                                                                            </div>
                                                                            <!--end::Menu item-->
                                                                            <!--begin::Menu item-->
                                                                            <div class="menu-item px-3">
                                                                                <a href="?action=delete&id=<?php echo $row["admin_id"]; ?>" class="menu-link flex-stack px-3">Delete user
                                                                                    <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" title="" data-bs-original-title="All bookings of this user will be deleted" aria-label="All bookings of this user will be deleted"></i></a>
                                                                            </div>
                                                                            <!--end::Menu item-->
                                                                        </div>
                                                                    </td>
                                                                </tr>

                                                    <?php
                                                            }
                                                        }
                                                    } ?>

                                                </tbody>
                                            </table>
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

        <!--begin::Modal - New User-->
        <div class="modal fade" id="kt_modal_new_user" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2>Neue Kunde</h2>
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
                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="form-label fs-6 fw-bold text-dark">Email</label>
                            <!--end::Label-->

                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="text" id="email" name="email" autocomplete="off" />
                            <!--end::Input-->
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack mb-2">
                                <!--begin::Label-->
                                <label class="form-label fw-bold text-dark fs-6 mb-0">Passcode</label>
                                <!--end::Label-->
                            </div>
                            <!--end::Wrapper-->

                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="text" id="password" name="password" autocomplete="off" />
                            <!--end::Input-->
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Wrapper-->
                        <div class="mt-5">
                            <button type="button" onclick="javascript:generatePassword()" class="btn btn-lg btn-primary my-2" data-kt-stepper-action="submit">
                                <span class="indicator-label">Random Password

                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <button type="submit" id="kt_new_admin_submit" name="kt_new_admin_submit" class="btn btn-lg btn-primary my-2" data-kt-stepper-action="submit">
                                <span class="indicator-label">Speichern

                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - New User-->

        <!--begin::Modal - Edit User-->
        <div class="modal fade" id="kt_modal_edit_user" tabindex="-1" aria-hidden="true">
            <!--begin::Modal dialog-->
            <div class="modal-dialog modal-dialog-centered">
                <!--begin::Modal content-->
                <div class="modal-content">
                    <!--begin::Modal header-->
                    <div class="modal-header">
                        <!--begin::Modal title-->
                        <h2>Edit User</h2>
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
                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Label-->
                            <label class="form-label fs-6 fw-bold text-dark">Email</label>
                            <!--end::Label-->

                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="hidden" id="admin_edit" name="admin_edit" autocomplete="off" />
                            <input class="form-control form-control-lg form-control-solid" type="text" id="email_edit" name="email_edit" autocomplete="off" />
                            <!--end::Input-->
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Input group-->
                        <div class="fv-row mb-10 fv-plugins-icon-container">
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack mb-2">
                                <!--begin::Label-->
                                <label class="form-label fw-bold text-dark fs-6 mb-0">Passcode</label>
                                <!--end::Label-->
                            </div>
                            <!--end::Wrapper-->

                            <!--begin::Input-->
                            <input class="form-control form-control-lg form-control-solid" type="text" id="password_edit" name="password_edit" autocomplete="off" />
                            <!--end::Input-->
                            <div class="fv-plugins-message-container invalid-feedback"></div>
                        </div>
                        <!--end::Input group-->
                        <!--begin::Wrapper-->
                        <div class="mt-5">
                            <button type="button" onclick="javascript:generatePasswordEdit()" class="btn btn-lg btn-primary my-2" data-kt-stepper-action="submit">
                                <span class="indicator-label">Random Password

                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <button type="submit" id="kt_edit_admin_submit" name="kt_edit_admin_submit" class="btn btn-lg btn-primary my-2" data-kt-stepper-action="submit">
                                <span class="indicator-label">Speichern

                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Modal body-->
                </div>
                <!--end::Modal content-->
            </div>
            <!--end::Modal dialog-->
        </div>
        <!--end::Modal - Edit User-->

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
        <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js"></script>
        <!--end::Page Vendors Javascript-->
        <!--begin::Page Custom Javascript(used by this page)
  <script src="../assets/js/custom/widgets.js"></script>-->
        <script>
            $(document).ready(function() {
                $('#table_user').DataTable({
                    language: {
                        url: '../assets/dataTables.german.json'
                    }
                });
            });

            function getRandomArbitrary(min, max) {
                return Math.floor(Math.random() * (max - min) + min);
            }

            function generatePassword() {
                document.getElementById("password").value = getRandomArbitrary(1000, 9999);
            }

            function generatePasswordEdit() {
                document.getElementById("password_edit").value = getRandomArbitrary(1000, 9999);
            }

            function editUser(id, email, password) {
                document.getElementById("admin_edit").value = id;
                document.getElementById("email_edit").value = email;
                //document.getElementById("password_edit").value = password;
            }
        </script>
        <!--end::Page Custom Javascript-->
        <!--end::Javascript-->
    </form>
    <!--end::Form-->
</body>
<!--end::Body-->

</html>