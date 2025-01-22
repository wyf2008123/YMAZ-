<?php
// 连接数据库
$servername = "localhost";
$username = "ymaz";
$password = "ymaz";
$dbname = "ymaz";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 获取访问者的 IP 地址
$ip = $_SERVER['REMOTE_ADDR'];

// 检查 IP 地址是否已被拉黑
$blacklist_sql = "SELECT * FROM blacklist WHERE ip_address = '$ip'";
$result = $conn->query($blacklist_sql);

// 如果 IP 地址已被拉黑，则显示非法访问提示
if ($result->num_rows > 0) {
    echo "非法访问，您的 IP 地址已被拉黑！";
} else {
    // 将访问者 IP 地址记录在数据库中
    $insert_sql = "INSERT INTO blacklist (ip_address) VALUES ('$ip')";
    if ($conn->query($insert_sql) === TRUE) {
        echo "非法访问，您的 IP 地址已被记录并拉黑！";
    } else {
        echo "记录 IP 地址时出错：" . $conn->error;
    }
}

$conn->close();
?>
