<?php
session_start();
header("Content-Type: application/json");

// 连接数据库
$servername = "localhost";
$username = "ymaz";
$password = "ymaz";
$dbname = "ymaz";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 处理卡密充值逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $username = $_POST["username"];
    $password = $_POST["password"];
    $code = $_POST["code"];
    $cardKey = $_POST["cardKey"];
    
    // 验证账号和密码是否正确
    if (!validateAccount($username, $password)) {
        $response = array(
            "success" => false,
            "message" => "账号或密码错误",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }

    // 验证卡密是否存在
    if (!validateCardKey($cardKey)) {
        $response = array(
            "success" => false,
            "message" => "无效的卡密",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }

    // 检查卡密是否已经被绑定
    if (isCardKeyUsed($cardKey)) {
        $response = array(
            "success" => false,
            "message" => "卡密已被绑定",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }

    // 验证标识码是否合法
    if (!validateCode($code)) {
        $response = array(
            "success" => false,
            "message" => "标识码异常",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }

    // 获取卡密类型
    $cardType = getCardKeyType($cardKey);

    // 根据卡密类型进行充值
    if ($cardType == "一天卡密") {
        $membershipEnd = strtotime("+1 days"); // 充值一天后的时间戳
    }
    elseif ($cardType == "一个月卡密") {
        $membershipEnd = strtotime("+1 month"); // 充值一个月后的时间戳
    } elseif ($cardType == "一季度卡密") {
        $membershipEnd = strtotime("+3 months"); // 充值三个月后的时间戳
    } elseif ($cardType == "永久卡密") {
        $membershipEnd = 0; // 永久会员，时间戳为0
    } else {
        $response = array(
            "success" => false,
            "message" => "无效的卡密类型",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }

    // 更新会员时间
    if (updateMembership($username, $membershipEnd)) {
        // 绑定卡密到标识码
        bindCardKey($code, $cardKey);

        $response = array(
            "success" => true,
            "message" => "充值成功",
            "membership_end" => $membershipEnd,
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    } else {
        $response = array(
            "success" => false,
            "message" => "充值失败",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }
}

// 验证账号和密码是否正确
function validateAccount($username, $password)
{
    global $conn;
    $select_sql = "SELECT * FROM users WHERE username=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $username);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $encryptedPassword = $row['password'];
        $decryptedPassword = decryptPassword($encryptedPassword); // 解密密码

        if ($password == $decryptedPassword) {
            return true;
        }
    }

    return false;
}

// 验证卡密是否存在
function validateCardKey($cardKey)
{
    global $conn;
    $select_sql = "SELECT * FROM card_keys WHERE card_key=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $cardKey);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

// 检查卡密是否已经被绑定
function isCardKeyUsed($cardKey)
{
    global $conn;
    $select_sql = "SELECT * FROM card_keys WHERE code=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $cardKey);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

// 获取卡密类型
function getCardKeyType($cardKey)
{
    global $conn;
    $select_sql = "SELECT card_type FROM card_keys WHERE card_key=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $cardKey);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['card_type'];
    } else {
        return false;
    }
}

// 更新会员时间和状态
function updateMembership($username, $membershipEnd)
{
    global $conn;
    $update_sql = "UPDATE users SET membership_end=?, membership=1 WHERE username=?"; // 将membership字段设置为1表示会员已激活
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ss", $membershipEnd, $username);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}


// 绑定卡密到标识码
function bindCardKey($code, $cardKey)
{
    global $conn;
    
    // 检查卡密是否已经绑定到其他标识码
    $select_sql = "SELECT * FROM card_keys WHERE card_key=? AND code IS NOT NULL";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $cardKey);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response = array(
            "success" => false,
            "message" => "卡密已被绑定",
            "timestamp" => time()
        );
        echo encryptResponse($response);
        exit();
    }
    
    // 更新卡密绑定的标识码
    $update_sql = "UPDATE card_keys SET code=? WHERE card_key=?";
    $stmt = $conn->prepare($update_sql);
    
    if ($stmt) {
        $stmt->bind_param("ss", $code, $cardKey);
        
        if ($stmt->execute()) {
            // 执行成功
            return;
        }
    }
    
    // 执行失败
    $response = array(
        "success" => false,
        "message" => "绑定卡密时发生错误",
        "timestamp" => time()
    );
    echo encryptResponse($response);
    exit();
}

// 解密密码
function decryptPassword($encryptedPassword)
{
    $key = "yujianyujianyuji"; // 密钥
    $method = "AES-128-ECB"; // 加密算法
    $decryptedPassword = openssl_decrypt(base64_decode($encryptedPassword), $method, $key, OPENSSL_RAW_DATA);

    return $decryptedPassword;
}

// 验证标识码是否合法
function validateCode($code)
{
    // 使用正则表达式匹配字母和数字组合
    $pattern = '/^[a-zA-Z0-9]+$/';
    return preg_match($pattern, $code);
}

// 辅助函数：处理表单数据
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// 解密数据
function decryptData($encryptedData)
{
    $key = "yujianyuijanyuji"; // 密钥
    $method = "AES-128-ECB"; // 加密算法
    $decryptedData = openssl_decrypt(base64_decode($encryptedData), $method, $key, OPENSSL_RAW_DATA);
    return $decryptedData;
}

// 加密响应数据
function encryptResponse($response)
{
    $key = "yujianyujianyuji"; // 密钥
    $method = "AES-128-ECB"; // 加密算法
    $encryptedResponse2 = openssl_encrypt(json_encode($response), $method, $key, 0);
    $encryptedResponse3 = openssl_encrypt(json_encode($encryptedResponse2), $method, $key, 0);
    $encryptedResponse = base64_encode($encryptedResponse3); // 进行Base64加密
    return $encryptedResponse;
}
?>