<?php
    require_once 'config.php';
    $querySlider= 'SELECT (SELECT COUNT(*) FROM TFRIEND WHERE DACCOUNTID = DACCOUNT.ID) AS COUNT_FRIEND,
    (SELECT COUNT(*) FROM TFRIENDREQUEST WHERE DACCOUNTID_RECEIVE = DACCOUNT.ID AND STATUS = 0) AS COUNT_REQUEST
    FROM DACCOUNT WHERE ID = ?';
    $paramSlider = array($_SESSION["userId"]);
    $stmtSlider = sqlsrv_query($conn, $querySlider, $paramSlider, array('Scrollable'=>'static'));
    $rowSlider = sqlsrv_fetch_array( $stmtSlider, SQLSRV_FETCH_ASSOC);
?>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./index.php">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">MESSENGER</div>
    </a>
    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="./list-user.php?type=users">
        <i class="fas fa-fw fa-search"></i>
        <span>Search</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="./list-user.php?type=friendreq">        
        <i class="fas fa-fw fa-sms"></i>
        <span>Friend Request <sup id='countRequest'><?php echo $rowSlider['COUNT_REQUEST'] ?></sup></span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">
    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="./list-user.php?type=friends">
        <i class="fas fa-fw fa-users"></i>
        <span>Friends <sup id='countFriend'><?php echo $rowSlider['COUNT_FRIEND'] ?></sup></span></a>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
</ul>
<!-- End of Sidebar -->