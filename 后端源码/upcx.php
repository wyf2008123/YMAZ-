<?php
// 连接数据库
$servername = "localhost";
$username = "ymaz";
$password = "ymaz";
$dbname = "ymaz";

$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT version, announcement, update_link, DATE_FORMAT(update_time, '%Y-%m-%d') AS formatted_update_time FROM gx ORDER BY version DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "[" . $row['version'] . "] (" . $row['formatted_update_time'] . ")\n";
        echo "◐\n";
        echo $row['announcement']."\n";
        echo "下载链接：".$row['update_link']."\n";
        echo "◐\n";
    }
}

// 关闭数据库连接的代码
?>
