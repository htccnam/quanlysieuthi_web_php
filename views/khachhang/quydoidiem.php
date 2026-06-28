<?php
require_once '../connectdb.php';
if (isset($con))
    { $conn = $con;
 }else {
    die("Lỗi kết nối");
}

$gift_list = [
    1 => [
        'name' => 'Túi Đeo Chéo Thời Trang', 
        'point' => 1200, 
        'image' => '<img src="img/tui_deo_cheo_1.jpg" alt="Túi đeo chéo">', 
        'desc' => 'Chống nước, nhiều ngăn tiện lợi'
    ],
    2 => [
        'name' => 'Nồi chiên không dầu Elmich', 
        'point' => 2500, 
        'image' => '<img src="img/EDA-0827.jpg" alt="Nồi chiên không dầu">',
        'desc' => 'Tiện lợi, chống dính, đa năng'
    ],
    3 => [
        'name' => 'Thùng Mì Indomie', 
        'point' => 800, 
        'image' => '<img src="img/S61a6256fd8ee4ddf97d6ba0b406ffa466.jpg" alt="Vị đặc biệt xào khô">',
        'desc' => 'Ngon miệng, bổ rẻ'
    ],
    4 => [
        'name' => 'Mũ Bảo Hiểm 3/4', 
        'point' => 1000, 
        'image' => '<img src="img/mu-bao-hiem-3-4-khong-kinh-1.jpg" alt="Mũ bảo hiểm">',
        'desc' => 'An toàn, kính chống tia UV'
    ],
    5 => [
        'name' => 'Balo Laptop Cao Cấp', 
        'point' => 5000, 
        'image' => '<img src="img/balo-bange-Main-7216-1.jpg" alt="Balo laptop">',
        'desc' => 'Chống sốc, bảo hành 1 năm'
    ]
];

$message = "";
$msg_type = "";


if (isset($_POST['btn_exchange'])) {
    // Dùng toán tử ?? để tránh lỗi "Undefined index" khi không có dữ liệu gửi lên
    $code_khach = $_POST['customer_code'] ?? '';
    $id_qua = $_POST['gift_id'] ?? '';
    
    // TÁCH BIỆT CÁC TRƯỜNG HỢP KIỂM TRA RỖNG (PHỤC VỤ TC_04, TC_05, TC_06)
    if (empty($code_khach) && empty($id_qua)) {
        // Trường hợp trống cả 2 (Bypass toàn bộ)
        $message = "Vui lòng chọn khách hàng và quà tặng!";
        $msg_type = "error";
    } elseif (empty($code_khach)) {
        // Trường hợp chỉ chọn Quà, quên chọn Khách hàng (TC_05)
        $message = "Vui lòng chọn khách hàng!";
        $msg_type = "error";
    } elseif (empty($id_qua)) {
        // Trường hợp chỉ chọn Khách hàng, quên chọn Quà (TC_04)
        $message = "Vui lòng lựa chọn món quà!";
        $msg_type = "error";
    } else {

        $query = mysqli_query($conn, "SELECT tenkhachhang, diemhientai FROM khachhang WHERE makhachhang = '$code_khach'");
        $cust = mysqli_fetch_assoc($query);
        
        $diem_co = intval($cust['diemhientai']);
        $diem_qua = $gift_list[$id_qua]['point'];
        $ten_qua = $gift_list[$id_qua]['name'];
        
        if ($diem_co < $diem_qua) {
            $message = "Khách <b>{$cust['tenkhachhang']}</b> còn thiếu " . ($diem_qua - $diem_co) . " điểm để đổi <b>$ten_qua</b>.";
            $msg_type = "error";
        } else {
            $diem_con_lai = $diem_co - $diem_qua;

            $sql_update = "UPDATE khachhang SET diemhientai = $diem_con_lai WHERE makhachhang = '$code_khach'";
            
            if (mysqli_query($conn, $sql_update)) {

                $sql_history = "INSERT INTO lichsudoiqua (makhachhang, tenqua, diemtru, thoigian) VALUES ('$code_khach', '$ten_qua', $diem_qua, NOW())";
                mysqli_query($conn, $sql_history);

                $message = "<div style='text-align:center;'>
                                <i class='fa-solid fa-gift' style='font-size: 24px; margin-bottom: 5px; display:block;'></i>
                                Đổi thành công món quà: <br>
                                <b style='font-size:18px; color:#155724;'>$ten_qua</b> <br>
                                <span style='font-size:14px; color:#555;'>Số dư mới: <b>" . number_format($diem_con_lai) . "</b> điểm</span>
                            </div>";
                $msg_type = "success";
            } else {
                $message = "Lỗi SQL: " . mysqli_error($conn);
                $msg_type = "error";
            }
        }
    }
}

$list_customers = mysqli_query($conn, "SELECT * FROM khachhang ORDER BY tenkhachhang ASC");


$sql_history_list = "SELECT h.*, k.tenkhachhang 
                     FROM lichsudoiqua h 
                     JOIN khachhang k ON h.makhachhang = k.makhachhang 
                     ORDER BY h.thoigian DESC";
$history_result = mysqli_query($conn, $sql_history_list);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đổi Điểm Nhận Quà</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

</head>
<body>

    <div class="navbar">
        <div class="navbar-brand"><i class="fa-solid fa-gift"></i> Đổi Điểm Nhận Quà</div>
        <div><a href="quanlykhachhang.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Về Quản Lý KH</a></div>
    </div>

    <div class="container">
        <form action="quydoidiem.php" method="POST">
            
            <div class="gift-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="color: var(--primary-color); border-left: 5px solid var(--primary-color); padding-left: 10px; margin: 0;">
                        Chọn Quà Tặng
                    </h3>
                    <button type="button" class="btn btn-secondary" onclick="openHistory()" style="font-size: 13px; background-color: #17a2b8;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử đổi quà
                    </button>
                </div>

                <div class="gift-grid">
                    <?php foreach($gift_list as $id => $gift): ?>
                    <label>
                        <input type="radio" name="gift_id" value="<?php echo $id; ?>" class="gift-radio-input">
                        <div class="gift-card-content gift-card">
                            <div class="gift-img-placeholder">
                                <?php echo $gift['image']; ?>
                            </div>
                            <span class="gift-name"><?php echo $gift['name']; ?></span>
                            <span class="gift-point"><?php echo number_format($gift['point']); ?> điểm</span>
                            <span class="gift-desc"><?php echo $gift['desc']; ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <h3 style="color: var(--primary-color); border-left: 5px solid var(--primary-color); padding-left: 10px; margin-bottom: 20px;">
                Thông Tin Khách Hàng
            </h3>
            
            <?php if ($message != ""): ?>
                <div class="alert <?php echo ($msg_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="exchange-panel">
                <div class="form-group">
                    <label class="form-label">Chọn Khách Hàng:</label>
                    <select name="customer_code" class="form-control" id="custSelect" onchange="showPoint()">
                        <option value="" data-point="0">-- Tìm kiếm khách hàng --</option>
                        <?php 
                        mysqli_data_seek($list_customers, 0);
                        while($kh = mysqli_fetch_assoc($list_customers)): 
                        ?>
                            <option value="<?php echo $kh['makhachhang']; ?>" data-point="<?php echo $kh['diemhientai']; ?>">
                                <?php echo $kh['tenkhachhang']; ?> - <?php echo $kh['makhachhang']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div id="custInfo" style="display:none; margin-top: 8px; font-size: 14px; color: #555;">
                        Điểm khả dụng: <span id="viewPoint" style="color: var(--primary-color); font-weight: bold; font-size: 16px;">0</span>
                    </div>
                </div>

                <div style="width: 200px;">
                    <button type="submit" name="btn_exchange" class="btn btn-primary" style="height: 40px; font-size: 15px;">
                        XÁC NHẬN ĐỔI <i class="fa-solid fa-arrow-right" style="margin-left: 5px;"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="historyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span style="font-weight: bold;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Đổi Quà Gần Đây</span>
                <span class="close-btn" onclick="closeHistory()">&times;</span>
            </div>
            <div class="modal-body">
<table class="history-table">
                        <thead>
                            <tr>
                                <th>Thời Gian</th>
                                <th>Khách Hàng</th>
                                <th>Món Quà</th>
                                <th>Điểm Trừ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($history_result) > 0): ?>
                                <?php while($h = mysqli_fetch_assoc($history_result)): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($h['thoigian'])); ?></td>
                                    <td>
                                        <b><?php echo $h['tenkhachhang']; ?></b><br>
                                        <small style="color:#888"><?php echo $h['makhachhang']; ?></small>
                                    </td>
                                    <td style="color: var(--primary-color); font-weight: 500;"><?php echo $h['tenqua']; ?></td>
                                    <td style="color: var(--danger);">-<?php echo number_format($h['diemtru']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding: 20px; color:#888;">Chưa có lịch sử đổi quà nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeHistory()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        function showPoint() {
            var select = document.getElementById("custSelect");
            var option = select.options[select.selectedIndex];
            var infoBox = document.getElementById("custInfo");
            
            if(select.value == "") {
                infoBox.style.display = "none";
            } else {
                infoBox.style.display = "block";
                document.getElementById("viewPoint").innerText = new Intl.NumberFormat().format(option.getAttribute("data-point"));
            }
        }

        function openHistory() { document.getElementById("historyModal").style.display = "block"; }
        function closeHistory() { document.getElementById("historyModal").style.display = "none"; }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById("historyModal")) {
                closeHistory();
            }
        }
    </script>
</body>
</html>