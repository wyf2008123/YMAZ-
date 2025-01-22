<?php
function queryAddress($idNumber) {
    // 检查身份证号码格式是否正确
    if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
        echo('身份证号码格式不正确');
        exit();
    }

    // 获取身份证号码中的前6位编码
    $provinceCode = substr($idNumber, 0, 6);

// 读取省份和对应的地址信息
$addressData = array();
$cityFile = './city.txt';
if (file_exists($cityFile)) {
    // 读取文件内容
    $cityData = file_get_contents($cityFile);

    // 移除花括号和单引号，以逗号分隔键值对
    $cityData = str_replace(array('{', '}', "'"), '', $cityData);

    // 将数据分割成键值对数组
    $cityPairs = explode(',', $cityData);

    // 遍历键值对数组，生成省份和地址的关联数组
    foreach ($cityPairs as $pair) {
        // 分割键值对为键和值
        $pair = explode(':', $pair);
        $key = trim($pair[0]);
        $value = trim($pair[1]);
        $addressData[$key] = $value;
    }
} else {
    echo '省份地址信息文件不存在';
    exit();
}


    // 检查省份编码是否存在于地址数据中
    if (isset($addressData[$provinceCode])) {
        return $addressData[$provinceCode];
    } else {
        // 如果找不到对应的省份编码，则尝试从省级行政区编码中获取
        $provinceCode = substr($provinceCode, 0, 2);
        if (isset($addressData[$provinceCode])) {
            return $addressData[$provinceCode];
        } else {
            return '未知省份';
        }
    }
}


function getBirthdateFromID($idNumber) {
    $birthdate = '';

    if (strlen($idNumber) === 18) {
        $year = substr($idNumber, 6, 4);
        $month = substr($idNumber, 10, 2);
        $day = substr($idNumber, 12, 2);
        $birthdate = $year . '-' . $month . '-' . $day;
    } elseif (strlen($idNumber) === 15) {
        $year = '19' . substr($idNumber, 6, 2);
        $month = substr($idNumber, 8, 2);
        $day = substr($idNumber, 10, 2);
        $birthdate = $year . '-' . $month . '-' . $day;
    }

    return $birthdate;
}

function getGenderFromID($idNumber) {
    $gender = '';

    // 获取身份证号码的倒数第二位数字
    $digit = intval(substr($idNumber, -2, 1));

    // 判断奇偶性来确定性别
    if ($digit % 2 === 0) {
        $gender = '女';
    } else {
        $gender = '男';
    }

    return $gender;
}

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
            // 如果存在账号且会员状态是1，则执行原有的图片生成代码
            $name = $_GET['name'];
            $idNumber = $_GET['idNumber'];
            $address = queryAddress($idNumber);
            $birthdate = getBirthdateFromID($idNumber);
            $gender = getGenderFromID($idNumber);
            $imagePath = 'plc.jpg';

            // 创建画布
            $image = imagecreatefromjpeg($imagePath);

            // 设置字体颜色
            $fontColor = imagecolorallocate($image, 0, 0, 0); // 使用黑色字体

            // 设置字体路径和大小
            $fontPath = './plc.ttf';
            $fontSize = 38;

            // 获取图片宽度和高度
            $textX = 480;
            $textY = 680;
            $textX2 = 730;
            $textY2 = 530;
            $textX3 = 730;
            $textY3 = 760;
            $textX4 = 600;
            $textY4 = 448;
            $textX5 = 600;
            $textY5 = 365;
            // 在指定位置写入身份证号码
            imagettftext($image, $fontSize, 0, $textX, $textY, $fontColor, $fontPath, $idNumber);

            // 在图片中心位置写入出生日期
            imagettftext($image, $fontSize, 0, $textX2, $textY2, $fontColor, $fontPath, $birthdate);

            // 在图片左上角位置写入地址
            imagettftext($image, $fontSize, 0, $textX3, $textY3, $fontColor, $fontPath, $address);

            // 在图片左上角位置写入性别
            imagettftext($image, $fontSize, 0, $textX4, $textY4, $fontColor, $fontPath, $gender);
            
            // 在图片左上角位置写入姓名
            imagettftext($image, $fontSize, 0, $textX5, $textY5, $fontColor, $fontPath, $name);

            // 输出图片
            header('Content-Type: image/jpeg');
            imagejpeg($image);

            // 获取要保存的文件名
            $filename = 'plc_' . $idNumber . '.jpg';

            // 保存图片到指定目录
            $imageSavePath = './plc/' . $filename;
            imagejpeg($image, $imageSavePath);

            // 释放内存
            imagedestroy($image);

            // 输出成功信息
            echo "图片已保存到：" . $imageSavePath;

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

