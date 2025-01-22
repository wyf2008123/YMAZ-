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

// 处理发布公告的逻辑
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $announcement = $_POST["announcement"];

    // 将公告内容保存到文件中
    $filename = "Notice/Notice.txt"; // 替换为实际的文件路径和文件名

    if (file_put_contents($filename, $announcement) !== false) {
        echo "公告内容已成功保存到文件！";
    } else {
        echo "保存文件时发生错误！";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>发布公告</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .announcement-form {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-top: 0;
        }
        
        form {
            margin-top: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        textarea {
            width: 100%;
            height: 100px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        
        span {
            font-size: 12px;
            color: #999;
        }
        
        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin-top: 20px;
        }
        
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="announcement-form">
            <h1>发布公告</h1>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateForm()">
                <label for="announcement">请输入公告内容（最多100个字符）：</label>
                <textarea id="announcement" name="announcement" maxlength="100"></textarea>
                <span>公告长度限制100位</span>
                <input type="submit" value="发布">
            </form>
        </div>
    </div>
    <script>
        // 页面加载时从localStorage中获取上次发布的内容并显示在公告编辑框中
        window.addEventListener('DOMContentLoaded', function() {
            var announcement = localStorage.getItem('announcement');
            if (announcement) {
                document.getElementById('announcement').value = announcement;
            }
        });
        
        function validateForm() {
            var announcement = document.getElementById("announcement").value.trim();
            
            if (announcement === "") {
                alert("公告内容不能为空！");
                return false;
            }
            
            // 将公告内容保存到localStorage中
            localStorage.setItem('announcement', announcement);

            // 将公告内容保存到Notice文件夹中的Notice.txt文件
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // 提示发布成功
                    alert("发布成功！自动保存中...");

                    // 清空localStorage中的公告内容
                    localStorage.removeItem('announcement');
                }
            };
            xhr.send("announcement=" + encodeURIComponent(announcement));

            return false; // 防止表单提交刷新页面
        }
    </script>
</body>
</html>

