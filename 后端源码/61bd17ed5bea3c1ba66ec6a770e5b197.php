<?php
// 数据库连接信息
$servername = "localhost"; // 数据库服务器名称
$username = "dxhz"; // 数据库用户名
$password = "dxhz"; // 数据库密码
$dbname = "dxhz"; // 您的数据库名称

// 链接数组
$urls = array(
  );

// 创建数据库连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接是否成功
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 检查文件是否存在
if (file_exists("dx.txt")) {
  // 从文件中读取链接
  $urls = file("dx.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  
  // 创建数据库连接
  $conn = new mysqli($servername, $username, $password, $dbname);

  // 检查连接是否成功
  if ($conn->connect_error) {
      die("数据库连接失败: " . $conn->connect_error);
  }

  // 循环插入链接到数据库
  foreach ($urls as $url) {
      // 准备 SQL 插入语句
      $sql = "INSERT INTO LetSMS_api (url, post, cookie, status, date) VALUES ('$url', '', '', 1, NOW())";

      if ($conn->query($sql) === TRUE) {
          echo "链接 '$url' 成功记录到数据库中<br>";
      } else {
          echo "插入链接 '$url' 失败: " . $conn->error . "<br>";
      }
  }

  // 关闭数据库连接
  $conn->close();
} else {
  echo "dx.txt文件不存在";
}
