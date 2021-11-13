<?php
    require_once 'config.php';
    if (!isset($_SESSION['userId']))
    {
        header('Location: login.php');
        return;
    }
?>
<html>
<head>
    <title>Messenger</title>
    <!-- Custom fonts for this template-->
    <link href="./common/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template-->
    <link href="./common/css/sb-admin-2.css" rel="stylesheet">
    <link href="./common/css/custom.css" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <?php
        include "./common/sliderbar.php";
    ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <!-- Main Content -->
        <div id="content">
            <?php
                include "./common/top.php";
            ?>
            <div class="container-fluid">
                <!-- <div class="col-12">
                    <div class="alert alert-danger" role="alert">
                        Error
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<?php
    include "./common/logout_modal.php";
?>

<div id="modal-dialog" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
</div>
<!-- Bootstrap core JavaScript-->
<script src="./common/vendor/jquery/jquery.min.js"></script>
<script src="./common/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Core plugin JavaScript-->
<script src="./common/vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Custom scripts for all pages-->
<script src="./common/js/sb-admin-2.min.js"></script>
<script src="./common/js/lib.js"></script>
<script src="./common/js/custom.js"></script>
</body>
</html>