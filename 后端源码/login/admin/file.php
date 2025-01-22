<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = $_GET['admin'];
    $token = isset($_GET['token']) ? $_GET['token'] : '';

    if ($admin == '2662576919') {
        if ($token == '') {
            $randomToken = generateRandomToken();
            echo $randomToken;
        } else {
            $previousToken = getPreviousToken();
            if ($token == $previousToken) {
                $fileContent = getFileContent();
                echo $fileContent;
            } else {
                echo "危险访问已阻止";
            }
        }
    } else {
        echo "参数错误";
    }
}

function generateRandomToken() {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $randomToken = '';
    for ($i = 0; $i < 24; $i++) {
        $randomToken .= $characters[rand(0, strlen($characters) - 1)];
    }
    saveTokenToFile($randomToken);
    return $randomToken;
}

function getPreviousToken() {
    $file = fopen("token.txt", "r");
    $previousToken = fgets($file);
    fclose($file);
    return $previousToken;
}

function saveTokenToFile($token) {
    $file = fopen("token.txt", "w");
    fwrite($file, $token);
    fclose($file);
}

function getFileContent() {
    $fileContent = file_get_contents("file.txt");
    return $fileContent;
}
?>