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
$counterFile = 'counter/counter.txt';
$newCount = file_get_contents($counterFile);
$userCount = $newCount;

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

// 查询注册人数
$count_sql = "SELECT COUNT(*) AS count FROM users";
$count_result = $conn->query($count_sql);

if ($count_result->num_rows > 0) {
    $row = $count_result->fetch_assoc();
    $registered_users = $row["count"];
}

// 获取当前日期和时间
$current_date = date("Y-m-d");

// 获取昨天的日期
$yesterday_date = date("Y-m-d", strtotime("-1 day"));

// 获取当前时间
$current_time = date("H:i:s");

// 检查是否已存在今天的记录
$check_date_sql = "SELECT * FROM users WHERE DATE(reg_date) = ?";
$check_date_stmt = $conn->prepare($check_date_sql);
$check_date_stmt->bind_param("s", $current_date);
$check_date_stmt->execute();
$check_date_result = $check_date_stmt->get_result();

// 如果不存在今天的记录，表示日期已经过了，将以当前日期为条件查询昨天的记录并计算人数
if ($check_date_result->num_rows == 0) {
    $count_sql = "SELECT COUNT(*) AS count FROM users WHERE DATE(reg_date) = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $yesterday_date);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();

    $count_stmt->close();
} else {
    // 如果存在今天的记录，将以今天为条件查询记录并计算人数
    $count_sql = "SELECT COUNT(*) AS count FROM users WHERE DATE(reg_date) = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $current_date);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();

    if ($count_result->num_rows > 0) {
        $row = $count_result->fetch_assoc();
        $registered_usersnew = $row["count"];
        if ($registered_usersnew == 0) {
        }
    } else {
    }

    $count_stmt->close();
}

$check_date_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>YMAZ功能盒管理后台 management background</title>
    <meta name="description" content="YMAZ">
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="icon" type="image/png" href="assets/i/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="assets/i/app-icon72x72@2x.png">
    <meta name="apple-mobile-web-app-title" content="YMAZ功能盒管理后台" />
    <script src="assets/js/echarts.min.js"></script>
    <link rel="stylesheet" href="assets/css/amazeui.min.css" />
    <link rel="stylesheet" href="assets/css/amazeui.datatables.min.css" />
    <link rel="stylesheet" href="assets/css/app.css">
    <script src="assets/js/jquery.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        h1 {
            font-size: 20px;
            color: #fff;
            animation: gradient 2s infinite;
            background-image: linear-gradient(to right, #1e6ebf, #e84a5f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }
    </style>
    <script>
        function updateTime() {
            var now = new Date();
            var year = now.getFullYear();
            var month = ("0" + (now.getMonth() + 1)).slice(-2);
            var day = ("0" + now.getDate()).slice(-2);
            var hour = ("0" + now.getHours()).slice(-2);
            var minute = ("0" + now.getMinutes()).slice(-2);
            var second = ("0" + now.getSeconds()).slice(-2);
            var ampm = (hour >= 12) ? "下午" : "上午";
            hour = (hour >= 12) ? hour - 12 : hour;

            var currentTime = year + "年" + month + "月" + day + "日" + ampm + hour + "点" + minute + "分" + second + "秒";
            document.getElementById("current-time").innerHTML = currentTime;
        }

        setInterval(updateTime, 1000);
    </script>
</head>
<body data-type="index">
    <script src="assets/js/theme.js"></script>
    <div class="am-g tpl-g">
        <header>
            <div class="am-fl tpl-header-logo">
                <a href="javascript:;"><img src="assets/img/logo.png" alt=""></a>
            </div>
                <div class="am-fl tpl-header-search">
                    <form class="tpl-header-search-form" action="javascript:;">
                        <button class="tpl-header-search-btn am-icon-search"></button>
                        <input class="tpl-header-search-box" type="text" placeholder="搜索内容...">
                    </form>
                </div>
                <div class="am-fr tpl-header-navbar">
                    <ul>
                        <li class="am-text-sm tpl-header-navbar-welcome">
                            <a href="javascript:;">欢迎你, <span>YMAZ功能盒管理后台</span> </a>
                        </li>
                            <ul class="am-dropdown-content tpl-dropdown-content">
                                <li class="tpl-dropdown-menu-messages">
                                    <a href="javascript:;" class="tpl-dropdown-menu-messages-item am-cf">
                                        <div class="menu-messages-ico">
                                            <img src="assets/img/user04.png" alt="">
                                        </div>
                                        <div class="menu-messages-time">
                                            3小时前
                                        </div>
                                        <div class="menu-messages-content">
                                            <div class="menu-messages-content-title">
                                                <i class="am-icon-circle-o am-text-success"></i>
                                                <span>夕风色</span>
                                            </div>
                                            <div class="am-text-truncate"> YMAZ功能盒管理后台 </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="tpl-dropdown-menu-messages">
                                    <a href="javascript:;" class="tpl-dropdown-menu-messages-item am-cf">
                                        <div class="menu-messages-ico">
                                            <img src="assets/img/user02.png" alt="">
                                        </div>
                                        <div class="menu-messages-time">
                                            5天前
                                        </div>
                                        <div class="menu-messages-content">
                                            <div class="menu-messages-content-title">
                                                <i class="am-icon-circle-o am-text-warning"></i>
                                                <span>禁言小张</span>
                                            </div>
                                            <div class="am-text-truncate"> 为了能最准确的传达所描述的问题， 建议你在反馈时附上演示，方便我们理解。 </div>
                                            <div class="menu-messages-content-time">2016-09-16 上午 09:23</div>
                                        </div>
                                    </a>
                                <li class="tpl-dropdown-menu-notifications">
                                    <a href="javascript:;" class="tpl-dropdown-menu-notifications-item am-cf">
                                        <i class="am-icon-bell"></i> 进入列表…
                                    </a>
                                </li>
                            </ul>
        </header>
        <div class="tpl-skiner">
            <div class="tpl-skiner-toggle am-icon-cog">
            </div>
            <div class="tpl-skiner-content">
                <div class="tpl-skiner-content-title">
                    选择主题
                </div>
                <div class="tpl-skiner-content-bar">
                    <span class="skiner-color skiner-white" data-color="theme-white"></span>
                    <span class="skiner-color skiner-black" data-color="theme-black"></span>
                </div>
            </div>
        </div>
        <div class="tpl-content-wrapper">
            <div class="container-fluid am-cf">
                <div class="row">
                    <div class="am-u-sm-12 am-u-md-12 am-u-lg-9">
                        <div class="page-header-heading"><span class="am-icon-home page-header-heading-icon"></span> 管理后台 <small>By遇见</small></div>
                        <p class="page-header-description"><?php echo "当前登录cookie:".$cookieValue; ?></br> <h1 id="current-time"></h1></p>
                    </div>
                    <div class="am-u-lg-3 tpl-index-settings-button">
                        <button type="button" class="page-header-button"><span class="am-icon-paint-brush"></span> 设置</button>
                    </div>
                </div>
            </div>
            <div class="row-content am-cf">
                <div class="row  am-cf">
                    <div class="am-u-sm-12 am-u-md-12 am-u-lg-4">
                        <div class="widget am-cf">
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">YMAZ功能盒总注册人数</div>
                                <div class="widget-function am-fr">
                                    <a href="javascript:;"></a>
                                </div>
                            </div>
                            <div class="widget-body am-fr">
                                <div class="am-fl">
                                    <div class="widget-fluctuation-period-text">
                                        <?php echo "软件总注册账号:".$registered_users."位"; ?>
                                    </div>
                                </div>
                                <div class="am-fr am-cf">
                                    <div class="widget-fluctuation-description-amount text-success" am-cf>
                                        <?php echo "+".$registered_usersnew."位" ; ?>
<button class="widget-fluctuation-tpl-btn">
    <a href="member/Am.php">
        <i class="am-icon-calendar"></i>
        账号管理
    </a>
</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
<div class="am-u-sm-12 am-u-md-6 am-u-lg-4">
    <div class="widget widget-primary am-cf">
        <div class="widget-statistic-header">
            YMAZ软件公告
        </div>
        <div class="widget-statistic-body">
            <div class="widget-statistic-value">
                软件内部公告
            </div>
            <div class="widget-statistic-description">
                <a href="http://usdtabcd.cn/login/admin/announcement.php">点我修改</a> <strong>内部</strong> 公告
            </div>
            <span class="widget-statistic-icon am-icon-credit-card-alt"></span>
        </div>
    </div>
</div>

                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-4">
                        <div class="widget widget-purple am-cf">
                            <div class="widget-statistic-header">
                                YMAZ软件版本更新
                            </div>
                            <div class="widget-statistic-body">
                                <div class="widget-statistic-value">
                                    软件版本更新
                                </div>
                                <div class="widget-statistic-description">
                             <a href="http://usdtabcd.cn/login/admin/update.php">点我更新</a> <strong>软件</strong> 版本
                                </div>
                                <span class="widget-statistic-icon am-icon-support"></span>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="am-u-sm-12 am-u-md-12 am-u-lg-4 widget-margin-bottom-lg ">
<div class="tpl-user-card am-text-center widget-body-lg">
    <div class="tpl-user-card-title">
        YMAZ功能盒
    </div>
    <img class="achievement-image" src="assets/img/user07.png" alt="">
    <div class="achievement-description">
        软件累计启动次数：
        <strong><?php echo $userCount; ?></strong>
    </div>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/amazeui.min.js"></script>
    <script src="assets/js/amazeui.datatables.min.js"></script>
    <script src="assets/js/dataTables.responsive.min.js"></script>
    <script src="assets/js/app.js"></script>
    
</body>
</html>