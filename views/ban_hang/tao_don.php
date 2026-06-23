<?php
session_start();
$con = mysqli_connect('localhost', "root", "", "quanlysieuthi");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['old_data'] = [
        'madonhang' => $_POST['madonhang'] ?? '',
        'ngaylap' => $_POST['ngaylap'] ?? date('Y-m-d'),
        'manhanvien' => $_POST['manhanvien'] ?? '',
        'makhachhang' => $_POST['makhachhang'] ?? '',
        'phuongthucban' => $_POST['phuongthucban'] ?? 'Tại quầy',
        'thanhtoan' => $_POST['thanhtoan'] ?? 'Tiền mặt',
        'makhuyenmai' => $_POST['makhuyenmai'] ?? ''
    ];
}

$old = $_SESSION['old_data'] ?? [
    'madonhang' => '', 'ngaylap' => date('Y-m-d'), 'manhanvien' => '', 
    'makhachhang' => '', 'phuongthucban' => 'Tại quầy', 'thanhtoan' => 'Tiền mặt', 'makhuyenmai' => ''
];

if (isset($_POST['action']) && $_POST['action'] == 'add_product') {
    $search = $_POST['ten_sp_search'] ?? '';
    $sl_mua = (int)($_POST['sl_input'] ?? 1);

    if (!empty($search)) {
        $stmt = $con->prepare("SELECT masanpham, tensanpham, giaban FROM sanpham WHERE masanpham = ? OR tensanpham = ? LIMIT 1");
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($sp = $res->fetch_assoc()) {
            $found = false;
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['masanpham'] == $sp['masanpham']) {
                        $item['soluong'] += $sl_mua;
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                $_SESSION['cart'][] = [
                    'masanpham'  => $sp['masanpham'],
                    'tensanpham' => $sp['tensanpham'],
                    'dongia'     => $sp['giaban'],
                    'soluong'    => $sl_mua
                ];
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['btn_save_order'])) {
    $madon = $old['madonhang'];
    if (!empty($_SESSION['cart']) && !empty($madon)) {
        
        $calc_subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $calc_subtotal += $item['soluong'] * $item['dongia'];
        }
        
        $calc_discount = 0;
        if (!empty($old['makhuyenmai'])) {
            $km_q = $con->query("SELECT sotiengiam FROM khuyenmai WHERE makhuyenmai = '{$old['makhuyenmai']}'");
            if ($km_d = $km_q->fetch_assoc()) {
                $calc_discount = $km_d['sotiengiam'];
            }
        }
        $final_total_db = max(0, $calc_subtotal - $calc_discount);

        $check_exist = $con->query("SELECT madonhang FROM donhang WHERE madonhang = '$madon'");
        if ($check_exist->num_rows > 0) {
            echo "<script>alert('Lỗi: Mã đơn hàng này đã tồn tại!');</script>";
        } else {
            $km_val = !empty($old['makhuyenmai']) ? "'" . $old['makhuyenmai'] . "'" : "NULL";

            $sql_dh = "INSERT INTO donhang (madonhang, makhachhang, manhanvien, makhuyenmai, ngaylap, phuongthucban, thanhtoan, tongtien) 
                       VALUES ('$madon', '{$old['makhachhang']}', '{$old['manhanvien']}', $km_val, '{$old['ngaylap']}', '{$old['phuongthucban']}', '{$old['thanhtoan']}', '$final_total_db')";

            if ($con->query($sql_dh)) {
                $cleaned_cart = [];
                foreach ($_SESSION['cart'] as $item) {
                    $id_sp = $item['masanpham'];
                    if (isset($cleaned_cart[$id_sp])) {
                        $cleaned_cart[$id_sp]['soluong'] += $item['soluong'];
                    } else { $cleaned_cart[$id_sp] = $item; }
                }

                foreach ($cleaned_cart as $item) {
                    $ma = $item['masanpham'];
                    $sl = $item['soluong'];
                    $gia = $item['dongia'];
                    $tt_item = $sl * $gia;

                    $sql_ct = "INSERT INTO chitietdonhang (madonhang, masanpham, tensanpham, soluong, dongia, thanhtien) 
                               VALUES ('$madon', '$ma', '{$item['tensanpham']}', '$sl', '$gia', '$tt_item')";
                    $con->query($sql_ct);

                    $sql_update_kho = "UPDATE sanpham SET soluong = soluong - $sl WHERE masanpham = '$ma'";
                    $con->query($sql_update_kho);
                }
                // tính điểm tích lũy
                if (!empty($old_kh)) {
                
                    $diem_tich_luy_them = floor($final_total / 10000); 
                    $diem_hien_tai_them = floor($final_total / 1000);

        
                    $sql_update_diem = "UPDATE khachhang 
                                        SET diemtichluy = diemtichluy + $diem_tich_luy_them,
                                            diemhientai = diemhientai + $diem_hien_tai_them
                                        WHERE makhachhang = '$old_kh'";
                    
                    $con->query($sql_update_diem);
                }

                unset($_SESSION['cart']);
                unset($_SESSION['old_data']);
                echo "<script>alert('Lưu đơn hàng và trừ kho thành công!'); window.location.href='thong_tin.php';</script>";
                exit();
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $idx = $_GET['delete'];
    if (isset($_SESSION['cart'][$idx])) {
        unset($_SESSION['cart'][$idx]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Đơn Hàng</title>
    <style>
        :root { --primary: #3498db; --success: #2ecc71; --dark: #34495e; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 20px; color: var(--dark); }
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-size: 13px; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { border: none; padding: 12px; border-radius: 5px; color: white; cursor: pointer; font-weight: bold; }
        .btn-add { background: var(--primary); width: 100px; }
        .btn-save { background: var(--success); width: 100%; margin-top: 15px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: var(--dark); color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-area { margin-top: 20px; text-align: right; border-top: 2px solid #eee; padding-top: 10px; }
        .discount-text { color: #e74c3c; font-weight: bold; }
    </style>
</head>
<body>

    <form method="POST" id="mainForm">
        <div class="grid">
            <div class="card">
                <h3 style="margin-top:0">📝 Thông tin hóa đơn</h3>
                <div class="form-group"><label>Mã đơn hàng</label><input name="madonhang" value="<?= htmlspecialchars($old['madonhang']) ?>" required></div>
                <div class="form-group"><label>Ngày lập</label><input type="date" name="ngaylap" value="<?= $old['ngaylap'] ?>"></div>

                <div class="form-group">
                    <label>Nhân viên</label>
                    <select name="manhanvien">
                        <?php $nvs = $con->query("SELECT manhanvien, tennhanvien FROM nhanvien");
                        while ($nv = $nvs->fetch_assoc()): ?>
                            <option value="<?= $nv['manhanvien'] ?>" <?= ($old['manhanvien'] == $nv['manhanvien'] ? 'selected' : '') ?>><?= $nv['tennhanvien'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Khách hàng</label>
                    <select name="makhachhang">
                        <?php $khs = $con->query("SELECT makhachhang, tenkhachhang FROM khachhang");
                        while ($kh = $khs->fetch_assoc()): ?>
                            <option value="<?= $kh['makhachhang'] ?>" <?= ($old['makhachhang'] == $kh['makhachhang'] ? 'selected' : '') ?>><?= $kh['tenkhachhang'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mã khuyến mại</label>
                    <select name="makhuyenmai" onchange="this.form.submit();">
                        <option value="">-- Không có --</option>
                        <?php
                        $kms = $con->query("SELECT makhuyenmai, sotiengiam FROM khuyenmai");
                        while ($km = $kms->fetch_assoc()): ?>
                            <option value="<?= $km['makhuyenmai'] ?>" <?= ($old['makhuyenmai'] == $km['makhuyenmai'] ? 'selected' : '') ?>>
                                <?= $km['makhuyenmai'] ?> (-<?= number_format($km['sotiengiam']) ?>đ)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Phương thức & Thanh toán</label>
                    <div style="display: flex; gap: 5px;">
                        <select name="phuongthucban">
                            <option value="Tại quầy" <?= ($old['phuongthucban'] == 'Tại quầy' ? 'selected' : '') ?>>Tại quầy</option>
                            <option value="Online" <?= ($old['phuongthucban'] == 'Online' ? 'selected' : '') ?>>Online</option>
                        </select>
                        <select name="thanhtoan">
                            <option value="Tiền mặt" <?= ($old['thanhtoan'] == 'Tiền mặt' ? 'selected' : '') ?>>Tiền mặt</option>
                            <option value="Chuyển khoản" <?= ($old['thanhtoan'] == 'Chuyển khoản' ? 'selected' : '') ?>>Chuyển khoản</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="btn_save_order" class="btn btn-save">LƯU HÓA ĐƠN</button>
            </div>

            <div class="card">
                <h3 style="margin-top:0">📦 Chi tiết đơn hàng</h3>
                <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <label>Sản phẩm (Mã hoặc Tên)</label>
                        <input name="ten_sp_search" list="list_sp" autocomplete="off">
                        <datalist id="list_sp">
                            <?php $sps = $con->query("SELECT masanpham, tensanpham FROM sanpham");
                            while ($s = $sps->fetch_assoc()) echo "<option value='{$s['masanpham']}'>{$s['tensanpham']}</option>"; ?>
                        </datalist>
                    </div>
                    <div style="flex: 0.5;">
                        <label>Số lượng</label>
                        <input type="number" name="sl_input" value="1" min="1" style="text-align:center">
                    </div>
                    <input type="hidden" name="action" id="action_type" value="">
                    <button type="submit" onclick="document.getElementById('action_type').value='add_product'" class="btn btn-add">THÊM</button>
                </div>

                <table>
                    <thead>
                        <tr><th>Mã SP</th><th>Tên sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        if (!empty($_SESSION['cart'])):
                            foreach ($_SESSION['cart'] as $idx => $item):
                                $tt = $item['soluong'] * $item['dongia'];
                                $subtotal += $tt;
                        ?>
                                <tr>
                                    <td><?= $item['masanpham'] ?></td>
                                    <td><b><?= $item['tensanpham'] ?></b></td>
                                    <td><?= $item['soluong'] ?></td>
                                    <td><?= number_format($item['dongia']) ?></td>
                                    <td><?= number_format($tt) ?></td>
                                    <td><a href="?delete=<?= $idx ?>" style="color:red; text-decoration:none">✖</a></td>
                                </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" style="text-align:center; color:#ccc">Chưa có sản phẩm nào</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="total-area">
                    <p>Tạm tính: <b><?= number_format($subtotal) ?> đ</b></p>
                    <?php
                    $final_discount = 0;
                    if (!empty($old['makhuyenmai'])) {
                        $km_q = $con->query("SELECT sotiengiam FROM khuyenmai WHERE makhuyenmai = '{$old['makhuyenmai']}'");
                        if ($km_d = $km_q->fetch_assoc()) {
                            $final_discount = $km_d['sotiengiam'];
                            echo "<p class='discount-text'>Khuyến mãi: -" . number_format($final_discount) . " đ</p>";
                        }
                    }
                    $grand_total_display = max(0, $subtotal - $final_discount);
                    ?>
                    <h2 style="color:var(--primary); margin: 5px 0;">Tổng: <?= number_format($grand_total_display) ?> đ</h2>
                </div>
            </div>
        </div>
    </form>
</body>
</html>