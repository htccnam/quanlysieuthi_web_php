<?php
include_once("../connectdb.php");
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}


if (isset($_POST['btnSua'])) {
    $textMaNhanVien = $_POST['txtMaNhanVien'];
    $textTenNhanVien = $_POST['txtTenNhanVien'];
    $textNgaySinh = $_POST['txtNgaySinh'];
    $textGioiTinh = $_POST['selectGioiTinh'];
    $textSoDienThoai = $_POST['txtSoDienThoai'];
    $textEmail = $_POST['txtEmail'];
    $textDiaChi = $_POST['txtDiaChi'];
    $textMaChucVu = $_POST['selectChucVu'];



    $sqlCapNhatNhanVien = "UPDATE nhanvien SET tennhanvien ='$textTenNhanVien' , ngaysinh= '$textNgaySinh',gioitinh='$textGioiTinh',sodienthoai='$textSoDienThoai',email='$textEmail',diachi='$textDiaChi',machucvu='$textMaChucVu' WHERE manhanvien='$textMaNhanVien'";
    mysqli_query($con, $sqlCapNhatNhanVien);

    echo "<script> alert('Sửa thành công'); 
        window.location='quanlynhanvien.php';
    </script>";
}

if (isset($_POST['btnThoat'])) {
    echo "<script> window.location='quanlynhanvien.php' </script>";
}

$textMaNhanVien = $_GET['manhanvien'];
$sqlSeclectNhanVien = "SELECT * FROM nhanvien WHERE manhanvien='$textMaNhanVien'";
$resultSelectNhanVien = mysqli_query($con, $sqlSeclectNhanVien);
$rowNhanVien = mysqli_fetch_assoc($resultSelectNhanVien);

$resultSelectChucVu = mysqli_execute_query($con, "select * from chucvu");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>suanhanvien</title>
    <link rel="stylesheet" href="../../css/dinhdang1.css">
</head>

<body>
    <form action="" method="POST" style="width: 500px; height: auto; margin: 50px auto;" class="formnhap">
        <h1>quản lý nhân viên</h1>
        <br>
        <label for="txtMaNhanVien">Mã nhân viên</label>
        <input type="text" name="txtMaNhanVien" class="highlight"  value="<?php echo $rowNhanVien['manhanvien'] ?>"  readonly>

        <br>
        <label for="txtTenNhanVien">Tên nhân viên</label>
        <input type="text" name="txtTenNhanVien" placeholder="Nhập tên nhân viên"
            value="<?php echo $rowNhanVien['tennhanvien'] ?>" required>

        <br>
        <label for="txtNgaySinh">Ngày sinh</label>
        <input type="date" name="txtNgaySinh" value="<?php echo $rowNhanVien['ngaysinh'] ?>" required>

        <br>
        <label for="selectGioiTinh">Giới tính</label>
        <select name="selectGioiTinh" id="">
            <option value="Nam" <?php if ($rowNhanVien['gioitinh'] == "Nam")
                echo "selected" ?>>Nam</option>
                <option value="Nữ" <?php if ($rowNhanVien['gioitinh'] == "Nữ")
                echo "selected" ?>>Nữ</option>
                <option value="Khác" <?php if ($rowNhanVien['gioitinh'] == "Khác")
                echo "selected" ?>>Khác</option>
            </select>

            <br>
            <label for="txtSoDienThoai">Số điện thoại</label>
            <input type="number" name="txtSoDienThoai" placeholder="Nhập số điện thoại"
                value="<?php echo $rowNhanVien['sodienthoai'] ?>" required>
        <br>

        <br>
        <label for="txtEmail">Email</label>
        <input type="text" name="txtEmail" placeholder="Nhập email" value="<?php echo $rowNhanVien['email'] ?>"
            required>
        <br>
        <br>
        <label for="txtDiaChi">Địa chỉ</label>
        <input type="text" name="txtDiaChi" placeholder="Nhập địa chỉ" value="<?php echo $rowNhanVien['diachi'] ?>"
            required>
        <br>
        <label for="selectChucVu">Chức vụ</label>
        <select name="selectChucVu">
            <?php
            if (mysqli_num_rows($resultSelectChucVu) > 0) {
                while ($rowChucVu = mysqli_fetch_assoc($resultSelectChucVu)) {
                    $selected = "";
                    if ($rowChucVu['machucvu'] == $rowNhanVien['machucvu']) {
                        $selected = "selected";
                    }
                    echo "<option value='{$rowChucVu['machucvu']}' $selected >{$rowChucVu['machucvu']} - {$rowChucVu['tenchucvu']}
                        </option>";
                }
            } else {
                echo "<option >không có chức vụ</option>";
            }
            ?>
        </select>
        <br>
        <div class="hang">
            <div class="cot">
                <button name="btnSua" style="width: 60%;" onclick="return confirm('bạn có chắc chắn muốn sửa')"
                    class="buttonThem">Sửa</button>
            </div>
            <div class="cot">
                <button type="submit" style="width: 60%;" name="btnThoat" onclick="return confirm('mọi thao tác sẽ không được lưu lại')"
                    class="buttonTimKiem">Thoát</button>
            </div>
        </div>
    </form>

</body>

</html>