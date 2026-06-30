<?php 
include_once("../connectdb.php");
$textMaNhanVien = $_GET['manhanvien'];
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
header("Content-type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment ; filename=danhsach.csv");

$output = fopen("php://output","w");
echo "\xEF\xBB\xBF";

fputcsv($output,['manhanvien','tennhanvien','ngaysinh','gioitinh','sodienthoai','email','diachi','machucvu'] , ';');

$sql ="SELECT * FROM nhanvien WHERE manhanvien LIKE '%$textMaNhanVien%'";
$result = mysqli_query($con,$sql);

while ($row = mysqli_fetch_assoc($result)){
    fputcsv($output,
    [
       $row['manhanvien'],
       $row['tennhanvien'],
       $row['ngaysinh'],
       $row['gioitinh'],
       $row['sodienthoai'],
       $row['email'],
       $row['diachi'],
       $row['machucvu']
    ] , ';'
    );
}
fclose($output);
exit;
?>