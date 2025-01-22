<?php
session_start();

// 检查是否存在adminCookie
if (isset($_COOKIE['adminCookie'])) {
    $cookieValue = $_COOKIE['adminCookie'];

    // 验证cookie的格式是否正确
    if (preg_match('/^[a-f0-9]{24}$/', $cookieValue)) {
    } else {
        // cookie格式不正确，重定向回登录页面
        header("Location: index.php");
        exit();
    }
} else {
    // 没有adminCookie，重定向回登录页面
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $version = $_POST['version'];
    $announcement = $_POST['announcement'];
    $updateLink = $_POST['update_link'];
    $currentTime = date("Y-m-d H:i:s"); // 获取当前时间

    // 连接数据库的代码
    $servername = "localhost";
    $username = "ymaz";
    $password = "ymaz";

    $conn = new mysqli($servername, $username, $password);

    // 检查连接是否成功
    if ($conn->connect_error) {
        die("数据库连接失败: " . $conn->connect_error);
    }

    // 选择数据库
    $conn->select_db("ymaz");
    // 准备插入语句，包括当前时间
    $sql = "INSERT INTO gx (version, announcement, update_link, update_time) VALUES ('$version', '$announcement', '$updateLink', '$currentTime')";

    if ($conn->query($sql) === TRUE) {
        $successMessage = "更新成功！";

        // 记录版本号到update.txt文本中
        $file = fopen("update.txt", "a");
        fwrite($file,$version);
        fclose($file);
    } else {
        $successMessage = "更新失败: " . $conn->error;
    }

    $conn->close();
    // 关闭数据库连接的代码
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>更新公告</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .button {
            padding: 10px 20px;
            background-color: #4CAF50;
            border: none;
            color: white;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            background-color: #e6f4ea;
            color: #008000;
            border: 1px solid #b3d4bc;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>更新公告</h2>
        <?php if (isset($successMessage)) { ?>
            <div class="message"><?php echo $successMessage; ?></div>
        <?php } ?>
        <form action="update.php" method="post">
            <div class="form-group">
                <label for="version">版本号（限制5字以内）:</label>
                <input type="text" id="version" name="version" maxlength="5" required>
            </div>
            <div class="form-group">
                <label for="announcement">功能公告（100个字以内）:</label>
                <input type="text" id="announcement" name="announcement" maxlength="100" required>
            </div>
            <div class="form-group">
                <label for="update_link">更新链接（50字以内）:</label>
                <input type="text" id="update_link" name="update_link" maxlength="50" required>
            </div>
            <button type="submit" class="button">更新</button>
        </form>
    </div>
</body>
</html>
