<?php
    require_once 'config.php';
    if (!isset($_SESSION['userId']))
    {
        header('Location: login.php');
        return;
    }
    $type = 'users';
    if (isset($_REQUEST['type']))
    {
        $type = $_REQUEST['type'];
        if (strlen($type) === 0 || ($type != 'users' && $type != 'friends' && $type != 'friendreq'))
        {
            $error = "Bạn không có quyền truy cập dữ liệu này!";
        }
    }

    if (!isset($error))
    {
        $param = array($_SESSION['userId'], $_SESSION['userId'], $_SESSION['userId'], $_SESSION['userId'], $_SESSION['userId']);
        $query = 'SELECT ID, NAME, EMAIL, PHONE, DATEOFBIRTH, AVATAR, ADDRESS, TIMECREATED,';
        $query = $query . " (SELECT COUNT(*) FROM TFRIEND T1 WHERE T1.DACCOUNTID = D1.ID";
        $query = $query . ' AND EXISTS (SELECT * FROM TFRIEND T2 WHERE T2.DFRIENDID = T1.DFRIENDID AND T2.DACCOUNTID = ?)) AS NUM_MUTUAL_FRIENDS,';
        $query = $query . ' (SELECT COUNT(*) FROM TFRIENDREQUEST WHERE D1.ID = DACCOUNTID_RECEIVE AND DACCOUNTID_SEND = ?) AS ISSEND';
        $query = $query . ' FROM DACCOUNT D1';

        if ($type === 'users')
        {
            //Danh sách người dùng không phải bạn bè và không có trong danh sách đã gửi kết bạn cho mình
            $query = $query . ' WHERE NOT EXISTS (SELECT * FROM TFRIEND WHERE TFRIEND.DFRIENDID = D1.ID AND DACCOUNTID = ?) AND D1.ID <> ?';
            $query = $query . ' AND NOT EXISTS (SELECT * FROM TFRIENDREQUEST WHERE COALESCE(STATUS, 0) = 0 AND D1.ID = DACCOUNTID_SEND AND DACCOUNTID_RECEIVE = ?)';
        }
        else if ($type === 'friends')
        {
            //Danh sách người dùng là bạn bè
            $query = $query . ' WHERE EXISTS (SELECT * FROM TFRIEND WHERE TFRIEND.DFRIENDID = D1.ID AND DACCOUNTID = ?)';
        }
        else {
            //Danh sách người dùng đang gửi lời mời kết bạn
            $query = $query . ' WHERE EXISTS (SELECT * FROM TFRIENDREQUEST WHERE TFRIENDREQUEST.DACCOUNTID_RECEIVE = ? AND D1.ID = TFRIENDREQUEST.DACCOUNTID_SEND AND COALESCE(TFRIENDREQUEST.STATUS, 0) = 0)';
        }
        $query = $query . ' ORDER BY D1.NAME';
    }
    $stmt  = sqlsrv_query($conn, $query, $param, array( "Scrollable" => 'static' ));
    print_r(sqlsrv_errors());
    if (0 === sqlsrv_num_rows($stmt))
    {
        $error = 'Danh sách người dùng trống!';
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
                <?php if (isset($error)) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error ?>
                    </div>
                <?php
                    } else {
                ?>
                <!-- không lỗi thì hiển thị giao diện -->
                <div class="row">
                    <div class="col-md-12">
                        <?php
                            while($rowProfile = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                ?>
                                <div class="people-nearby" data-id="<?php echo $rowProfile['ID'] ?>">
                                    <div class="nearby-user">
                                        <div class="row">
                                            <div class="col-2">
                                            <img src="./uploads/<?php if (isset($rowProfile['AVATAR'])) echo $rowProfile['AVATAR']; else echo 'no-image.png'; ?>" alt="user" class="profile-photo-lg">
                                            </div>
                                            <div class="col-7">
                                                <h5><a href="./profile.php?userId=<?php echo $rowProfile['ID'] ?>" class="profile-link"><?php echo $rowProfile['NAME'] ?></a></h5>
                                                <p><?php echo $rowProfile['NUM_MUTUAL_FRIENDS'] ?> bạn chung</p>
                                            </div>
                                            <div class="col-3">
                                                <div class="row">
                                                <!-- users, friends, friendreq -->
                                                    <?php
                                                        if ($type === 'friends')
                                                        {
                                                            ?>
                                                                <div style="margin-left: 5px;">
                                                                <a href='./message.php?userId=<?php echo $rowProfile['ID']; ?>'>
                                                                    <button data-id='<?php echo $rowProfile['ID']; ?>' class="btn btn-info pull-right message-f" title="Gửi tin nhắn"><i class="fa fa-inbox" aria-hidden="true"></i></button>
                                                                </a>
                                                                </div>
                                                                <div style="margin-left: 5px;">
                                                                    <button data-id='<?php echo $rowProfile['ID']; ?>' class="btn btn-danger pull-right remove-f" title="Hủy kết bạn"><i class="fa fa-user-times" aria-hidden="true"></i></button>
                                                                </div>
                                                            <?php
                                                        }
                                                        if ($type === 'users')
                                                        {
                                                            ?>
                                                                <div style="margin-left: 5px;">
                                                                    <button <?php if ($rowProfile['ISSEND'] != 0) echo 'hidden'; ?> class="btn btn-success pull-right addFriend" data-id='<?php echo $rowProfile['ID'] ?>' title="Thêm bạn"><i class="fa fa-user-plus" aria-hidden="true"></i></button>
                                                                    <button <?php if ($rowProfile['ISSEND'] == 0) echo 'hidden'; ?> class="btn btn-info pull-right waitAddFriend" data-id='<?php echo $rowProfile['ID'] ?>' title="Chờ xác nhận">Wait for confirmation</button>
                                                                </div>
                                                            <?php
                                                        }
                                                        if ($type === 'friendreq')
                                                        {
                                                            ?>
                                                                <div style="margin-left: 5px;">
                                                                    <button data-id='<?php echo $rowProfile['ID']; ?>' class="btn btn-success pull-right confirm-f" title="Chấp nhận"><i class="fa fa-check" aria-hidden="true"></i></button>
                                                                </div>

                                                                <div style="margin-left: 5px;">
                                                                    <button data-id='<?php echo $rowProfile['ID']; ?>' class="btn btn-danger pull-right cancel-confirm-f" title="Hủy bỏ"><i class="fa fa-times" aria-hidden="true"></i></button>
                                                                </div>
                                                            <?php
                                                        }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                </div>
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