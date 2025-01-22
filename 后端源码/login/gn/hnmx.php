<?php

$name = $_GET['xm'] ?? '';
$idcard = $_GET['sfz'] ?? '';
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
$headers = array(
    'Host: zyb.micro-recruit.com',
    'Connection: keep-alive',
    'Content-Type: application/json',
    'charset: utf-8',
    'micro-key: micro@2020',
    'User-Agent: Mozilla/5.0 (Linux; Android 9; LIO-AN00 Build/PQ3B.190801.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/91.0.4472.114 Mobile Safari/537.36 MMWEBID/2142 MicroMessenger/8.0.43.2480(0x28002B51) NetType/WIFI Language/zh_CN ABI/arm64 MiniProgramEnv/android',
    'micro-secret: micro@2020@xingdiankeji',
    'Referer: https://servicewechat.com/wx8cb112aefec5aca1/113/page-frame.html'
);

$data = array(
    'motherCert' => $idcard,
    'motherName' => $name
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://zyb.micro-recruit.com/zyb-app/mo/wxp/order/getwxpmailarchparents');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$response = json_decode($response, true);
echo($response);
if ($response['result']) {
    $mother_name = $response['result'][0]['motherName'];
    $mother_id = $response['result'][0]['motherCertNo'];
    $mother_phone = $response['result'][0]['mobile'];
    $mother_address = $response['result'][0]['motherResidenceAddress'];

    $father_name = $response['result'][0]['fatherName'];
    $father_id = $response['result'][0]['fatherCertNo'];
    $father_phone = $response['result'][0]['mobile'];
    $father_address = $response['result'][0]['fatherResidenceAddress'];

    $child_hospital = $response['result'][0]['childrens'][0]['birthAddr'];
    $child_birth_address = $response['result'][0]['childrens'][0]['birthAddress'];
    $child_home_address = $response['result'][0]['childrens'][0]['haddress'];
    $child_residence_address = $response['result'][0]['childrens'][0]['raddress'];

    $mother_admission_time = $response['result'][0]['admissionTime'];
    $child_admission_time = $response['result'][0]['childrens'][0]['deliDate'];

    echo "母亲信息:\n姓名: {$mother_name}\n身份证: {$mother_id}\n手机号: {$mother_phone}\n地址: {$mother_address}\n\n父亲信息:\n姓名: {$father_name}\n身份证: {$father_id}\n手机号: {$father_phone}\n地址: {$father_address}\n\n儿童信息:\n出生医院: {$child_hospital}\n出生地址: {$child_birth_address}\n家庭地址: {$child_home_address}\n居住地址: {$child_residence_address}\n\n入院时间:\n母亲入院时间: {$mother_admission_time}\n儿童入院时间: {$child_admission_time}";
} else {
    echo '';
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
