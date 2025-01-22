<?php
session_start(); // 开启 session

// 连接数据库
$servername = "localhost";
$username = "ymaz";
$password = "ymaz";
$dbname = "ymaz";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$usernameErr = $passwordErr = $captchaErr = "";
$successMessage = "";

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $username = test_input($_POST["username"]);
    $password = test_input($_POST["password"]);
    $nickname = test_input($_POST["nickname"]);
    $captcha = test_input($_POST["captcha"]);
    $ip_address = $_SERVER['REMOTE_ADDR']; // 获取用户 IP 地址

    // 验证图形验证码
    if (!captcha_check($captcha)) {
        $captchaErr = "图形验证码错误！";
    }

    // 验证用户名和密码的格式
    if (!preg_match("/^[a-zA-Z0-9]+$/", $username)) {
        $usernameErr = "用户名只能包含英文字母和数字！";
    }
    if (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
        $passwordErr = "密码只能包含英文字母和数字！";
    }

    // 验证密码长度
    if (strlen($password) <= 4) {
        $passwordErr = "密码必须大于4位数！";
    }

    // 检查用户名是否已存在
    $check_username_sql = "SELECT * FROM users WHERE username=?";
    $check_username_stmt = $conn->prepare($check_username_sql);
    $check_username_stmt->bind_param("s", $username);
    $check_username_stmt->execute();
    $check_username_result = $check_username_stmt->get_result();

    if ($check_username_result->num_rows > 0) {
        $usernameErr = "用户名已存在！";
    }

    // 如果没有错误，插入数据到数据库
    if (empty($usernameErr) && empty($passwordErr) && empty($captchaErr)) {
        // 使用 AES 加密密码
        $encryption_key = "yujianyujianyuji"; // 替换为您自己的密钥
        $encrypted_password = openssl_encrypt($password, "AES-128-ECB", $encryption_key);

        $insert_sql = "INSERT INTO users (username, password, nickname, ip_address) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssss", $username, $encrypted_password, $nickname, $ip_address);

        if ($insert_stmt->execute()) {
            $successMessage = "账号注册成功！";
        } else {
            echo "注册失败: " . $conn->error;
        }

        $insert_stmt->close();
    }

    $check_username_stmt->close();
    $conn->close();
}

// 辅助函数：处理表单数据
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// 辅助函数：验证图形验证码
function captcha_check($input_captcha)
{
    return ($input_captcha === $_SESSION["captcha"]);
}
?>

<!DOCTYPE HTML>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }

        h2 {
            color: #333333;
        }

        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 3px;
        }

        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #333333;
            color: #ffffff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .error {
            color: red;
            margin-top: 5px;
        }

        .success {
            color: green;
            margin-top: 5px;
        }

        .captcha-container {
            text-align: center;
        }

        .captcha-container img {
            border: 1px solid #cccccc;
            border-radius: 3px;
            margin-top: 10px;
        }

        @media only screen and (max-width: 600px) {
            .form-container {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>账号注册</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">用户名：</label>
                <input type="text" name="username" id="username" maxlength="10" required>
                <span class="error"><?php echo $usernameErr; ?></span>
            </div>
            <div class="form-group">
                <label for="password">密码：</label>
                <input type="password" name="password" id="password" maxlength="10" required>
                <span class="error"><?php echo $passwordErr; ?></span>
            </div>
            <div class="form-group">
                <label for="nickname">昵称：</label>
                <input type="text" name="nickname" id="nickname" maxlength="10">
            </div>
            <div class="form-group">
                <label for="captcha">图形验证码：</label>
                <input type="text" name="captcha" id="captcha" required>
                <span class="error"><?php echo $captchaErr; ?></span>
            </div>
            <div class="captcha-container">
                <img src="generate_captcha.php" alt="图形验证码">
            </div>
            <div class="form-group">
                <input type="submit" name="submit" value="注册">
            </div>
            <div class="success"><?php echo $successMessage; ?></div>
            <?php if (!empty($successMessage)) { ?>
                <p>注册成功，请前往 YMAZ 功能盒登录！</p>
            <?php } ?>
        </form>
    </div>
</body>

</html>

