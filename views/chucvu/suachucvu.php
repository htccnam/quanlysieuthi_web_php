<?php

include_once("../connectdb.php");
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
if (isset($_POST['btnSua'])) {
    $textMaChucVu = $_POST['txtMaChucVu'];
    $textTenChucVu = $_POST['txtTenChucVu'];

    try {
        $sqlUpdate = "update chucvu set tenchucvu='$textTenChucVu' where machucvu='$textMaChucVu'";
        mysqli_execute_query($con, $sqlUpdate);
        echo "<script> alert('Sửa thành công');
                        window.location='quanlychucvu.php'; 
                        </script>";

    } catch (Exception $e) {
        $msg = $e->getMessage();
        echo "<script> alert('lỗi sửa chức vụ: " . addslashes($msg) . "') </script>";
    }

}

if (isset($_POST['btnThoat'])) {
    echo "<script> window.location='quanlychucvu.php' </script>";
}

$textMaChucVu = $_GET['machucvu'];
$sqlSelectChucVu = "select * from chucvu where machucvu='$textMaChucVu'";
$result = mysqli_execute_query($con, $sqlSelectChucVu);
$row = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/dinhdang1.css">
    <title>Suachucvu</title>
</head>

<body>
    <form action="" method="post" class="formnhap" style="width: 500px; height: 400px; margin: 50px auto;">
        <h1>Sửa chức vụ</h1>
        <br>
        <label for="txtMaChucVu">Mã chức vụ</label>
        <input type="text" name="txtMaChucVu" placeholder="Nhập mã chức vụ" class="highlight"
            value="<?php echo $row['machucvu'] ?>" readonly>
        <br>
        <label for="txtTenChucVu">Tên chức vụ</label>
        <input type="text" name="txtTenChucVu" placeholder="Nhập tên chức vụ" value="<?php echo $row['tenchucvu'] ?>"
            required>
        <br>
        <br>
        <br>
        <div class="hang">
            <div class="cot">
                <button name="btnSua" style="width: 40%;" class="buttonThem" onclick="return confirm('Xác nhận thông tin sửa')">Sửa</button>
            </div>
            <div class="cot">
                <button name="btnThoat" style="width: 40%;" class="buttonTimKiem"
                    onclick="return confirm('Những thao tác hiện tại sẽ mất')">Thoát</button>
            </div>
        </div>


    </form>
</body>

</html>