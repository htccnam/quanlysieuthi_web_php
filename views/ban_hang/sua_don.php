<?php
$con = mysqli_connect('localhost', "root", "", "quanlysieuthi");
$id = $_GET['id'];

if (isset($_POST['btn_save'])) {
    $pt = $_POST['phuongthuc'];
    $tt = $_POST['thanhtoan'];
    $grand_total = 0;
    $error_message = '';

    // Lấy số lượng cũ của từng sản phẩm trong đơn hàng
    $oldQtys = [];
    $oldResult = $con->query("SELECT masanpham, soluong FROM chitietdonhang WHERE madonhang = '$id'");
    while ($oldRow = $oldResult->fetch_assoc()) {
        $oldQtys[$oldRow['masanpham']] = $oldRow['soluong'];
    }

    // Kiểm tra tồn kho trước khi cập nhật
    foreach ($_POST['qty'] as $masp => $newQty) {
        $newQty = (int)$newQty;
        $oldQty = isset($oldQtys[$masp]) ? (int)$oldQtys[$masp] : 0;

        // Lấy tồn kho hiện tại của sản phẩm
        $stockResult = $con->query("SELECT soluong FROM sanpham WHERE masanpham = '$masp'");
        if ($stockResult && $stockRow = $stockResult->fetch_assoc()) {
            $currentStock = (int)$stockRow['soluong'];

            // Số lượng thực tế có thể sử dụng = tồn kho hiện tại + số lượng cũ (vì kho đã bị trừ oldQty từ trước)
            $availableStock = $currentStock + $oldQty;

            if ($newQty > $availableStock) {
                // Lấy tên sản phẩm để thông báo
                $tenSP = $con->query("SELECT tensanpham FROM sanpham WHERE masanpham = '$masp'")->fetch_assoc()['tensanpham'] ?? $masp;
                $error_message = "Sản phẩm \"$tenSP\" không đủ hàng! Hiện chỉ có thể đặt tối đa $availableStock sản phẩm.";
                break; // dừng kiểm tra, không lưu
            }
        } else {
            $error_message = "Sản phẩm mã $masp không tồn tại trong kho!";
            break;
        }
    }

    // Nếu có lỗi, dừng và hiển thị thông báo (không lưu)
    if (!empty($error_message)) {
        echo "<script>alert('$error_message');</script>";
    } else {
        // Tiến hành cập nhật
        foreach ($_POST['qty'] as $masp => $newQty) {
            $price = $_POST['price'][$masp];
            $total = $newQty * $price;
            $grand_total += $total;

            // Lấy số lượng cũ của sản phẩm này
            $oldQty = isset($oldQtys[$masp]) ? (int)$oldQtys[$masp] : 0;

            // Cập nhật chi tiết đơn hàng
            $con->query("UPDATE chitietdonhang SET soluong='$newQty', thanhtien='$total' WHERE madonhang='$id' AND masanpham='$masp'");

            // Cập nhật lại kho: hoàn trả số lượng cũ, trừ số lượng mới
            $con->query("UPDATE sanpham SET soluong = soluong + $oldQty - $newQty WHERE masanpham = '$masp'");
        }

        // Cập nhật thông tin đơn hàng
        $con->query("UPDATE donhang SET phuongthucban='$pt', thanhtoan='$tt', tongtien='$grand_total' WHERE madonhang='$id'");

        echo "<script>alert('Cập nhật thành công!'); window.location.href='thong_tin.php';</script>";
        exit();
    }
}

$order = $con->query("SELECT * FROM donhang WHERE madonhang = '$id'")->fetch_assoc();
$details = $con->query("SELECT * FROM chitietdonhang WHERE madonhang = '$id'");
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Sửa đơn hàng</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
            border-top: 5px solid #f1c40f;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
        }

        label {
            font-weight: bold;
            color: #34495e;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th,
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #34495e;
            color: white;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }

        .btn-save {
            background-color: #27ae60;
        }

        .btn-save:hover {
            background-color: #2ecc71;
        }

        .btn-cancel {
            background-color: #e74c3c;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>CHỈNH SỬA ĐƠN HÀNG: <?= $id ?></h2>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Phương thức bán:</label>
                    <input type="text" name="phuongthuc" value="<?= $order['phuongthucban'] ?>">
                </div>
                <div class="form-group">
                    <label>Hình thức thanh toán:</label>
                    <input type="text" name="thanhtoan" value="<?= $order['thanhtoan'] ?>">
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="100">Số lượng</th>
                        <th>Đơn giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $details->fetch_assoc()): ?>
                        <tr>
                            <td><?= $item['tensanpham'] ?></td>
                            <td>
                                <input type="number" name="qty[<?= $item['masanpham'] ?>]" value="<?= $item['soluong'] ?>" min="1">
                                <input type="hidden" name="price[<?= $item['masanpham'] ?>]" value="<?= $item['dongia'] ?>">
                            </td>
                            <td><?= number_format($item['dongia']) ?> đ</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div style="margin-top: 25px; text-align: right;">
                <a href="thong_tin.php" class="btn btn-cancel">Hủy bỏ</a>
                <button type="submit" name="btn_save" class="btn btn-save">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</body>

</html>