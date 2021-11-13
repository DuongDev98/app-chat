<?php
require_once 'config.php';
$dataResult['success'] = TRUE;
if (!isset($_SESSION['userId']))
{
    $dataResult['success'] = FALSE;
    echo utf8_encode(json_encode($dataResult));
    return;
}

$func = '';
if (isset($_GET['f']))
{
    $func = $_GET['f'];
}

class DataMessage
{
    public $type;
    public $user_send;
    public $user_reveice;
    public $message;
}

$dataResult['success'] = TRUE;
$param = array($_SESSION['userId']);
switch ($func)
{
    case 'messageCenter' : {
        $query = 'SELECT COUNT(*) AS COUNT FROM TMESSAGE WHERE DACCOUNTID_RECEIVE = ? AND COALESCE(WATCHED, 0) = 0';
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
        $count = $row['COUNT'];

        //Lấy danh top 5 tin nhắn
        $param = array($_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"],$_SESSION["userId"]);
        $query = "SELECT TOP 5 FORMAT(A.TIMECREATED, 'HH:mm:ss dd/MM/yy') AS TIMECREATED, A.DACCOUNTID_SEND, A.AVATAR,
        A.NAME, A.WATCHED, A.MESSAGE FROM (
        SELECT ID AS DACCOUNTID_SEND, AVATAR,  DACCOUNT.NAME,
        (SELECT TOP 1 COALESCE(WATCHED,0) FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS WATCHED,
        (SELECT TOP 1 TMESSAGE.TIMECREATED FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS TIMECREATED,
        (SELECT TOP 1 MESSAGE FROM TMESSAGE WHERE ((DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = DACCOUNT.ID)) ORDER BY TIMECREATED DESC) AS MESSAGE
        FROM DACCOUNT WHERE ID <> ? AND EXISTS (SELECT * FROM TMESSAGE WHERE (DACCOUNTID_SEND = DACCOUNT.ID AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_RECEIVE = DACCOUNT.ID AND DACCOUNTID_SEND = ?))
        )A ORDER BY A.TIMECREATED DESC";
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $arr = array();
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        {
            array_push($arr, $row);
        }
        $dataResult['data-count'] = $count;
        $dataResult['data-arr'] = $arr;
    } break;
    case 'notificationCenter' : {
        $query = 'SELECT COUNT(*) AS COUNT FROM TNOTIFICATION WHERE DACCOUNTID_RECEIVE = ? AND COALESCE(WATCHED, 0) = 0';
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
        $count = $row['COUNT'];

        //Lấy danh top 5 tin nhắn
        $query = "SELECT TOP 5 TNOTIFICATION.ID, DACCOUNTID_SEND, COALESCE(WATCHED,0) AS WATCHED, FORMAT(TNOTIFICATION.TIMECREATED, 'HH:mm:ss dd/MM/yy') AS TIMECREATED, AVATAR, MESSAGE
        FROM TNOTIFICATION INNER JOIN DACCOUNT ON DACCOUNTID_SEND = DACCOUNT.ID
        WHERE DACCOUNTID_RECEIVE = ? ORDER BY TMESSAGE.TIMECREATED DESC";
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $arr = array();
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
        {
            array_push($arr, $row);
        }
        $dataResult['data-count'] = $count;
        $dataResult['data-arr'] = $arr;
    } break;
    case 'numberOfFriend': {
        $query= 'SELECT COUNT(*) AS COUNT_FRIEND FROM TFRIEND WHERE DACCOUNTID = ?';
        $param = array($_SESSION["userId"]);
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
        $dataResult['data-count'] = $row['COUNT_FRIEND'];
    } break;
    case 'numberOfFriendReq': {
        $query= 'SELECT COUNT(*) AS COUNT_REQ FROM TFRIENDREQUEST WHERE DACCOUNTID_RECEIVE = ? AND STATUS = 0';
        $param = array($_SESSION["userId"]);
        $stmt = sqlsrv_query($conn, $query, $param, array('Scrollable'=>'static'));
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $dataResult['data-count'] = $row['COUNT_REQ'];
    } break;
    case 'sendFriendRequest': {
        $data = new DataMessage();
        $data = json_decode($_POST["data"]);

        //thêm lời mời kết bạn
        $query = "INSERT TFRIENDREQUEST(ID, STATUS, DACCOUNTID_SEND, DACCOUNTID_RECEIVE, TIMECREATED)
        VALUES(LOWER(NEWID()), 0, ?, ?, GETDATE())";
        $param = array($data->user_send, $data->user_reveice);
        $stmt = sqlsrv_query($conn, $query, $param);
        if ($stmt === FALSE)
        {
            $dataResult['success'] = FALSE;
            $data->error = print_r( sqlsrv_errors(), true);
        }
        else {
            //Thêm tiếp thông báo
            $query = "INSERT INTO TNOTIFICATION(ID, DACCOUNTID_SEND, DACCOUNTID_RECEIVE, TIMECREATED, TYPE, MESSAGE)";
            $query = $query . " VALUES (LOWER(NEWID()), ?, ?, GETDATE(), 0, (SELECT NAME FROM DACCOUNT WHERE ID = ?) + N' gửi cho bạn lời mời kết bạn')";
            $param = array($data->user_send, $data->user_reveice, $data->user_send);
            $stmt = sqlsrv_query($conn, $query, $param);
            if ($stmt === FALSE)
            {
                $dataResult['success'] = FALSE;
                $dataResult['msg'] = print_r( sqlsrv_errors(), true);
            }
        }
        $dataResult['success'] = TRUE;
    } break;
    case 'okFriendRequest': {
        $data = new DataMessage();
        $data = json_decode($_POST["data"]);

        //chấp nhận
        $query = 'INSERT INTO TFRIEND(ID, DACCOUNTID, DFRIENDID, TIMECREATED)
        VALUES(LOWER(NEWID()), ?, ?, GETDATE())';
        $stmt = sqlsrv_query($conn, $query, array($data->user_send, $data->user_reveice));
        if ($stmt === FALSE) {
            $dataResult['success'] = FALSE;
            $dataResult['msg'] = print_r(sqlsrv_errors(), true);
        }
        else {
            $query = 'INSERT INTO TFRIEND(ID, DACCOUNTID, DFRIENDID, TIMECREATED)
            VALUES(LOWER(NEWID()), ?, ?, GETDATE())';
            $stmt = sqlsrv_query($conn, $query, array($data->user_reveice, $data->user_send));
            if ($stmt === FALSE) {
                $dataResult['success'] = FALSE;
                $dataResult['msg'] = print_r(sqlsrv_errors(), true);
            }
            else {
                //cập nhật trạng thái chấp nhận
                $query = 'UPDATE TFRIENDREQUEST SET STATUS = 30 WHERE DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?';
                $stmt = sqlsrv_query($conn, $query, array($data->user_reveice, $data->user_send));
                if ($stmt === FALSE) {
                    $dataResult['success'] = FALSE;
                    $dataResult['msg'] = print_r(sqlsrv_errors(), true);
                }
                else {
                    //Thêm tiếp thông báo
                    $query = "INSERT INTO TNOTIFICATION(ID, DACCOUNTID_SEND, DACCOUNTID_RECEIVE, TIMECREATED, TYPE, MESSAGE)";
                    $query = $query . " VALUES (LOWER(NEWID()), ?, ?, GETDATE(), 1, (SELECT NAME FROM DACCOUNT WHERE ID = ?) + N' đã chấp lời mời kết bạn')";
                    $param = array($data->user_send, $data->user_reveice, $data->user_send);
                    $stmt = sqlsrv_query($conn, $query, $param);
                    if ($stmt === FALSE)
                    {
                        $dataResult['success'] = FALSE;
                        $dataResult['msg'] = print_r( sqlsrv_errors(), true);
                    }
                }
            }
        }
    } break;
    case 'cancelFriendRequest': {
        $data = new DataMessage();
        $data = json_decode($_POST["data"]);

        //ko chấp nhận
        $query = 'DELETE FROM TFRIENDREQUEST WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)';
        $stmt = sqlsrv_query($conn, $query, array($data->user_send,$data->user_reveice,$data->user_reveice,$data->user_send));
        if ($stmt === FALSE) {
            $dataResult['success'] = FALSE;
            $dataResult['msg'] = print_r(sqlsrv_errors(), true);
        }
        else {
            $query = 'DELETE FROM TNOTIFICATION WHERE DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ? AND TYPE = 0';
            $stmt = sqlsrv_query($conn, $query, array($data->user_reveice,$data->user_send));
            if ($stmt === FALSE) {
                $dataResult['success'] = FALSE;
                $dataResult['msg'] = print_r(sqlsrv_errors(), true);
            }
        }
    } break;
    case 'removeFriend': {
        $data = new DataMessage();
        $data = json_decode($_POST["data"]);
        //hủy kết bạn
        $query = 'DELETE FROM TFRIEND WHERE (DACCOUNTID = ? AND DFRIENDID = ?) OR (DACCOUNTID = ? AND DFRIENDID = ?)';
        $stmt = sqlsrv_query($conn, $query, array($data->user_send,$data->user_reveice,$data->user_reveice,$data->user_send));
        if ($stmt === FALSE) {
            $dataResult['success'] = FALSE;
            $dataResult['msg'] = print_r(sqlsrv_errors(), true);
        }
        else {
            $query = 'DELETE FROM TFRIENDREQUEST WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)';
            $stmt = sqlsrv_query($conn, $query, array($data->user_send,$data->user_reveice,$data->user_reveice,$data->user_send));
            if ($stmt === FALSE) {
                $dataResult['success'] = FALSE;
                $dataResult['msg'] = print_r(sqlsrv_errors(), true);
            }
            else {
                $query = 'DELETE FROM TNOTIFICATION WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?) OR (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)';
                $stmt = sqlsrv_query($conn, $query, array($data->user_send,$data->user_reveice,$data->user_reveice,$data->user_send));
                if ($stmt === FALSE) {
                    $dataResult['success'] = FALSE;
                    $dataResult['msg'] = print_r(sqlsrv_errors(), true);
                }
            }
        }
    } break;
    case 'saveMessage': {
        $data = new DataMessage();
        $data = json_decode($_POST["data"]);
        //hủy kết bạn
        $query = "INSERT INTO TMESSAGE(ID, DACCOUNTID_SEND, DACCOUNTID_RECEIVE, MESSAGE, WATCHED, TIMECREATED)";
        $query = $query . " VALUES (LOWER(NEWID()), ?, ?, ?, 0, GETDATE())";
        $stmt = sqlsrv_query($conn, $query, array($data->user_send,$data->user_reveice, $data->message));
        if ($stmt === FALSE) {
            $dataResult['success'] = FALSE;
            $dataResult['msg'] = print_r(sqlsrv_errors(), true);
        }
    } break;
    case 'getAllMessage': {
        $data = json_decode($_POST["data"]);
        $query = "SELECT * FROM
				(
					SELECT 0 AS LOAI, TIMECREATED, MESSAGE FROM TMESSAGE WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)
					UNION ALL
					SELECT 1, TIMECREATED, MESSAGE FROM TMESSAGE WHERE (DACCOUNTID_SEND = ? AND DACCOUNTID_RECEIVE = ?)
				) A ORDER BY A.TIMECREATED ASC";
		$stmt = sqlsrv_query($conn, $query, array($data->user_send, $data->user_reveice, $data->user_reveice, $data->user_send), array('Scrollable'=>'static'));
        if ($stmt === FALSE) {
            $dataResult['success'] = FALSE;
            $dataResult['msg'] = print_r(sqlsrv_errors(), true);
        }
        else {
            $arrData = array();
            while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
            {
                array_push($arrData, $row);
            }
            $dataResult['msg'] = json_encode($arrData);
        }
    } break;
}
echo utf8_encode(json_encode($dataResult));