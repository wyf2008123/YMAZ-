<?php
session_start();

// 检查是否存在adminCookie
if (isset($_COOKIE['adminCookie'])) {
    $cookieValue = $_COOKIE['adminCookie'];

    // 验证cookie的格式是否正确
    if (preg_match('/^[a-f0-9]{24}$/', $cookieValue)) {
    } else {
        // cookie格式不正确，重定向回登录页面
        header("Location: blacklist.php");
        exit();
    }
} else {
    // 没有adminCookie，重定向回登录页面
    header("Location: blacklist.php");
    exit();
}

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

// 检查 IP 地址是否在黑名单中
$blacklist_sql = "SELECT * FROM blacklist WHERE ip_address = '$ip'";
$result = $conn->query($blacklist_sql);

// 如果 IP 地址在黑名单中，则终止脚本并显示非法访问提示
if ($result->num_rows > 0) {
    die("非法访问，您的 IP 地址已被拉黑！");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>账号管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="submit"],
        input[type="text"],
        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f5f5f5;
        }

        .fade-in {
            animation: fade-in 0.5s forwards;
        }

        @keyframes fade-in {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
<div class="container fade-in">
    <h1>账号管理</h1>
    <!-- 显示所有账号信息 -->
    <h2>显示所有账号信息</h2>
    <form method="post">
        <input type="hidden" name="action" value="display">
        <input type="submit" value="显示所有账号信息">
    </form>
    <!-- 查看卡密记录 -->
    <h2>查看卡密记录</h2>
    <form method="post">
        <input type="hidden" name="action" value="viewCardKeys">
        <input type="submit" value="查看卡密记录">
    </form>

    <!-- 删除卡密 -->
    <h2>删除卡密</h2>
    <form method="post">
        <input type="hidden" name="action" value="deleteCardKey">
        <select name="cardKey">
            <?php
            $select_sql = "SELECT card_key FROM card_keys";
            $result = $conn->query($select_sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['card_key']}'>{$row['card_key']}</option>";
                }
            } else {
                echo "<option value=''>没有卡密可供选择</option>";
            }
            ?>
        </select>
        <input type="submit" value="删除卡密">
    </form>

    <!-- 删除账号 -->
    <h2>删除账号</h2>
    <form method="post">
        <input type="hidden" name="action" value="delete">
        <input type="text" name="username" placeholder="请输入账号名称">
        <input type="submit" value="删除账号">
    </form>

    <!-- 修改会员状态 -->
    <h2>修改会员状态</h2>
    <form method="post">
        <input type="hidden" name="action" value="changeMembership">
        <input type="text" name="username" placeholder="请输入账号名称">
        <select name="isMember">
            <option value="yes">改为会员</option>
            <option value="no">取消会员</option>
        </select>
        <select name="duration">
            <option value="86400">一天</option>
            <option value="2626560">一个月</option>
            <option value="7891680">一季度</option>
            <option value="0">永久</option>
        </select>
        <input type="submit" value="修改会员状态">
    </form>

    <!-- 搜索账号 -->
    <h2>搜索账号</h2>
    <form method="post">
        <input type="hidden" name="action" value="search">
        <input type="text" name="search" placeholder="请输入账号名称">
        <input type="submit" value="搜索账号">
    </form>

    <!-- 生成卡密 -->
    <h2>生成卡密</h2>
    <form method="post">
        <input type="hidden" name="action" value="generate">
        <select name="cardType">
            <option value="1">一天卡密</option>
            <option value="2">一个月卡密</option>
            <option value="3">一季度卡密</option>
            <option value="4">永久卡密</option>
        </select>
        <input type="number" name="quantity" placeholder="生成的卡密数量">
        <input type="submit" value="生成卡密">
    </form>

    <!-- 搜索结果 -->
    <?php
    if (isset($search_results)) {
        echo "<h2>搜索结果</h2>";
        if ($search_results) {
            echo "<table>";
            echo "<tr><th>账号名称</th><th>昵称</th><th>注册时间</th><th>IP地址</th><th>会员状态</th></tr>";
            $count = 0;
            foreach ($search_results as $row) {
                $count++;
                $membership = $row['membership'] ? '是' : '否';
                $row_color = ($count % 2 == 0) ? '#f5f5f5' : '#ffffff';
                echo "<tr style='background-color: $row_color;'>";
                echo "<td>{$row['username']}</td>";
                echo "<td>{$row['nickname']}</td>";
                echo "<td>{$row['reg_date']}</td>";
                echo "<td>{$row['ip_address']}</td>";
                echo "<td>{$membership}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>没有找到匹配的账号</p>";
        }
    }
    ?>

    <!-- 操作结果消息 -->
    <?php
    if (isset($message)) {
        echo "<div class='message ";
        echo $message['type'] == 'success' ? "success" : "error";
        echo "'>";
        echo $message['text'];
        echo "</div>";
    }
    ?>
</div>
</body>
</html>


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

// 功能一：显示所有账号信息
function displayAllAccounts()
{
    global $conn;
    $select_sql = "SELECT username, nickname, reg_date, ip_address, membership, membership_end FROM users";
    $result = $conn->query($select_sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>账号名称</th><th>昵称</th><th>注册时间</th><th>IP地址</th><th>会员状态</th><th>会员时间（时间戳）</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $membership = $row['membership'] ? '是' : '否';
            echo "<tr><td>{$row['username']}</td><td>{$row['nickname']}</td><td>{$row['reg_date']}</td><td>{$row['ip_address']}</td><td>{$membership}</td><td>{$row['membership_end']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "没有账号信息";
    }
}

// 功能二：删除账号
function deleteAccount($username)
{
    global $conn;
    $select_sql = "SELECT * FROM users WHERE username=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $username);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM users WHERE username=?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            echo "成功删除账号：{$username}";
        } else {
            echo "删除账号失败";
        }

        $stmt->close();
    } else {
        echo "没有找到该账号";
    }

    $select_stmt->close();
}

function changeMembershipStatus($username, $isMember, $duration)
{
    global $conn;
    $select_sql = "SELECT * FROM users WHERE username=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $username);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        if ($duration == "0") {
            $membership_end = 0; // 设置为永久会员
        } else {
            $durationInSeconds = intval($duration); // 将持续时间转换为整数
            $current_timestamp = time();
            $membership_end = $current_timestamp + $durationInSeconds; // 设置会员过期时间为当前时间戳加上选择的会员时长
        }

        $update_sql = "UPDATE users SET membership=?, membership_end=? WHERE username=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iss", $isMember, $membership_end, $username);

        if ($stmt->execute()) {
            echo "成功修改会员状态";
        } else {
            echo "修改会员状态失败：" . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "没有找到该账号";
    }

    $select_stmt->close();
}



// 功能四：搜索账号
function searchAccount($keyword)
{
    global $conn;
    $search_sql = "SELECT username, nickname, reg_date, ip_address, membership FROM users WHERE username LIKE ?";
    $stmt = $conn->prepare($search_sql);
    $keyword = "%" . $keyword . "%";
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>账号名称</th><th>昵称</th><th>注册时间</th><th>IP地址</th><th>会员状态</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $membership = $row['membership'] ? '是' : '否';
            echo "<tr><td>{$row['username']}</td><td>{$row['nickname']}</td><td>{$row['reg_date']}</td><td>{$row['ip_address']}</td><td>{$membership}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "没有找到匹配的账号";
    }

    $stmt->close();
}
// 生成和记录卡密
function generateCardKeys($cardType, $quantity)
{
    global $conn;
    $cardTypeNames = array("一天卡密","一个月卡密", "一季度卡密", "永久卡密");
    $cardTypeDuration = array(86400,2626560, 7891680, 0);

    $cardKeys = array();
    for ($i = 0; $i < $quantity; $i++) {
        $cardKey = generateRandomKey();
        $cardKeys[] = $cardKey;

        $insert_sql = "INSERT INTO card_keys (card_key, card_type) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ss", $cardKey, $cardTypeNames[$cardType - 1]);
        $stmt->execute();
        $stmt->close();
    }

    echo "<h3>生成的{$cardTypeNames[$cardType - 1]}：</h3>";
    echo "<ul>";
    foreach ($cardKeys as $cardKey) {
        echo "<li>{$cardKey}</li>";
    }
    echo "</ul>";
}

// 功能五：查看卡密记录
function viewCardKeys()
{
    global $conn;
    $select_sql = "SELECT card_key, card_type, created_at, code FROM card_keys";
    $result = $conn->query($select_sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>卡密</th><th>卡密类型</th><th>卡密创建时间</th><th>卡密绑定码</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['card_key']}</td><td>{$row['card_type']}</td><td>{$row['created_at']}</td><td>{$row['code']}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "没有卡密记录";
    }
}

// 功能六：删除卡密
function deleteCardKey($cardKey)
{
    global $conn;
    $select_sql = "SELECT * FROM card_keys WHERE card_key=?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("s", $cardKey);
    $select_stmt->execute();
    $result = $select_stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM card_keys WHERE card_key=?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("s", $cardKey);

        if ($stmt->execute()) {
            echo "成功删除卡密：{$cardKey}";
        } else {
            echo "删除卡密失败";
        }

        $stmt->close();
    } else {
        echo "没有找到该卡密";
    }

    $select_stmt->close();
}


// 辅助函数：生成随机卡密
function generateRandomKey()
{
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $keyLength = 24;
    $randomKey = "";

    for ($i = 0; $i < $keyLength; $i++) {
        $randomKey .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomKey;
}

// 处理表单提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取表单数据
    $action = test_input($_POST["action"]);

    // 根据不同的操作执行相应的功能
    if ($action == "display") {
        displayAllAccounts();
    } elseif ($action == "delete") {
        $username = test_input($_POST["username"]);
        deleteAccount($username);
    } elseif ($action == "changeMembership") {
        $username = test_input($_POST["username"]);
        $isMember = test_input($_POST["isMember"]);
        $duration = test_input($_POST["duration"]);

        if ($isMember == "yes") {
            changeMembershipStatus($username, 1, $duration);
        } elseif ($isMember == "no") {
            changeMembershipStatus($username, 0, $duration);
        } else {
            echo "无效的会员状态";
        }
    } elseif ($action == "search") {
        $keyword = test_input($_POST["search"]);
        searchAccount($keyword);
    } elseif ($action == "generate") {
        $cardType = test_input($_POST["cardType"]);
        $quantity = test_input($_POST["quantity"]);

        if ($cardType && $quantity > 0) {
            generateCardKeys($cardType, $quantity);
        } else {
            echo "无效的卡密类型或数量";
        }
    } elseif ($action == "viewCardKeys") {
        viewCardKeys();
    } elseif ($action == "deleteCardKey") {
        $cardKey = test_input($_POST["cardKey"]);
        deleteCardKey($cardKey);
    } else {
        echo "无效的操作";
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