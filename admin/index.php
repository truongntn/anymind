<?php
require_once("../config.php");
$conn = mysqli_connect($server, $username, $password, $database);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (isset($_COOKIE['email']) && isset($_COOKIE['admin'])) {
  header("Location: calendar");
}

$valid = true;
if (isset($_POST['kt_sign_in_submit'])) {
  $sql = "SELECT * FROM admin where admin_email='" . $_POST['email'] . "' and admin_password='" . md5($_POST['password']) . "'";
  if ($result = mysqli_query($conn, $sql)) {
    $valid = true;
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        setCookie("email", $row["admin_email"], time() + (48 * 60 * 60));
        setCookie("admin", $row["admin_id"], time() + (48 * 60 * 60));
      }
      header("Location: calendar");
    } else {
      $valid = false;
    }
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
  <link href="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
  <!--end::Page Vendor Stylesheets-->
  <!--begin::Global Stylesheets Bundle(used by all pages)-->
  <link href="../assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
  <link href="../assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
  <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" style="background-image: url(../assets/media/patterns/header-bg.png)" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled">
  <div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed auth-page-bg">
    <!--begin::Content-->
    <div class="d-flex  flex-column flex-column-fluid p-10 pb-lg-20 mt-20">
      <!--begin::Logo-->
      <span class="mb-12">
        <h2 class="h-15px h-lg-20px menu-title text-white text-center">
          Anwendung Center
        </h2>
      </span>
      <!--end::Logo-->

      <!--begin::Wrapper-->
      <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
        <!--begin::Form-->
        <form method="post" class="form w-100 fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate" id="kt_sign_in_form" data-kt-redirect-url="calendar" action="">
          <!--begin::Heading-->
          <div class="text-center mb-10">
            <!--begin::Title-->
            <h1 class="text-dark mb-3">Anmelden</h1>
            <!--end::Title-->
          </div>
          <!--begin::Heading-->
          <?php if ($valid === false) { ?>
            <!--begin::Alert-->
            <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
              <!--begin::Svg Icon | path: icons/duotune/general/gen048.svg-->
              <span class="svg-icon svg-icon-2hx svg-icon-danger me-4"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path>
                  <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="black"></path>
                </svg></span>
              <!--end::Svg Icon-->
              <div class="d-flex flex-column">
                <h4 class="mb-1 text-danger">Fehler</h4>
                <span>Falsche email oder passcode.</span>
              </div>
            </div>
            <!--end::Alert-->
          <?php } ?>
          <!--begin::Input group-->
          <div class="fv-row mb-10 fv-plugins-icon-container">
            <!--begin::Label-->
            <label class="form-label fs-6 fw-bold text-dark">Email</label>
            <!--end::Label-->

            <!--begin::Input-->
            <input class="form-control form-control-lg form-control-solid" type="text" name="email" autocomplete="off" />
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
            <input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="off" />
            <!--end::Input-->
            <div class="fv-plugins-message-container invalid-feedback"></div>
          </div>
          <!--end::Input group-->

          <!--begin::Actions-->
          <div class="text-center">
            <!--begin::Submit button-->
            <button type="submit" id="kt_sign_in_submit" name="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
              <span class="indicator-label"> Weiter </span>

              <span class="indicator-progress">
                Please wait...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
              </span>
            </button>
            <!--end::Submit button-->
          </div>
          <!--end::Actions-->
        </form>
        <!--end::Form-->
      </div>
      <!--end::Wrapper-->
    </div>
    <!--end::Content-->

    <!--begin::Footer-->
    <div class="d-flex flex-center flex-column-auto p-20">
      <!--begin::Links-->
      <div class="d-flex align-items-center fw-semibold fs-6">
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
  <script src="../assets/plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
  <!--end::Page Vendors Javascript-->
  <!--begin::Page Custom Javascript(used by this page)-->
  <script src="../assets/js/custom/widgets.js"></script>
  <!--end::Page Custom Javascript-->
  <!--end::Javascript-->
</body>
<!--end::Body-->

</html>