<?php
    require_once 'config.php';
    $queryTop= 'SELECT ID, NAME, AVATAR FROM DACCOUNT WHERE ID = ?';
    $paramTop = array($_SESSION["userId"]);
    $stmtTop = sqlsrv_query($conn, $queryTop, $paramTop, array('Scrollable'=>'static'));
    $rowTop = sqlsrv_fetch_array( $stmtTop, SQLSRV_FETCH_ASSOC);
    $avatarTop = $rowTop['AVATAR'];
    $nameTop = $rowTop['NAME'];
    //Đếm và lấy ra số thông báo
    $queryTop = 'SELECT COUNT(*) AS COUNT FROM TNOTIFICATION WHERE DACCOUNTID_RECEIVE = ? AND COALESCE(WATCHED, 0) = 0';
    $stmtTop = sqlsrv_query($conn, $queryTop, $paramTop, array('Scrollable'=>'static'));
    $rowTop = sqlsrv_fetch_array( $stmtTop, SQLSRV_FETCH_ASSOC);
    $countNotification = $rowTop['COUNT'];
    //Đếm và lấy ra số tin nhắn
    $queryTop = 'SELECT COUNT(DISTINCT DACCOUNTID_SEND) AS COUNT FROM TMESSAGE WHERE DACCOUNTID_RECEIVE = ? AND COALESCE(WATCHED, 0) = 0';
    $stmtTop = sqlsrv_query($conn, $queryTop, $paramTop, array('Scrollable'=>'static'));
    $rowTop = sqlsrv_fetch_array( $stmtTop, SQLSRV_FETCH_ASSOC);
    $countMessage = $rowTop['COUNT'];

    //Lấy danh top 5 tin nhắn
    $paramTop = array($_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"]);
    $queryTop = "SELECT TOP 5 FORMAT(A.TIMECREATED, 'HH:mm:ss dd/MM/yy') AS TIMECREATED, A.DACCOUNTID_SEND, A.AVATAR,
    A.NAME, A.WATCHED, A.MESSAGE FROM (
    SELECT ID AS DACCOUNTID_SEND, AVATAR,  DACCOUNT.NAME,
    (SELECT TOP 1 COALESCE(WATCHED,0) FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS WATCHED,
    (SELECT TOP 1 TMESSAGE.TIMECREATED FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS TIMECREATED,
    (SELECT TOP 1 MESSAGE FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS MESSAGE
    FROM DACCOUNT WHERE ID <> ? AND EXISTS (SELECT * FROM TMESSAGE WHERE (DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_RECEIVE = DACCOUNT.ID AND DACCOUNTID_SEND = ?))
    )A ORDER BY A.TIMECREATED DESC";
    $stmtMessage = sqlsrv_query($conn, $queryTop, $paramTop, array('Scrollable'=>'static'));
    //Lấy danh top 5 thông báo
    $queryTop = "SELECT TOP 5 TNOTIFICATION.ID, DACCOUNTID_SEND, COALESCE(WATCHED, 0) AS WATCHED, FORMAT(TNOTIFICATION.TIMECREATED, 'HH:mm:ss dd/MM/yy') AS TIMECREATED, AVATAR, MESSAGE
    FROM TNOTIFICATION INNER JOIN DACCOUNT ON DACCOUNTID_SEND = DACCOUNT.ID
    WHERE DACCOUNTID_RECEIVE = ? ORDER BY TNOTIFICATION.TIMECREATED DESC";
    $stmtNotification = sqlsrv_query($conn, $queryTop, $paramTop, array('Scrollable'=>'static'));
?>
<!-- Topbar -->
<div id="accountId" hidden='hidden' data-account='<?php echo $_SESSION['userId'] ?>'></div>

<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                 aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small"
                               placeholder="Search for..." aria-label="Search"
                               aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter" id='countNotification'><?php echo $countNotification; ?></span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>
                <div id='notificationCenter'>
                    <?php
                        while ($rowTop = sqlsrv_fetch_array($stmtNotification, SQLSRV_FETCH_ASSOC))
                        {
                            ?>
                            <a class="dropdown-item d-flex align-items-center" href="./profile.php?userId=<?php echo $rowTop['DACCOUNTID_SEND']; ?>&readed=<?php echo $rowTop['ID']; ?>">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="./uploads/<?php if (isset($rowTop['AVATAR'])) echo $rowTop['AVATAR']; else echo 'no-image.png'; ?>" alt="">
                                    <!-- <div class="status-indicator bg-success"></div> -->
                                </div>
                                <div>
                                    <div class="small text-gray-500"><?php echo $rowTop['TIMECREATED']; ?></div>
                                    <?php
                                        if ($rowTop['WATCHED'] == 0)
                                        {
                                            ?>
                                                <span class="font-weight-bold">
                                            <?php
                                        }
                                        echo $rowTop['MESSAGE'];
                                        if ($rowTop['WATCHED'] == 30)
                                        {
                                            ?>
                                                </span>
                                            <?php
                                        }
                                    ?>
                                </div>
                            </a>
                            <?php
                        }
                    ?>
                </div>
                <a class="dropdown-item text-center small text-gray-500" href="./notification.php">Show All Alerts</a>
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-danger badge-counter" id='countMessage'><?php echo $countMessage; ?></span>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <div id='messageCenter'>
                    <?php
                        while ($rowTop = sqlsrv_fetch_array($stmtMessage, SQLSRV_FETCH_ASSOC))
                        {
                            ?>
                            <a class="dropdown-item d-flex align-items-center" href="./message.php?userId=<?php echo $rowTop['DACCOUNTID_SEND']; ?>">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="./uploads/<?php if (isset($rowTop['AVATAR'])) echo $rowTop['AVATAR']; else echo 'no-image.png'; ?>" alt="">
                                    <!-- <div class="status-indicator bg-success"></div> -->
                                </div>
                                <div>
                                    <?php
                                        echo '<b>' . $rowTop['NAME'] . '<br>' . '</b>';
                                        if ($rowTop['WATCHED'] == 0)
                                        {
                                            ?>
                                                <span class="font-weight-bold">
                                            <?php
                                        }
                                        echo $rowTop['MESSAGE'];
                                        if ($rowTop['WATCHED'] == 30)
                                        {
                                            ?>
                                                </span>
                                            <?php
                                        }
                                    ?>
                                    <div class="small text-gray-500"><?php echo $rowTop['TIMECREATED']; ?></div>
                                </div>
                            </a>
                            <?php
                        }
                    ?>
                </div>
                <a class="dropdown-item text-center small text-gray-500" href="./message.php">Read More Messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $nameTop ?></span>
                <img class="img-profile rounded-circle" src="./uploads/<?php if (isset($avatarTop)) echo $avatarTop; else echo 'no-image.png'; ?>" alt="IMG-AVATAR"/>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                 aria-labelledby="userDropdown">
                <a class="dropdown-item" href="./profile.php?userId=<?php echo $_SESSION['userId'] ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
<!-- End of Topbar -->