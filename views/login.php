<?php

include_once("./connectdb.php");
session_start();
if (isset($_POST['btnDangnhap'])) {
    $textTaiKhoan = $_POST['txtTaiKhoan'];
    $textMatKhau = $_POST['txtMatKhau'];
    $selectTaiKhoan = "SELECT * FROM taikhoan WHERE taikhoan = '$textTaiKhoan' and matkhau = '$textMatKhau'";
    $resultSelectTaiKhoan = mysqli_query($con, $selectTaiKhoan);

    if ((mysqli_num_rows($resultSelectTaiKhoan) > 0)) {
        $row =mysqli_fetch_assoc($resultSelectTaiKhoan);
        $_SESSION['taikhoan']=$row['taikhoan'];
        header("Location:menu_admin.php");
        exit;
    } else {
        echo "<script> alert ('tài khoản hoặc mật khẩu không chính xác'); </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <!-- <link rel="stylesheet" href="../css/themsuaxoatimkiem.css"> -->
    <link rel="stylesheet" href="../css/dinhdang1.css">
</head>

<body>
    <form action="" method="POST" style="height: 300px;width: 500px; margin-top: 100px; margin: 50px auto;"
        class="formnhap">
        <label for="txtTaiKhoan">Tài khoản</label>
        <input type="text" name="txtTaiKhoan" placeholder="nhập tài khoản" required>
        <br>

        <label for="txtMatKhau">Mật khẩu</label>
        <input type="text" name="txtMatKhau" placeholder="nhập mật khẩu" required>
        <br>

        <button name="btnDangnhap" style="width: 100%;" class="buttonThem">đăng nhập</button>
    </form>

</body>

</html>