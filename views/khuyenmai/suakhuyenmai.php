<?php
include_once("../connectdb.php");
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
if (isset($_POST['btnSua'])) {
    $maKhuyenMai = $_POST['txtMaKhuyenMai'];
    $tenKhuyenMai = $_POST['txtTenKhuyenMai'];
    $moTa = $_POST['txtMoTa'];
    $soTienGiam = $_POST['txtSoTienGiam'];
    $ngayTao = $_POST['txtNgayTao'];

    $sqlUpdate = "update khuyenmai set 
        tenkhuyenmai='$tenKhuyenMai',
        mota='$moTa',
        sotiengiam='$soTienGiam',
        ngaytao='$ngayTao'
        where makhuyenmai='$maKhuyenMai'";

    mysqli_execute_query($con, $sqlUpdate);
    echo "<script>
            alert('Sửa thành công');
            window.location='quanlykhuyenmai.php';
          </script>";
}

if (isset($_POST['btnThoat'])) {
    echo "<script>window.location='quanlykhuyenmai.php'</script>";
}

$maKhuyenMai = $_GET['makhuyenmai'];
$sqlSelect = "select * from khuyenmai where makhuyenmai='$maKhuyenMai'";
$result = mysqli_execute_query($con, $sqlSelect);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Suakhuyenmai</title>
    <link rel="stylesheet" href="../../css/dinhdang1.css">
</head>

<body>
    <form action="" method="post" class="formnhap" style="width: 600px ; height: auto; margin: 50px auto;">
        <h1>Sửa khuyến mãi</h1>

        <label>Mã khuyến mãi</label>
        <input type="text" name="txtMaKhuyenMai" value="<?php echo $row['makhuyenmai'] ?>" class="highlight" readonly>

        <label>Tên khuyến mãi</label>
        <input type="text" name="txtTenKhuyenMai" value="<?php echo $row['tenkhuyenmai'] ?>" required>

        <label>Mô tả</label>
        <input type="text" name="txtMoTa" value="<?php echo $row['mota'] ?>">

        <label>Số tiền giảm</label>
        <input type="number" name="txtSoTienGiam" value="<?php echo $row['sotiengiam'] ?>" required>

        <label>Ngày tạo</label>
        <input type="date" name="txtNgayTao" value="<?php echo $row['ngaytao'] ?>" required>

        <div class="hang">
            <div class="cot"><button name="btnSua" class="buttonThem" style="width: 50%;"
                    onclick="return confirm('Xác nhận sửa?')">Sửa</button></div>
            <div class="cot"><button name="btnThoat" class="buttonTimKiem" style="width: 50%;"
                    onclick="return confirm('Thoát không lưu?')">Thoát</button></div>
        </div>


    </form>
</body>

</html>