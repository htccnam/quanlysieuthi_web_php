<?php 
        include_once("../connectdb.php");
        if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}
    $dsLoai = mysqli_query($con, "SELECT * FROM loaihang");
    $dsNCC = mysqli_query($con, "SELECT * FROM nhacungcap");

 // 3. XỬ LÝ CHỨC NĂNG THÊM MỚI (ĐÃ BỔ SUNG RÀNG BUỘC)
    if(isset($_POST['btnThem'])){
        // Lấy dữ liệu
        $maSP = $_POST['txtMaSP'];
        $tenSP = $_POST['txtTenSP'];
        $maLoai = $_POST['slMaLoai'];
        $maNCC = $_POST['slMaNCC'];
        $xuatXu = $_POST['txtXuatXu'];
        
        // Ép kiểu số để so sánh cho chuẩn
        $soLuong = (int)$_POST['txtSoLuong']; 
        $giaNhap = (float)$_POST['txtGiaNhap'];
        $giaBan = (float)$_POST['txtGiaBan'];
        
        $ngaySX = $_POST['txtNgaySX'];
        $hanSD = $_POST['txtHanSD'];
        $tinhTrang = $_POST['slTinhTrang']; 
        $dvt = $_POST['txtDVT'];

        if ($soLuong < 0) {
            echo "<script>alert('Lỗi: Số lượng không được là số âm!');</script>";
        } 
        elseif ($giaNhap <= 0 || $giaBan <= 0) {
            echo "<script>alert('Lỗi: Giá nhập và Giá bán phải lớn hơn 0!');</script>";
        }

        elseif ($giaBan < $giaNhap) {
            echo "<script>alert('Cảnh báo: Giá bán ($giaBan) đang thấp hơn Giá nhập ($giaNhap). Bạn sẽ bị lỗ vốn!');</script>";
        }
        elseif (strtotime($ngaySX) > strtotime($hanSD)) {
             echo "<script>alert('Lỗi logic: Ngày sản xuất không được lớn hơn Hạn sử dụng!');</script>";
        }
        else {     
            $check = mysqli_query($con, "SELECT masanpham FROM sanpham WHERE masanpham='$maSP'");
            if(mysqli_num_rows($check) > 0){
                echo "<script>alert('Mã sản phẩm $maSP đã tồn tại!');</script>";
            } else {
                $sqlInsert = "INSERT INTO sanpham 
                (masanpham, tensanpham, maloai, manhacungcap, xuatxu, soluong, ngaysanxuat, hansudung, tinhtrang, gianhap, giaban, donvitinh) 
                VALUES 
                ('$maSP', '$tenSP', '$maLoai', '$maNCC', '$xuatXu', '$soLuong', '$ngaySX', '$hanSD', '$tinhTrang', '$giaNhap', '$giaBan', '$dvt')";
                
                if(mysqli_query($con, $sqlInsert)){
                    echo "<script>alert('Thêm thành công!'); window.location='quanlysanpham.php';</script>";
                } else {
                    echo "<script>alert('Lỗi thêm: " . mysqli_error($con) . "');</script>";
                }
            }
        }
    }
    if(isset($_GET['btnXoa'])){
        $maXoa = $_GET['masanpham'];
        mysqli_query($con, "DELETE FROM sanpham WHERE masanpham='$maXoa'");
        echo "<script>alert('Xóa thành công'); window.location='quanlysanpham.php';</script>";
    }
    $txtTimKiem = "";
    if(isset($_POST['btnTimKiem'])){
        $txtTimKiem = $_POST['txtTimKiem'];
    }
    $sqlTimKiem = "SELECT s.*, l.tenloai, n.tennhacungcap 
                   FROM sanpham s
                   INNER JOIN loaihang l ON s.maloai = l.maloai
                   INNER JOIN nhacungcap n ON s.manhacungcap = n.manhacungcap
                   WHERE s.masanpham LIKE '%$txtTimKiem%' OR s.tensanpham LIKE '%$txtTimKiem%'";
    
    $result = mysqli_query($con, $sqlTimKiem);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Sản phẩm</title>
    <link rel="stylesheet" href="../../css/sanpham.css">
</head>
<body>

    <div class="main-container">
        
        <div class="card left-panel" style="max-width: 400px;"> <h2>➕ Thêm Sản Phẩm Mới</h2>
            <form action="" method="post">
                
                <div class="form-group">
                    <label>Thông tin cơ bản</label>
                    <div class="form-row">
                        <div class="form-col" style="flex: 1;">
                            <input type="text" name="txtMaSP" placeholder="Mã SP" required>
                        </div>
                        <div class="form-col" style="flex: 2;">
                            <input type="text" name="txtTenSP" placeholder="Tên sản phẩm" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <label>Loại hàng</label>
                            <select name="slMaLoai">
                                <?php while($row = mysqli_fetch_assoc($dsLoai)){ ?>
                                    <option value="<?php echo $row['maloai']; ?>">
                                        <?php echo $row['tenloai'];?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-col">
                            <label>Nhà cung cấp</label>
                            <select name="slMaNCC">
                                <?php while($row = mysqli_fetch_assoc($dsNCC)){ ?>
                                    <option value="<?php echo $row['manhacungcap']; ?>">
                                        <?php echo $row['tennhacungcap']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <input type="text" name="txtXuatXu" placeholder="Xuất xứ" required>
                        </div>
                        <div class="form-col">
                            <select name="slTinhTrang">
                                <option value="Tốt">Tốt</option>
                                <option value="Đã hết hạn" style="color:red;">Đã hết hạn</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Thời gian</label>
                    <div class="form-row">
                        <div class="form-col">
                            <span style="font-size:12px">NSX:</span>
                            <input type="date" name="txtNgaySX" required style="padding: 8px;">
                        </div>
                        <div class="form-col">
                            <span style="font-size:12px">HSD:</span>
                            <input type="date" name="txtHanSD" required style="padding: 8px;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Giá cả (VNĐ)</label>
                    <div class="form-row">
                        <div class="form-col">
                            <input type="number" name="txtGiaNhap" placeholder="Giá nhập" required>
                        </div>
                        <div class="form-col">
                            <input type="number" name="txtGiaBan" placeholder="Giá bán" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row">
                        <div class="form-col">
                            <input type="number" name="txtSoLuong" placeholder="Số lượng" required>
                        </div>
                        <div class="form-col">
                            <input type="text" name="txtDVT" placeholder="Đơn vị" required>
                        </div>
                    </div>
                </div>

                <button name="btnThem" class="btn btn-add">Lưu Sản Phẩm</button>
            </form>
        </div>
        <div class="card right-panel">
            <h2>📦 Kho Sản Phẩm</h2>
            
            <form action="" method="post" class="search-box">
                <input type="text" name="txtTimKiem" placeholder="Tìm tên hoặc mã SP..." value="<?php echo $txtTimKiem; ?>">
                <button name="btnTimKiem" class="btn btn-search">🔍 Tìm</button>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Loại hàng</th>
                            <th>Nhà CC</th>
                            <th>Xuất xứ</th>
                            <th>SL</th>
                            <th>ĐVT</th>
                            <th>NSX</th>
                            <th>HSD</th>
                            <th>Giá nhập</th>
                            <th>Giá bán</th>
                            <th>Tình trạng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)){ ?>
                                <tr>
                                    <td><b><?php echo $row['masanpham']; ?></b></td>
                                    <td style="font-weight: 600; color: #2c3e50;">
                                        <?php echo $row['tensanpham']; ?>
                                    </td>
                                    <td><?php echo $row['tenloai']; ?></td>
                                    <td><?php echo $row['tennhacungcap']; ?></td>
                                    <td><?php echo $row['xuatxu']; ?></td>
                                    <td style="text-align: center;"><?php echo $row['soluong']; ?></td>
                                    <td><?php echo $row['donvitinh']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($row['ngaysanxuat'])); ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($row['hansudung'])); ?></td>
                                    <td><?php echo number_format($row['gianhap']); ?></td>
                                    <td style="color: #d35400; font-weight:bold;">
                                        <?php echo number_format($row['giaban']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if($row['tinhtrang'] == 'Tốt') 
                                                echo "<span style='color:green; background:#e8f8f5; padding: 3px 8px; border-radius:10px; font-size:12px;'>Tốt</span>";
                                            else 
                                                echo "<span style='color:red; background:#fdedec; padding: 3px 8px; border-radius:10px; font-size:12px;'>Hết hạn</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <a href="suasanpham.php?masanpham=<?php echo $row['masanpham']; ?>" class="action-link edit" title="Sửa">✏️</a>
                                        <a href="?btnXoa=1&masanpham=<?php echo $row['masanpham']; ?>" 
                                           class="action-link delete" title="Xóa"
                                           onclick="return confirm('Bạn có chắc muốn xóa <?php echo $row['tensanpham']; ?>?')">🗑️</a>
                                    </td>
                                </tr>
                            <?php }
                        } else {
                            echo "<tr><td colspan='13' style='text-align:center; padding: 20px; color: #888;'>Chưa có sản phẩm nào</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div> </div>
        



    </div>
</body>
</html>