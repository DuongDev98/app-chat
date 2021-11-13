<?php
   require_once 'config.php';
   if (!isset($_SESSION['userId']))
   {
       header('Location: login.php');
       return;
   }
   $DACCOUNTID = isset($_POST['valId']) ? $_POST['valId'] : '';
   if (strlen($DACCOUNTID) == 0)
   {
       $error = "Tài khoản không tồn tại trong hệ thống!";
   }
   if ($DACCOUNTID != $_SESSION['userId'])
   {
        $error = "Bạn không có quyền truy cập chức năng này!";
   }
   else
   {
        //Thực hiện update thành công thì chuyển về profile
        $query = sqlsrv_query($conn, "SELECT * FROM DACCOUNT WHERE ID = ?", array($DACCOUNTID), array( "Scrollable" => 'static' ));
        $row_count = sqlsrv_num_rows($query);
        if ($row_count > 0) {
            $name = $_POST['valName'];
            $phone = $_POST['valPhone'];
            $email = $_POST['valEmail'];
            $address = $_POST['valAddress'];
            $dateOfBirth = $_POST['valDateOfBirth'];
            $query = "UPDATE DACCOUNT SET NAME = ?, PHONE = ?, EMAIL = ?, ADDRESS = ?, DATEOFBIRTH = ? WHERE ID = ?";
            $param = array($name, $phone, $email, $address, $dateOfBirth, $DACCOUNTID);
            $stmt = sqlsrv_query($conn, $query, $param);
            if ($stmt === FALSE)
            {
                $error = print_r( sqlsrv_errors(), true);
            }
            else
            {
                header('Location: profile.php?userId=' . $DACCOUNTID);
            }
        } else {
            $error = "Tài khoản không tồn tại trong hệ thống!";
        }
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
                <?php
                    if (isset($error)) {
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error ?>
                    </div>
                <?php
                    } else {
                ?>

                <?php
                    }
                ?>
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