<?php
session_start();

$servername = "localhost";
$username = "ymaz";
$password = "ymaz";
$dbname = "ymaz";

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}


// 定义用于记录登录失败次数的变量
$loginAttempts = 0;

// 检查是否存在登录失败次数的Session变量
if (isset($_SESSION['login_attempts'])) {
    $loginAttempts = $_SESSION['login_attempts'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 预防SQL注入
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);

    // 查询数据库中的管理员帐号和密码
    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // 登录成功
        echo "管理员登录成功！";
        // 生成24位随机cookie
        $cookieValue = bin2hex(random_bytes(12));
        // 设置cookie有效期为1天
        $cookieExpiration = time() + (24 * 60 * 60);
        // 设置cookie
        setcookie("adminCookie", $cookieValue, $cookieExpiration);
        // 重定向到admin.php
        header("Location: admin.php");
        exit();
    } else {
        // 增加登录失败次数
        $loginAttempts++;

        // 更新登录失败次数的Session变量
        $_SESSION['login_attempts'] = $loginAttempts;

        // 检查登录失败次数是否超过3次
        if ($loginAttempts >= 3) {
            // 禁用登录一分钟
            sleep(60);
            // 重置登录失败次数
            $loginAttempts = 0;
            // 更新登录失败次数的Session变量
            $_SESSION['login_attempts'] = $loginAttempts;
        }
    }
}


// 关闭数据库连接
$conn->close();
?>

<!DOCTYPE html>
<html>
<style>
        * {
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
        }

        input,
        button {
            background: transparent;
            border: 0;
            outline: none;
        }

        body {
            height: 100vh;
            background: linear-gradient(#141e30, #243b55);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            color: #03e9f4;
        }

        .loginBox {
            width: 400px;
            height: 364px;
            background-color: #0c1622;
            margin: 100px auto;
            border-radius: 10px;
            box-shadow: 0 15px 25px 0 rgba(0, 0, 0, .6);
            padding: 40px;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: aliceblue;
            margin-bottom: 30px;
            font-family: 'Courier New', Courier, monospace;
        }

        .item {
            height: 45px;
            border-bottom: 1px solid #fff;
            margin-bottom: 40px;
            position: relative;
        }

        .item input {
            width: 100%;
            height: 100%;
            color: #fff;
            padding-top: 20px;
            box-sizing: border-box;
        }

        .item input:focus+label,
        .item input:valid+label {
            top: 0px;
            font-size: 12px;
        }

        .item label {
            position: absolute;
            left: 0;
            top: 12px;
            transition: all 0.5s linear;
        }

        .btn {
            padding: 10px 20px;
            margin-top: 30px;
            color: #03e9f4;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 2px;
            left: 35%;
        }

        .btn:hover {
            border-radius: 5px;
            color: #fff;
            background: #03e9f4;
            box-shadow: 0 0 5px 0 #03e9f4,
                0 0 25px 0 #03e9f4,
                0 0 50px 0 #03e9f4,
                0 0 100px 0 #03e9f4;
            transition: all 1s linear;
        }

        .btn>span {
            position: absolute;
        }

        .btn>span:nth-child(1) {
            width: 100%;
            height: 2px;
            background: -webkit-linear-gradient(left, transparent, #03e9f4);
            left: -100%;
            top: 0px;
            animation: line1 1s linear infinite;
        }

        @keyframes line1 {

            50%,
            100% {
                left: 100%;
            }
        }

        .btn>span:nth-child(2) {
            width: 2px;
            height: 100%;
            background: -webkit-linear-gradient(top, transparent, #03e9f4);
            right: 0px;
            top: -100%;
            animation: line2 1s 0.25s linear infinite;
        }

        @keyframes line2 {

            50%,
            100% {
                top: 100%;
            }
        }

        .btn>span:nth-child(3) {
            width: 100%;
            height: 2px;
            background: -webkit-linear-gradient(left, #03e9f4, transparent);
            left: 100%;
            bottom: 0px;
            animation: line3 1s 0.75s linear infinite;
        }

        @keyframes line3 {

            50%,
            100% {
                left: -100%;
            }
        }

        .btn>span:nth-child(4) {
            width: 2px;
            height: 100%;
            background: -webkit-linear-gradient(top, transparent, #03e9f4);
            left: 0px;
            top: 100%;
            animation: line4 1s 1s linear infinite;
        }

        @keyframes line4 {

            50%,
            100% {
                top: -100%;
            }
        }

        .success {
            color: #00ff00;
            animation: successAnimation 1s linear infinite;
        }

        @keyframes successAnimation {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .failure {
            color: #ff0000;
            animation: failureAnimation 1s linear infinite;
        }

        @keyframes failureAnimation {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(10deg); }
            100% { transform: rotate(0deg); }
        }

        .message {
            text-align: center;
            color: #ffffff;
            margin-top: 20px;
            font-size: 18px;
            animation: fadeIn 0.5s linear;
            opacity: 0;
        }

        .message.show {
            opacity: 1;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateX(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }
</style>
<head>
    <title>管理员登录后台</title>
</head>
<body>
    <div class="loginBox">
        <h2>YMAZ功能盒管理员登录后台</h2>
        <form action="" method="POST">
            <div class="item">
                <input type="text" name="username" required>
                <label for="">userName</label>
            </div>
            <div class="item">
                <input type="password" name="password" required>
                <label for="">password</label>
            </div>
            <p class="message <?php echo isset($result) ? ($result ? 'success' : 'failure') : ''; ?>">
                <?php
                $servername = "localhost";
                $username = "ymaz";
                $password = "ymaz";
                $dbname = "ymaz";

                // 创建数据库连接
                $conn = new mysqli($servername, $username, $password, $dbname);

                // 检查连接是否成功
                if ($conn->connect_error) {
                    die("连接失败: " . $conn->connect_error);
                }

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = $_POST["username"];
                    $password = $_POST["password"];

                    // 预防SQL注入
                    $username = mysqli_real_escape_string($conn, $username);
                    $password = mysqli_real_escape_string($conn, $password);

                    // 查询数据库中的管理员帐号和密码
                    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        // 登录成功
                        echo "管理员登录成功！";
                        // 生成24位随机cookie
                        $cookieValue = bin2hex(random_bytes(12));
                        // 设置cookie有效期为1天
                        $cookieExpiration = time() + (24 * 60 * 60);
                        // 设置cookie
                        setcookie("adminCookie", $cookieValue, $cookieExpiration);
                        // 重定向到admin.php
                        header("Location: admin.php");
                        exit();
                    } else {
                        // 登录失败
                        echo "用户名或密码错误！";
                    }
                }

                // 关闭数据库连接
                $conn->close();
                ?>
            </p>
            <button class="btn" type="submit">submit
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </button>
        </form>
    </div>
</body>
</html>
