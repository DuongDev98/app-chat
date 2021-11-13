<?php
    require_once 'config.php';
    $DACCOUNTID = $_SESSION["userId"];
    $query = "SELECT AVATAR FROM DACCOUNT WHERE ID = ?";
    
    $stmt = sqlsrv_query($conn, $query, array($DACCOUNTID), array('Scrollable'=>'static'));
    $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
    if ($row === NULL)
    {
        $error = "Tài khoản không tồn tại trong hệ thống!";
        echo 'error: '. $error;
        return;
    }

    if($_FILES['file']['name'] != ''){
        $test = explode('.', $_FILES['file']['name']);
        $extension = end($test);
        $uid = dechex( microtime(true) * 1000 ) . bin2hex( random_bytes(8) ); 
        $name = $uid.'.'.$extension;
        $location = 'uploads/'.$name;
        move_uploaded_file($_FILES['file']['tmp_name'], $location);
        //Xóa avata cũ nếu có
        $imgAvatar = $row["AVATAR"];
        if (strlen($imgAvatar) != 0)
        {
            if (file_exists('uploads/'.$row['AVATAR']))
            {
                unlink('uploads/'.$row['AVATAR']);
            }
        }
        //Cập nhật avatar mới
        $query = "UPDATE DACCOUNT SET AVATAR = ? WHERE ID = ?";
        $param = array($name, $DACCOUNTID);
        $stmt = sqlsrv_query($conn, $query, $param);
        if ($stmt === FALSE)
        {
            $error = print_r( sqlsrv_errors(), true);
            echo 'error: '. $error;
            return;
        }
        echo $DACCOUNTID;
    }