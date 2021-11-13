<?php
    require_once 'config.php';
    if (!isset($_SESSION['userId']))
    {
        header('Location: login.php');
        return;
    }
	$query = "SELECT AVATAR, NAME FROM DACCOUNT WHERE ID = ?";
	$param = array($_SESSION['userId']);
	$stmt = sqlsrv_query($conn, $query, $param , array('Scrollable'=>'static'));
	$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
	$avartar = $row['AVATAR'];
	$account_name = $row['NAME'];

    //kiểm tra userid
    $DACCOUNTID = isset($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
    if (strlen($DACCOUNTID) == 0)
    {
        $error = "Tài khoản không tồn tại trong hệ thống!";
    }
    else
    {
        $query = "SELECT DACCOUNT.* FROM DACCOUNT WHERE ID = ?";
        $param = array($DACCOUNTID);
        $stmt = sqlsrv_query($conn, $query, $param , array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
        if ($row === NULL)
        {
            $error = "Tài khoản không tồn tại trong hệ thống!";
        }
        else
        {
			$current_name = $row["NAME"];
			$current_avatar = $row["AVATAR"];

            $query = "SELECT * FROM TFRIEND WHERE DACCOUNTID = ? AND DFRIENDID = ?";
            $stmt = sqlsrv_query($conn, $query, array($_SESSION['userId'], $DACCOUNTID), array('Scrollable'=>'static'));
			print_r(sqlsrv_errors());
            $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
            if ($row == NULL)
            {
                $error = "Không phải là bạn bè, không thể gửi tin nhắn!";
            }
            else
            {
				//đánh dấu là đã đọc hết
				$query = 'UPDATE TMESSAGE SET WATCHED = 30 WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)';
				$stmt = sqlsrv_query($conn, $query, array($DACCOUNTID, $_SESSION['userId'], $_SESSION['userId'], $DACCOUNTID));
                //lấy danh sách bạn bè đã nhắn tin
				$query = "SELECT ID, NAME, AVATAR FROM DACCOUNT WHERE ID <> ? AND ID <> ?
				AND EXISTS (SELECT * FROM TMESSAGE WHERE (DACCOUNTID_SEND = DACCOUNT.ID OR DACCOUNTID_RECEIVE = DACCOUNT.ID))
				ORDER BY (SELECT TOP 1 TIMECREATED FROM TMESSAGE WHERE (DACCOUNTID_SEND = DACCOUNT.ID OR DACCOUNTID_RECEIVE = DACCOUNT.ID)) DESC";
				$stmtUser = sqlsrv_query($conn, $query, array($_SESSION['userId'], $DACCOUNTID), array('Scrollable'=>'static'));
				print_r(sqlsrv_errors());
				//Lấy danh sách tin nhắn hiện tại
				$query = "SELECT * FROM
				(
					SELECT 0 AS LOAI, TIMECREATED, MESSAGE FROM TMESSAGE WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)
					UNION ALL
					SELECT 1, TIMECREATED, MESSAGE FROM TMESSAGE WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)
				) A ORDER BY A.TIMECREATED ASC";
				$stmtMessage = sqlsrv_query($conn, $query, array($_SESSION['userId'], $DACCOUNTID, $DACCOUNTID, $_SESSION['userId']), array('Scrollable'=>'static'));
				print_r(sqlsrv_errors());
            }
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
    <link href="./common/css/chat.css" rel="stylesheet">
</head>
<body>
<?php
    if (isset($error)) {
?>
    <div class="alert alert-danger" role="alert">
<?php echo $error ?>
    </div>
<?php
}
else {
    ?>
    <div class="container-fluid h-100">
			<div class="row justify-content-center h-100">
				<div class="col-md-4 col-xl-3 chat"><div class="card mb-sm-3 mb-md-0 contacts_card">
					<div class="card-header">
						<div class="input-group">
							<!-- <input type="text" placeholder="Search..." name="" class="form-control search">
							<div class="input-group-prepend">
								<span class="input-group-text search_btn"><i class="fas fa-search"></i></span>
							</div> -->

                            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="./index.php">
                                <div class="sidebar-brand-icon rotate-n-15">
                                    <i class="fas fa-laugh-wink" style="color:white"></i>
                                </div>
                                <div class="sidebar-brand-text mx-3" style="color:white">MESSENGER</div>
                            </a>
							<input id='userAvatar' value='<?php if (isset($avartar)) echo './uploads/' . $avartar; else echo './uploads/no-image.png'; ?>' hidden/>
						</div>
					</div>
					<div class="card-body contacts_body">
						<ui class="contacts">
						<li class="user-message active" data-id='<?php echo $DACCOUNTID; ?>'>
							<div class="d-flex bd-highlight">
								<div class="img_cont">
									<img src="<?php if (isset($current_avatar)) echo './uploads/' . $current_avatar; else echo './uploads/no-image.png'; ?>" class="rounded-circle user_img">
									<span class="online_icon offline"></span>
								</div>
								<div class="user_info">
									<span><?php echo $current_name; ?></span>
									<!-- <p>Kalid is online</p> -->
								</div>
							</div>
						</li>

						<?php
							while($row = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC))
							{
								?>
								<li class="user-message" data-id='<?php echo $row['ID']; ?>'>
									<div class="d-flex bd-highlight">
										<div class="img_cont">
										<img src="<?php if (isset($row['AVATAR'])) echo './uploads/' . $row['AVATAR']; else echo './uploads/no-image.png'; ?>" class="rounded-circle user_img">
											<span class="online_icon offline"></span>
										</div>
										<div class="user_info">
											<span><?php echo $row['NAME']; ?></span>
											<!-- <p>Taherah left 7 mins ago</p> -->
										</div>
									</div>
								</li>
								<?php
							}
						?>
						</ui>
					</div>
					<div class="card-footer"></div>
				</div></div>
				<div class="col-md-8 col-xl-6 chat">
					<div class="card">
						<div class="card-header msg_head">
							<div class="d-flex bd-highlight">
								<div class="img_cont">
									<img id='currentAvatar' src="<?php if (isset($current_avatar)) echo './uploads/' . $current_avatar; else echo './uploads/no-image.png'; ?>" class="rounded-circle user_img">
									<span class="online_icon offline"></span>
								</div>
								<div class="user_info">
									<input id='accountId' value="<?php echo $_SESSION['userId']; ?>" hidden/>
									<input id='currentId' value="<?php echo $DACCOUNTID; ?>" hidden/>
									<span><?php echo $current_name; ?></span>
									<input hidden id='account_name' value='<?php echo $account_name; ?>'/>
									<!-- <p>1767 Messages</p> -->
								</div>
								<!-- <div class="video_cam">
									<span><i class="fas fa-video"></i></span>
									<span><i class="fas fa-phone"></i></span>
								</div> -->
							</div>
							<span id="action_menu_btn"><i class="fas fa-ellipsis-v"></i></span>
							<div class="action_menu">
								<ul>
									<li><a href='./profile.php?userId=<?php echo $DACCOUNTID; ?>' style='color:white;'><i class="fas fa-user-circle"></i> View profile</a></li>
									<!-- <li><i class="fas fa-users"></i> Add to close friends</li>
									<li><i class="fas fa-plus"></i> Add to group</li> -->
									<li><i class="fas fa-ban remove-f"></i> Block</li>
								</ul>
							</div>
						</div>
						<div class="card-body msg_card_body">
							<?php
								while($row = sqlsrv_fetch_array($stmtMessage, SQLSRV_FETCH_ASSOC)){
									if ($row["LOAI"] == 0) {
										?>
										<div class="d-flex justify-content-end mb-4">
											<div class="msg_cotainer_send">
												<?php echo $row['MESSAGE']; ?>
												<!-- <span class="msg_time">8:40 AM, Today</span> -->
											</div>
											<div class="img_cont_msg">
												<img src="<?php if (isset($avartar)) echo './uploads/' . $avartar; else echo './uploads/no-image.png'; ?>" class="rounded-circle user_img_msg">
											</div>
										</div>
										<?php
									}
									else {
										?>
											<div class="d-flex justify-content-start mb-4">
												<div class="img_cont_msg">
													<img src="<?php if (isset($current_avatar)) echo './uploads/' . $current_avatar; else echo './uploads/no-image.png'; ?>" class="rounded-circle user_img_msg">
												</div>
												<div class="msg_cotainer">
													<?php echo $row['MESSAGE']; ?>
													<!-- <span class="msg_time">9:12 AM, Today</span> -->
												</div>
											</div>
										<?php
									}
								}
							?>
						</div>
						<div class="card-footer">
							<div class="input-group">
								<div class="input-group-append">
									<span class="input-group-text attach_btn"><i class="fas fa-paperclip"></i></span>
								</div>
								<textarea id="txtMessage" class="form-control type_msg" placeholder="Type your message..."></textarea>
								<div class="input-group-append">
									<span class="input-group-text send_btn"><i class="fas fa-location-arrow"></i></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
    <?php
}
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
<script src="./common/js/chat.js"></script>
<script src="./common/js/custom.js"></script>
</body>
</html>