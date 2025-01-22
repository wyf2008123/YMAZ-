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

$usernameErr = $passwordErr = "";
$errorMessage = "";

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $username = test_input($_POST["username"]);
    $password = test_input($_POST["password"]);

    // 如果没有错误，验证用户名和密码是否匹配数据库中的记录
    if (empty($usernameErr) && empty($passwordErr)) {
        // 使用 AES 解密数据库中的密码
        $encryption_key = "yujianyujianyuji"; // 替换为您自己的密钥
        $encryption_key2 = "yujianyuijanyuji"; // 替换为您自己的密钥
        $select_sql = "SELECT * FROM users WHERE username=?";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bind_param("s", $username);
        $select_stmt->execute();
        $select_result = $select_stmt->get_result();

        if ($select_result->num_rows > 0) {
            $row = $select_result->fetch_assoc();
            $encrypted_password = $row['password'];

            // 使用 AES 解密数据库中的密码
            $decrypted_password = openssl_decrypt($encrypted_password, "AES-128-ECB", $encryption_key);

            if (strcmp($password, $decrypted_password) === 0) {
                // 验证当前 IP 与数据库中的 IP 是否相同
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $registered_ip = $row['ip_address'];
                $is_member = $row['membership']; // 获取会员状态

                $timestamp = time(); // 获取当前13位时间戳

if ($is_member) {
    // 判断会员是否过期
    $current_time = time(); // 获取当前时间戳
    $membership_expiry = $row['membership_end']; // 获取会员过期时间

    if ($membership_expiry == 0) {
        // 会员是永久会员
        if ($ip_address !== $registered_ip) {
            $result = array(
                "status" => "success",
                "message" => "登录成功，会员用户，ip不一致",
                "timestamp" => $timestamp
            );
        } else {
            $result = array(
                "status" => "success",
                "message" => "登录成功，会员用户",
                "timestamp" => $timestamp
            );
        }
    } else {
        // 会员非永久会员，判断会员是否过期
        if ($current_time > $membership_expiry) {
            // 会员已过期
            $result = array(
                "status" => "success",
                "message" => "会员已过期",
                "timestamp" => $timestamp
            );
                // 更新数据库中的会员状态为非会员
    $update_sql = "UPDATE users SET membership = 0 WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("s", $username);
    $update_stmt->execute();
    $update_stmt->close();
        } else {
            if ($ip_address !== $registered_ip) {
                $result = array(
                    "status" => "success",
                    "message" => "登录成功，会员用户，ip不一致",
                    "timestamp" => $timestamp
                );
            } else {
                $result = array(
                    "status" => "success",
                    "message" => "登录成功，会员用户",
                    "timestamp" => $timestamp
                );
            }
        }
    }
} else {
    $result = array(
        "status" => "success",
        "message" => "登录成功，非会员用户",
        "timestamp" => $timestamp
    );
}

$encrypted_result2 = openssl_encrypt(json_encode($result), "AES-128-ECB", $encryption_key);
$encrypted_result3 = openssl_encrypt(json_encode($encrypted_result2), "AES-128-ECB", $encryption_key2);
$encrypted_result = base64_encode($encrypted_result3); // 进行Base64加密
echo $encrypted_result;
exit;

            } else {
                $result = array("status" => "error", "message" => "账号或密码错误");
                $encrypted_result2 = openssl_encrypt(json_encode($result), "AES-128-ECB", $encryption_key);
                $encrypted_result3 = openssl_encrypt(json_encode($encrypted_result2), "AES-128-ECB", $encryption_key2);
$encrypted_result = base64_encode($encrypted_result3); // 进行Base64加密
                echo $encrypted_result;
                exit;
            }
        } else {
            $result = array("status" => "error", "message" => "账号或密码错误！");
            $encrypted_result2 = openssl_encrypt(json_encode($result), "AES-128-ECB", $encryption_key);
            $encrypted_result3 = openssl_encrypt(json_encode($encrypted_result2), "AES-128-ECB", $encryption_key2);
$encrypted_result = base64_encode($encrypted_result3); // 进行Base64加密
            echo $encrypted_result;
            exit;
        }

        $select_stmt->close();
    }

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
?>
