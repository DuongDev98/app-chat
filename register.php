<?php
    require_once 'config.php';
    $username = '';
    $name = '';
    $email = '';
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['ipUsername']);
        $name = trim($_POST['ipName']);
        $email = trim($_POST['ipEmail']);
        $password = trim($_POST['ipPassword']);
        $repeatPassword = trim($_POST['ipRepeatPassword']);
        //Kiểm tra trường dữ liệu
        if (strlen($username) === 0) {
            $error = "Tài khoản trống!";
        } else if (strlen($name) === 0) {
            $error = "Tên người dùng trống!";
        } else if (strlen($email) === 0) {
            $error = "Email trống!";
        } else if (strlen($password) === 0) {
            $error = "Mật khẩu trống!";
        } else if ($password != $repeatPassword) {
            $error = "Mật khẩu không khớp!";
        }
        if (!isset($error)) {
            //kiểm tra username có tồn tại trong hệ thống không
            $query = sqlsrv_query($conn, "SELECT * FROM DACCOUNT WHERE USERNAME = ?", array($username), array( "Scrollable" => 'static' ));
            $row_count = sqlsrv_num_rows($query);
            if ($row_count > 0) {
                $error = "Tài khoản đã tồn tại trong hệ thống!";
            } else {
                //insert vao database
                $query = "INSERT INTO DACCOUNT(TIMECREATED, ID, USERNAME, PASSWORD, NAME, EMAIl) 
                VALUES(GETDATE() ,LOWER(NEWID()), ?, ?, ?, ?)";
                $param = array($username, $password, $name, $email);
                $stmt = sqlsrv_query($conn, $query, $param);
                if ($stmt === FALSE)
                {
                    $error = print_r( sqlsrv_errors(), true);
                }
                else
                {
                    header('Location: login.php');
                }
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
</head>
<body class="bg-gradient-primary">
<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row" style="height: 530px">
                <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                <div class="col-lg-7">
                    <div class="p-5">
                        <div class="text-center">
                        <?php
                            if (isset($error)) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $error ?>
                        </div>
                        <?php
                            }
                        ?>
                            <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                        </div>
                        <form class="user" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="text" class="form-control form-control-user" name="ipUsername"
                                           placeholder="Username" value="<?php echo $username; ?>">
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control form-control-user" name="ipName"
                                           placeholder="Name" value="<?php echo $name; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control form-control-user" name="ipEmail"
                                       placeholder="Email Address" value="<?php echo $email; ?>">
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <input type="password" class="form-control form-control-user"
                                    name="ipPassword" placeholder="Password">
                                </div>
                                <div class="col-sm-6">
                                    <input type="password" class="form-control form-control-user"
                                    name="ipRepeatPassword" placeholder="Repeat Password">
                                </div>
                            </div>
                            <button class="btn btn-primary btn-user btn-block" type="submit">
                                Register Account
                            </button>
                        </form>
                        <hr>
                        <!-- <div class="text-center">
                            <a class="small" href="#">Forgot Password?</a>
                        </div> -->
                        <div class="text-center">
                            <a class="small" href="./login.php">Already have an account? Login!</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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