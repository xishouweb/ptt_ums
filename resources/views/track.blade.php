<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">  
<html xmlns="http://www.w3.org/1999/xhtml">  
<head>  
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />  
<title>已上传数据列表</title>  
<style type="text/css"> 
.sp-grid-import{border-collapse: collapse;width:100%; border:1px solid #E1E6EB; border-left:none;}
.sp-grid-import thead th{line-height:20px;padding:8px 12px; border-bottom:1px solid #E1E6EB; border-left:1px solid #E1E6EB; white-space: nowrap; text-align:center; font-weight:normal !important;letter-spacing:1px;}
.sp-grid-import tbody td{text-align: center;line-height:20px;padding:8px 10px;font-size:13px;border-bottom:1px solid #E1E6EB; border-left:1px solid #E1E6EB;}
</style>  
</head>  
  
<body>  
	<table class="sp-grid-import">
<thead>
    <tr>
        <th>ID</th>
		<th>数据源</th>
        <th>txhash</th>
		<th>bc_id</th>
		<th>UID</th>
		<th>时间</th>
    </tr>
</thead>
<tbody>
	@foreach($records as $record)	
    <tr>
        <td>{{$record->id}}</td>
        <td><a href="https://scan.proton.global/tx/{{$record->hx}}" target="_blank">{{$record->hx}}</a></td>
        <td>{{$record->id}} <br/> 从链上查询：<a href="http://v1.proton.global:8888/track/{{$record->id}}" target="_blank">查询</a></td>
        <td>{{$record->content}}</td>
        <td>{{$record->created_at}}</td>
    </tr>
   @endforeach
</tbody>
</table>

</body>  
</html> 
