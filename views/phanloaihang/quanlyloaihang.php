<?php
include_once("../connectdb.php");
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
if (isset($_POST['btnThem'])) {
    $textMaLoai = $_POST['txtMaLoai'];
    $textTenLoai = $_POST['txtTenLoai'];

    $check = mysqli_query($con, "SELECT maloai FROM loaihang WHERE maloai='$textMaLoai'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Mã loại hàng đã tồn tại!');</script>";
    } else {
        $sqlInsert = "INSERT INTO loaihang (maloai, tenloai) VALUES ('$textMaLoai','$textTenLoai')";
        if (mysqli_query($con, $sqlInsert)) {
            echo "<script>alert('Thêm thành công'); window.location='quanlyloaihang.php';</script>";
        } else {
            echo "<script>alert('Lỗi thêm: " . mysqli_error($con) . "');</script>";
        }
    }
}
    if (isset($_GET['btnXoa'])) {
        $maXoa = $_GET['maloai']; 
        $checkSP = mysqli_query($con, "SELECT * FROM sanpham WHERE maloai = '$maXoa'");

        if (mysqli_num_rows($checkSP) > 0) {
            echo "<script>
                alert('Cảnh báo: Loại hàng này đang có sản phẩm. Bạn phải xóa sản phẩm trước!');
                window.location='quanlyloaihang.php';
            </script>";
        } else {
            $sqlDelete = "DELETE FROM loaihang WHERE maloai = '$maXoa'";
            if(mysqli_query($con, $sqlDelete)){
                echo "<script>alert('Xóa thành công'); window.location='quanlyloaihang.php';</script>";
            } else {
                echo "<script>alert('Lỗi xóa: ".mysqli_error($con)."');</script>";
            }
        }
    }

$txtTimKiem = "";
if (isset($_POST['btnTimKiem'])) {
    $txtTimKiem = $_POST['txtTimKiem'];
}

$sqlSelect = "SELECT * FROM loaihang WHERE maloai LIKE '%$txtTimKiem%' OR tenloai LIKE '%$txtTimKiem%'";
$result = mysqli_query($con, $sqlSelect);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý Loại hàng</title>

    <link rel="stylesheet" href="../../css/sanpham.css">

</head>

<body>

    <div class="main-container">

        <div class="card left-panel">
            <h2>➕ Thêm Loại Hàng</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Mã loại hàng</label>
                    <input type="text" name="txtMaLoai" placeholder="VD: LH01" required>
                </div>
                <div class="form-group">
                    <label>Tên loại hàng</label>
                    <input type="text" name="txtTenLoai" placeholder="Nhập tên..." required>
                </div>
                <button name="btnThem" class="btn btn-add">Lưu Loại Hàng</button>
            </form>
        </div>

        <div class="card right-panel">
            <h2>📦 Danh Sách Loại Hàng</h2>

            <form action="" method="POST" class="search-box">
                <input type="text" name="txtTimKiem" placeholder="Tìm mã hoặc tên loại..."
                    value="<?php echo $txtTimKiem; ?>">
                <button name="btnTimKiem" class="btn btn-search">🔍 Tìm</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th width="20%">Mã loại</th>
                        <th width="50%">Tên loại hàng</th>
                        <th width="30%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><b><?php echo $row['maloai']; ?></b></td>
                                <td><?php echo $row['tenloai']; ?></td>
                                <td>
                                    <a href="sualoaihang.php?maloai=<?php echo $row['maloai']; ?>" class="action-link edit">✏️
                                        Sửa</a>

                                    <a href="?btnXoa=1&maloai=<?php echo $row['maloai']; ?>" class="action-link delete"
                                        onclick="return confirm('Bạn chắc chắn muốn xóa <?php echo $row['tenloai']; ?>?')">🗑️
                                        Xóa</a>
                                </td>
                            </tr>
                        <?php }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; color:#888; padding:30px;'>Không tìm thấy dữ liệu</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>