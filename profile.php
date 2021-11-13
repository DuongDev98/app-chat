<?php
    require_once 'config.php';
    if (!isset($_SESSION['userId']))
    {
        header('Location: login.php');
        return;
    }
    //cập nhật
    if (isset($_REQUEST['readed'])) {
        $reader = $_REQUEST['readed'];
        if (strlen($reader) > 0)
        {
            //cập nhật thông báo là đã đọc
            $query = "UPDATE TNOTIFICATION SET WATCHED = 30 WHERE ID = ?";
            $stmt = sqlsrv_query($conn, $query, array($reader));
        }
    }
    
    $DACCOUNTID = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
    if (strlen($DACCOUNTID) == 0)
    {
        $error = "Tài khoản không tồn tại trong hệ thống!";
    }
    $itsme = FALSE;
    $isFriend = FALSE;
    $isSend = FALSE;
    $hasSend = FALSE;
    if ($DACCOUNTID == $_SESSION['userId'])
    {
        $itsme = TRUE;
    }
    else
    {
        $query = "SELECT * FROM TFRIEND WHERE DACCOUNTID = ? AND DFRIENDID = ?";
        $stmt = sqlsrv_query($conn, $query, array($_SESSION['userId'], $DACCOUNTID), array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
        if ($row != NULL)
        {
            $isFriend = TRUE;
        }
    }
    //Lấy thông tin từ database
    $query = "SELECT DACCOUNT.*,  (SELECT COUNT(*) FROM TFRIENDREQUEST WHERE DACCOUNT.ID = DACCOUNTID_RECEIVE AND DACCOUNTID_SEND = ?) AS ISSEND, 
    (SELECT COUNT(*) FROM TFRIENDREQUEST WHERE DACCOUNT.ID = DACCOUNTID_SEND AND DACCOUNTID_RECEIVE = ?) AS HASSEND FROM DACCOUNT WHERE ID = ?";
    $param = array($_SESSION['userId'], $_SESSION['userId'], $DACCOUNTID);
    $stmt = sqlsrv_query($conn, $query, $param , array('Scrollable'=>'static'));
    $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
    if ($row === NULL)
    {
        $error = "Tài khoản không tồn tại trong hệ thống!";
    }
    else
    {
        $isSend = isset($row['ISSEND']) && $row['ISSEND'] > 0;
        $hasSend = isset($row['HASSEND']) && $row['HASSEND'] > 0;
        $username = $row['USERNAME'];
        $nameProfile = $row['NAME'];
        $phone = $row['PHONE'];
        $email = $row['EMAIL'];
        $address = $row['ADDRESS'];
        $dateOfBirth = $row['DATEOFBIRTH'] == NULL ? '' : $row['DATEOFBIRTH']->format('d/m/Y');
        $avatar = $row['AVATAR'];
        $timeCreated = $row['TIMECREATED']->format('d/m/Y');
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
                <!-- form-edit -->
                <div class="container emp-profile">
                    <form id='formprofile' method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="profile-img">
                                    <img src="./uploads/<?php if (isset($avatar)) echo $avatar; else echo 'no-image.png'; ?>" alt="IMG-AVATAR"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-head">
                                    <h5 id="ipName"><?php echo $nameProfile; ?></h5>
                                    <?php
                                        if (!$itsme)
                                        {
                                            if ($isFriend)
                                            {
                                    ?>
                                                <div class='row'>
                                                    <div style="margin-left: 5px;">
                                                                    <a href='./message.php?userId=<?php echo $DACCOUNTID ?>'>
                                                                    <div data-id='<?php echo $DACCOUNTID ?>' class="btn btn-success pull-right message-f" title="Chấp nhận">Message</div>
                                                                    </a>
                                                                </div>

                                                                <div style="margin-left: 5px;">
                                                                    <div data-id='<?php echo $DACCOUNTID ?>' class="btn btn-danger pull-right remove-f" title="Hủy bỏ">Remove Friend</i></div>
                                                                </div>
                                                </div>
                                    <?php
                                            }
                                            else
                                            {
                                                if ($isSend)
                                                {
                                                    ?>
                                                    <div class="btn btn-info" style="width: 200px;">Wait for confirmation</div>
                                                    <?php
                                                }
                                                else
                                                {
                                                    if ($hasSend)
                                                    {
                                                        ?>
                                                        <div class="row">
                                                                <div style="margin-left: 5px;">
                                                                    <div data-id='<?php echo $DACCOUNTID ?>' class="btn btn-success pull-right confirm-f" title="Chấp nhận">Confirm</div>
                                                                </div>

                                                                <div style="margin-left: 5px;">
                                                                    <div data-id='<?php echo $DACCOUNTID ?>' class="btn btn-danger pull-right cancel-confirm-f" title="Hủy bỏ">Remove</i></div>
                                                                </div>
                                                        </div>
                                                        <?php
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                        <div data-id='<?php echo $DACCOUNTID ?>' class="btn btn-success addFriend" style="width: 200px;">Add friend</div>
                                                        <?php
                                                    }
                                                }
                                            }
                                        }
                                    ?>
                                </div>

                                <hr/>

                                <div class="tab-content profile-tab" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Username</label>
                                            </div>
                                            <div class="col-md-6">
                                                <p id="ipId" hidden='hidden'><?php echo $DACCOUNTID; ?></p>
                                                <p id="ipUserName"><?php echo $username; ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Phone</label>
                                            </div>
                                            <div class="col-md-6">
                                                <p id="ipPhone"><?php echo $phone; ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Email</label>
                                            </div>
                                            <div class="col-md-6">
                                                <p id="ipEmail"><?php echo $email; ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Address</label>
                                            </div>
                                            <div class="col-md-6">
                                                <p id="ipAddress"><?php echo $address; ?></p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Date of birth</label>
                                            </div>
                                            <div class="col-md-6">
                                                <p id="ipDateOfBirth"><?php echo $dateOfBirth; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php
                                if ($itsme)
                                {
                            ?>
                                <div class="col-md-2">
                                    <div id="btnEditProfile" class="btn btn-warning">Edit profile</div>
                                </div>
                            <?php
                                }
                            ?>
                        </div>
                        <br/>
                        <div class="row">
                            <div class="col-md-4 profile-img">
                                    <?php
                                        if ($itsme)
                                        {
                                    ?>
                                        <div class="file btn btn-lg btn-primary">
                                            Change Photo
                                            <input type="file" name="file" id="btnFileInfo"/>
                                        </div>
                                    <?php
                                        }
                                    ?>
                                    <div class="profile-work">
                                        <p>Started: <?php echo $timeCreated; ?></p>
                                    </div>
                                </div>
                        </div>
                    </form>
                </div>
                <!-- end form -->
                <?php } ?>
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