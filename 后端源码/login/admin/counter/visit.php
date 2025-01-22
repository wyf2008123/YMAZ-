<?php
// 定义存储计数的文件路径
$counterFile = 'counter.txt';

// 检查文件是否存在，若不存在则创建
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, '0');
}

// 读取计数器的当前值
$currentCount = intval(file_get_contents($counterFile));

// 将计数器增加1
$newCount = $currentCount + 1;

// 保存新的计数器值到文件中
file_put_contents($counterFile, strval($newCount));

// 输出计数器的当前值
echo $newCount;
?>
