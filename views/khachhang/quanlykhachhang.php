<?php

require_once '../connectdb.php'; 
if (isset($con)) {
    $conn = $con;
} else {
    die("Lỗi kết nối");
}

$message = "";
$msg_type = "";
$edit_customer = null;

$errors = []; // Mảng chứa các lỗi cụ thể của từng trường

if (isset($_POST['btn_save'])) {
    $makh = trim($_POST['makhachhang']); 
    $tenkh = trim($_POST['tenkhachhang']);
    $gioitinh = $_POST['gioitinh'];
    $ngaysinh = $_POST['ngaysinh'];
    $email = trim($_POST['email']);
    $sdt = trim($_POST['sdt']);
    $diachi = trim($_POST['diachi']);
    
    // 1. KIỂM TRA ĐỘC LẬP TỪNG TRƯỜNG NHẬP LIỆU
    if (empty($makh)) {
        $errors['makh'] = "Vui lòng nhập Mã khách hàng!";
    }
    
    if (empty($tenkh)) {
        $errors['tenkh'] = "Vui lòng nhập Họ tên khách hàng!";
    }
    
    if (empty($sdt)) {
        $errors['sdt'] = "Vui lòng nhập Số điện thoại!";
    } elseif (!preg_match('/^[0-9]+$/', $sdt)) {
        $errors['sdt'] = "Số điện thoại không hợp lệ!";
    }

    // 2. KIỂM TRA TRÙNG LẶP DATABASE (Chỉ check nếu trường đó đã được nhập)
    if (isset($_POST['old_makh']) && !empty($_POST['old_makh'])) {
        // --- LUỒNG SỬA ---
        $old_makh = $_POST['old_makh'];
        if (!empty($sdt)) {
            $check_sdt = mysqli_query($conn, "SELECT * FROM khachhang WHERE sdt='$sdt' AND makhachhang != '$old_makh'");
            if (mysqli_num_rows($check_sdt) > 0) {
                $errors['sdt'] = "SĐT đã tồn tại!";
            }
        }
    } else {
        // --- LUỒNG THÊM MỚI ---
        if (!empty($makh)) {
            $check_makh = mysqli_query($conn, "SELECT * FROM khachhang WHERE makhachhang='$makh'");
            if (mysqli_num_rows($check_makh) > 0) {
                $errors['makh'] = "Mã KH '$makh' đã tồn tại!";
            }
        }
        if (!empty($sdt)) {
            $check_sdt = mysqli_query($conn, "SELECT * FROM khachhang WHERE sdt='$sdt'");
            if (mysqli_num_rows($check_sdt) > 0) {
                $errors['sdt'] = "SĐT đã tồn tại!";
            }
        }
    }

    // 3. TIẾN HÀNH LƯU NẾU KHÔNG CÓ BẤT KỲ LỖI NÀO
    if (empty($errors)) {
        if (isset($_POST['old_makh']) && !empty($_POST['old_makh'])) {
            $sql = "UPDATE khachhang SET 
                    tenkhachhang='$tenkh', gioitinh='$gioitinh', ngaysinh='$ngaysinh',
                    email='$email', sdt ='$sdt', diachi='$diachi' 
                    WHERE makhachhang = '$old_makh'";
            if (mysqli_query($conn, $sql)) { 
                $message = "Cập nhật thành công!"; $msg_type = "success"; 
            } else { 
                $message = "Lỗi SQL: " . mysqli_error($conn); $msg_type = "error"; 
            }
        } else {
            $sql = "INSERT INTO khachhang (makhachhang, tenkhachhang, gioitinh, ngaysinh, diachi, email, sdt, diemtichluy, diemhientai) 
                    VALUES ('$makh', '$tenkh', '$gioitinh', '$ngaysinh', '$diachi', '$email', '$sdt', 0, 0)";
            if (mysqli_query($conn, $sql)) { 
                $message = "Thêm mới thành công!"; $msg_type = "success"; 
            } else { 
                $message = "Lỗi SQL: " . mysqli_error($conn); $msg_type = "error"; 
            }
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Bước 1: Kiểm tra điểm tích lũy (Logic nghiệp vụ theo ý tưởng của bạn)
    $check_diem_query = mysqli_query($conn, "SELECT diemtichluy FROM khachhang WHERE makhachhang = '$code'");
    $khachhang_info = mysqli_fetch_assoc($check_diem_query);

    if ($khachhang_info && $khachhang_info['diemtichluy'] > 0) {
        $message = "Khách hàng hiện đang có đơn hàng không thể xóa!"; 
        $msg_type = "error";
    } else {
        // Bước 2: Tiến hành xóa và BẮT LỖI Database (Bảo vệ hệ thống không bị sập)
        try {
            $sql = "DELETE FROM khachhang WHERE makhachhang = '$code'";
            if (mysqli_query($conn, $sql)) { 
                $message = "Đã xóa khách hàng có mã $code!"; 
                $msg_type = "success"; 
            }
        } catch (mysqli_sql_exception $e) {
            // Mã lỗi 1451 là mã đặc trưng của lỗi vi phạm khóa ngoại (Foreign Key)
            if ($e->getCode() == 1451) {
                $message = "Khách hàng hiện đang có đơn hàng không thể xóa!";
            } else {
                $message = "Lỗi khi xóa: " . $e->getMessage();
            }
            $msg_type = "error";
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['code'])) {
    $code = $_GET['code'];
    $result = mysqli_query($conn, "SELECT * FROM khachhang WHERE makhachhang = '$code'");
    $edit_customer = mysqli_fetch_assoc($result);
}


$keyword = "";
$sql_list = "SELECT * FROM khachhang";
if (isset($_GET['tukhoa']) && !empty($_GET['tukhoa'])) {
    $keyword = $_GET['tukhoa'];
    $sql_list .= " WHERE makhachhang LIKE '%$keyword%' OR tenkhachhang LIKE '%$keyword%'";
}
$sql_list .= " ORDER BY makhachhang ASC"; 
$list_customers = mysqli_query($conn, $sql_list);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách Hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand"><i class="fa-solid fa-cart-shopping"></i> Quản lý Khách Hàng</div>
        <div style="font-size: 14px; color: #666;"> <a href="#" style="color: var(--primary-color);"></a></div>
    </div>


    <div class="container block-layout">

        <div class="card">
            <div class="card-header">
                <span><i class="fa-solid <?php echo ($edit_customer) ? 'fa-pen-to-square' : 'fa-user-plus'; ?>"></i> <?php echo ($edit_customer) ? "Cập Nhật" : "Thêm Mới"; ?></span>
            </div>
            <div class="card-body">
                <form action="quanlykhachhang.php" method="POST">
                    <input type="hidden" name="old_makh" value="<?php echo ($edit_customer) ? $edit_customer['makhachhang'] : ''; ?>">
                    
                    <div class="form-group">
                    <label class="form-label">Mã KH <span style="color:red">*</span></label>
                    <input type="text" name="makhachhang" class="form-control" placeholder="VD: KH001" 
                    value="<?php echo ($edit_customer) ? $edit_customer['makhachhang'] : (isset($_POST['makhachhang']) ? $_POST['makhachhang'] : ''); ?>"
                    <?php echo ($edit_customer) ? 'readonly' : ''; ?>>
    <!-- Hiện lỗi Mã KH -->
                     <?php if(isset($errors['makh'])) echo "<small style='color: red; font-style: italic; margin-top: 5px; display: block;'><i class='fa-solid fa-circle-exclamation'></i> ".$errors['makh']."</small>"; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Họ Tên <span style="color:red">*</span></label>
                        <input type="text" name="tenkhachhang" class="form-control" value="<?php echo ($edit_customer) ? $edit_customer['tenkhachhang'] : ''; ?> ">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label class="form-label">Giới Tính</label>
                            <select name="gioitinh" class="form-control">
                                <option value="Nam" <?php echo ($edit_customer && $edit_customer['gioitinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                <option value="Nữ" <?php echo ($edit_customer && $edit_customer['gioitinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ngày Sinh</label>
                            <input type="date" name="ngaysinh" class="form-control" value="<?php echo ($edit_customer) ? $edit_customer['ngaysinh'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
    <label class="form-label">SĐT <span style="color:red">*</span></label>
    <input type="text" name="sdt" class="form-control" placeholder="VD: 0912345678"
           value="<?php echo ($edit_customer) ? $edit_customer['sdt'] : (isset($_POST['sdt']) ? $_POST['sdt'] : ''); ?>">
    <!-- Hiện lỗi SĐT -->
    <?php if(isset($errors['sdt'])) echo "<small style='color: red; font-style: italic; margin-top: 5px; display: block;'><i class='fa-solid fa-circle-exclamation'></i> ".$errors['sdt']."</small>"; ?>
</div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo ($edit_customer) ? $edit_customer['email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Địa Chỉ</label>
                        <input type="text" name="diachi" class="form-control" value="<?php echo ($edit_customer) ? $edit_customer['diachi'] : ''; ?>">
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" name="btn_save" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> <?php echo ($edit_customer) ? "Lưu Thay Đổi" : "Lưu Thông Tin"; ?>
                        </button>
                        <?php if($edit_customer): ?>
                            <a href="quanlykhachhang.php" class="btn btn-secondary" style="margin-top: 10px; width: 100%; box-sizing: border-box;"><i class="fa-solid fa-ban"></i> Hủy Bỏ</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>


        <div class="card">
            <div class="card-header">
                <span><i class="fa-solid fa-list"></i> Danh Sách Khách Hàng</span>
                <span class="badge badge-points"><?php echo mysqli_num_rows($list_customers); ?> Khách</span>
            </div>
            <div class="card-body">
                <?php if ($message != ""): ?>
                    <div class="alert <?php echo ($msg_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="quanlykhachhang.php" method="GET" style="display:flex; gap:10px; margin-bottom:20px;">
                    <input type="text" name="tukhoa" class="form-control" placeholder="Tìm kiếm..." value="<?php echo $keyword; ?>">
                    <button class="btn btn-primary" style="width: auto;"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã KH</th>
                                <th>Họ Tên</th>
                                <th>Giới Tính</th>
                                <th>Liên Hệ</th>
                                <th>Tích Lũy</th>
                                <th style="text-align: right;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($list_customers) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($list_customers)): ?>
                                <tr>
                                    <td><span style="font-weight: bold; color: var(--primary-color);"><?php echo $row['makhachhang']; ?></span></td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo $row['tenkhachhang']; ?></div>
                                        <div style="font-size: 12px; color: #888;"><?php echo date('d/m/Y', strtotime($row['ngaysinh'])); ?></div>
                                    </td>
                                    <td><?php echo $row['gioitinh']; ?></td>
                                    <td>
                                        <div style="font-size: 13px;"><i class="fa-solid fa-phone"></i> <?php echo $row['sdt']; ?></div>
                                    </td>
                                    <td><span class="badge badge-points"><?php echo number_format($row['diemtichluy']); ?></span></td>
                                    <td style="text-align: right;">
                                        <a href="quanlykhachhang.php?action=edit&code=<?php echo $row['makhachhang']; ?>" class="btn-action-edit"><i class="fa-solid fa-pen"></i></a>
                                        <a href="quanlykhachhang.php?action=delete&code=<?php echo $row['makhachhang']; ?>" onclick="return confirm('Xóa khách hàng <?php echo $row['makhachhang']; ?>?');" class="btn-action-delete"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">Không có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>