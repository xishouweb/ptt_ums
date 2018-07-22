<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

	<link rel="stylesheet" type="text/css" href="main.css">

	<script src="./node_modules/web3/dist/web3.min.js"></script>

<style type="text/css">
body {
    background-color:#F0F0F0;
    padding: 2em;
	font-family: 'Raleway','Source Sans Pro', 'Arial';
}
.container {
    width: 50%;
    margin: 0 auto;
}
label {
    display:block;
    margin-bottom:10px;
}
input {
    padding:10px;
    width: 50%;
    margin-bottom: 1em;
}
button {
    margin: 2em 0;
    padding: 1em 4em;
    display:block;
}

#info {
    padding:1em;
    background-color:#fff;
    margin: 1em 0;
}
</style>

</head>
<body>
	<div class="container">

        <h1>数据测试</h1>

		<form action="/api/vendor/data" method="post">
		<label for="name" class="col-lg-2 control-label">apikey</label>
		<input id="name" name="apikey" type="text" value="woaixuexi">
		<label for="name" class="col-lg-2 control-label">公钥</label>
		<input id="name" name="address" type="text" value="0x0428e150f72797bdfef7135b11b0953639494f15">
		<label for="name" class="col-lg-2 control-label">数据源</label>
		<input id="name" name="user_application_id" type="text" value="1">
		<label for="name" class="col-lg-2 control-label">原数据</label>
	<textarea style="margin: 0px;
    width: 361px;
    height: 145px" name="content">
{"source": "0x0428e150f72797bdfef7135b11b0953639494f15", "phone": "17184033615","gender": "男","age": "18岁-25岁", "user_address": "北京市", "industry": "房地产", "hobby": "游戏", "interest": "娱乐", "model": "小米", "data-summary": "11111111"}
</textarea>

		<button id="button">上传</button>
		</form>

    </div>

	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>

    <script>
       // Our future code here..
	   //     </script>
	   
	        </body>
	        </html>
