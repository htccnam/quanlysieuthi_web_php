<?php
include_once("../connectdb.php");
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
if (isset($_POST['btnThem'])) {
    $txtManhacungcap = $_POST['txtManhacungcap'];
    $txtTennhacungcap = $_POST['txtTennhacungcap'];
    $txtLoaihinh = $_POST['txtLoaihinh'];
    $txtEmail = $_POST['txtEmail'];
    $textSodienthoai = $_POST['txtSodienthoai'];
    $txtDiachi = $_POST['txtDiachi'];


    $textCheckma = mysqli_query($con, "SELECT manhacungcap FROM nhacungcap WHERE manhacungcap = '$txtManhacungcap'");
    if (mysqli_num_rows($textCheckma) > 0) {
        echo "<script> alert('Mã nhà cung cấp $txtManhacungcap đã tồn tại');</script>";
    } else {
        $sqlInsert = "INSERT INTO nhacungcap VALUES ('$txtManhacungcap','$txtTennhacungcap','$txtLoaihinh','$txtEmail','$textSodienthoai',' $txtDiachi')";

        if (mysqli_query($con, $sqlInsert)) {
            echo "<script> alert('Thêm thành công'); window.location='quanlynhacungcap.php'; </script>";
        } else {
            echo "<script> alert('Lỗi thêm: " . mysqli_error($con) . "'); </script>";
        }
    }
}

if (isset($_GET['btnXoa'])) {
    $maXoa = $_GET['manhacungcap'];
    $checkSP = mysqli_query($con,"SELECT * FROM sanpham WHERE manhacungcap='$maXoa'");
    if(mysqli_num_rows($checkSP)>0){
        echo " <script> 
            alert('Cảnh báo: Nhà cung cấp này đang cung cấp hàng hóa trong kho. Bạn phải xóa sản phẩm trước!');
            window.loction='quanlynhacungcap.php';
        </script> ";
    }else{
    $sqlDelete = "DELETE FROM nhacungcap WHERE manhacungcap = '$maXoa'";
    mysqli_query($con, $sqlDelete);
    echo "<script> alert('Xóa thành công'); window.location='quanlynhacungcap.php'; </script>";
}
}

$txtTimKiem = "";
if (isset($_POST['btnTimKiem'])) {
    $txtTimKiem = $_POST['txtTimKiem'];
}

$sqlTimKiem = "SELECT * FROM nhacungcap WHERE manhacungcap LIKE '%$txtTimKiem%' OR tennhacungcap LIKE '%$txtTimKiem%'";
$resultTimKiem = mysqli_query($con, $sqlTimKiem);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà cung cấp</title>
    <link rel="stylesheet" href="../../css/sanpham.css">
</head>

<body>

    <div class="main-container">

        <div class="card left-panel">
            <h2>➕ Thêm Nhà Cung Cấp</h2>
            <form action="" method="post">
                <div class="form-group">
                    <label>Mã NCC</label>
                    <input type="text" name="txtManhacungcap" placeholder="VD: NCC01" required>
                </div>
                <div class="form-group">
                    <label>Tên nhà cung cấp</label>
                    <input type="text" name="txtTennhacungcap" placeholder="Nhập tên..." required>
                </div>
                <div class="form-group">
                    <label>Loại hình</label>
                    <input type="text" name="txtLoaihinh" placeholder="Doanh nghiệp/Cá nhân" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="text" name="txtEmail" placeholder="example@gmail.com" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="txtSodienthoai" placeholder="Nhập SĐT" required>
                </div>
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="txtDiachi" placeholder="Nhập địa chỉ" required>
                </div>
                <button name="btnThem" class="btn btn-add">Lưu Nhà Cung Cấp</button>
            </form>
        </div>

        <div class="card right-panel">
            <h2>🏭 Danh Sách Nhà Cung Cấp</h2>

            <form action="" method="post" class="search-box">
                <input type="text" name="txtTimKiem" placeholder="Tìm mã hoặc tên..."
                    value="<?php echo $txtTimKiem; ?>">
                <button name="btnTimKiem" class="btn btn-search">🔍 Tìm kiếm</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Mã NCC</th>
                        <th>Tên NCC</th>
                        <th>Loại hình</th>
                        <th>Email</th>
                        <th>SĐT</th>
                        <th>Địa chỉ</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultTimKiem && mysqli_num_rows($resultTimKiem) > 0) {
                        while ($row = mysqli_fetch_assoc($resultTimKiem)) { ?>
                            <tr>
                                <td><b><?php echo $row['manhacungcap']; ?></b></td>
                                <td><?php echo $row['tennhacungcap']; ?></td>
                                <td><?php echo $row['loaihinh']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['sodienthoai']; ?></td>
                                <td><?php echo $row['diachi']; ?></td>
                                <td>
                                    <a href="suanhacungcap.php?manhacungcap=<?php echo $row['manhacungcap']; ?>"
                                        class="action-link edit">✏️ Sửa</a>
                                    <a href="?btnXoa=1&manhacungcap=<?php echo $row['manhacungcap']; ?>"
                                        class="action-link delete"
                                        onclick="return confirm('Bạn có chắc muốn xóa <?php echo $row['tennhacungcap']; ?>?')">🗑️
                                        Xóa</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 20px; color: #888;'>Không tìm thấy dữ liệu</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>