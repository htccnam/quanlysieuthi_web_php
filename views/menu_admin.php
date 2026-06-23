<?php 
include("check_dangnhap.php");
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ SIÊU THỊ</title>

    <!-- link phông chữ -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/menu.css">

    <!-- add phông chữ -->
    <style>
        html,
        body {
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>

<body>

    <div>
        <header>
            <div class="logo">
                <a href="logo.php" target="contentFrame">QUẢN LÝ SIÊU THỊ</a>
            </div>
            <ul class="menu">
                <li><a href="logo.php" target="contentFrame">📦 Hàng hóa & Kho ▼</a>
                    <ul>
                        <li><a href="sanpham/quanlysanpham.php" target="contentFrame">Danh sách sản phẩm</a></li>
                        <li><a href="phanloaihang/quanlyloaihang.php" target="contentFrame">Phân loại hàng</a></li>
                        <li><a href="nhacungcap/quanlynhacungcap.php" target="contentFrame">Nhà cung cấp</a></li>
                    </ul>
                </li>

                <li><a href="logo.php" target="contentFrame">🛒 Bán hàng ▼</a>
                    <ul>
                        <li><a href="ban_hang/tao_don.php" target="contentFrame">Tạo đơn mới</a></li>
                        <li><a href="ban_hang/thong_tin.php" target="contentFrame">Chi tiết đơn hàng</a></li>
                    </ul>
                </li>
                

                
                <!--Khách hàng-->
                <li><a href="#">👥 Khách Hàng ▼</a>
                    <ul>
                        <li><a href="khachhang/quanlykhachhang.php" target="contentFrame">Quản Lý Khách Hàng</a></li>
                        <li><a href="khachhang/xephangthanhvien.php" target="contentFrame">Xếp Hạng Thành Viên</a></li>
                        <li><a href="khachhang/quydoidiem.php" target="contentFrame">Quy Đổi Điểm</a></li>
                    </ul>
                </li>

                <li><a href="khuyenmai/quanlykhuyenmai.php" target="contentFrame">📰 Khuyến mại</a></li>

                <li><a href="logo.php" target="contentFrame">👔 Nhân sự ▼</a>
                    <ul>
                        <li><a href="nhanvien/quanlynhanvien.php" target="contentFrame">Quản lý nhân viên</a></li>
                        <li><a href="chucvu/quanlychucvu.php" target="contentFrame">Quản lý chức vụ</a></li>
                    </ul>
                </li>

                <li>
                    <button onclick="if(confirm('Bạn có chắc muốn đăng xuất?')){window.location='logout.php';}"
                        style="color: #ff6b6b; font-weight: bold;">
                        Đăng xuất ➜
                    </button>   
                </li>
            </ul>
        </header>
    </div>

    <!-- THẺ DIV ĐỂ CHỨA NỘI DUNG -->
    <div style="height: 750px; ">
        <iframe name="contentFrame" style="width:100%; height:100%; border:none;">
        </iframe>
    </div>

</body>

</html>