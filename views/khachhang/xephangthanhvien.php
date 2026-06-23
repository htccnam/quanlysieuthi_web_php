<?php

require_once '../connectdb.php';
if (isset($con))
    { $conn = $con;
 } else {
    die("Lỗi kết nối");
}
$rank_rules = [
    'Đồng' => ['point' => 100, 'benefit' => '<i class="fa-solid fa-gift"></i> Quà gia nhập'],
    'Bạc' => ['point' => 500, 'benefit' => '<i class="fa-solid fa-cake-candles"></i> Quà sinh nhật 200k'],
    'Vàng' => ['point' => 1000, 'benefit' => '<i class="fa-solid fa-truck-fast"></i> Free ship<br><i class="fa-solid fa-wine-glass"></i> Quà tết'],
    'Kim Cương' => ['point' => 5000, 'benefit' => '<i class="fa-solid fa-crown"></i> Quà độc quyền 5tr<br><i class="fa-solid fa-headset"></i> Chăm sóc 24/7']
];

$message = ""; $msg_type = "";

if (isset($_POST['btn_update_rank'])) {
    $code_khach = $_POST['customer_code']; 
    $rank_moi = $_POST['rank_level'];
    
    if (empty($code_khach) || empty($rank_moi)) { 
        $message = "Thiếu thông tin!"; $msg_type = "error"; 
    } else {
        $q = mysqli_query($conn, "SELECT diemtichluy, tenkhachhang FROM khachhang WHERE makhachhang = '$code_khach'");
        $c_data = mysqli_fetch_assoc($q);
        
        $curr = intval($c_data['diemtichluy']);
        $req = $rank_rules[$rank_moi]['point'];
        
        if ($curr < $req) { 
            $message = "Khách <b>{$c_data['tenkhachhang']}</b> chưa đủ điểm ($curr < $req)."; $msg_type = "error"; 
        } else {
            if (mysqli_query($conn, "UPDATE khachhang SET hangthanhvien = '$rank_moi' WHERE makhachhang = '$code_khach'")) {
                $message = "Thăng hạng thành công cho mã <b>$code_khach</b>!"; $msg_type = "success";
            } else { 
                $message = "Lỗi SQL: " . mysqli_error($conn); $msg_type = "error"; 
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    $code = $_GET['code'];
    mysqli_query($conn, "UPDATE khachhang SET hangthanhvien = 'Chưa xếp hạng' WHERE makhachhang = '$code'");
    $message = "Đã hủy hạng cho mã $code!"; $msg_type = "success";
}

$list_customers = mysqli_query($conn, "SELECT * FROM khachhang ORDER BY diemtichluy DESC");
$list_for_modal = mysqli_query($conn, "SELECT * FROM khachhang ORDER BY tenkhachhang ASC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xếp Hạng & Tra Cứu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <div class="navbar-brand"><i class="fa-solid fa-crown"></i> Quản Lý Hạng Thành Viên</div>
        <div><a href="quanlykhachhang.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Về Quản Lý KH</a></div>
    </div>


    <div class="container grid-layout">


        <div>
            <div class="rule-box">
                <h3 style="color: var(--primary-color); margin-top: 0; font-size: 18px;"><i class="fa-solid fa-sitemap"></i> Tác Vụ</h3>
                <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Chọn chức năng thực hiện.</p>
                <button onclick="openModal('rankModal')" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;"><i class="fa-solid fa-trophy"></i> Xét Duyệt Hạng</button>
                <button onclick="openModal('benefitModal')" class="btn btn-secondary" style="width: 100%; background-color: #17a2b8;"><i class="fa-solid fa-list-check"></i> Tra Cứu Quyền Lợi</button>
            </div>
            
            <div style="margin-top: 20px;">
                <h4 style="margin-bottom: 10px; color: #555; font-size: 15px;">Mức Điểm Yêu Cầu</h4>
                <?php foreach ($rank_rules as $rank => $data): ?>
                <div style="margin-bottom: 8px; font-size: 13px; display: flex; justify-content: space-between;">
                    <span class="badge-rank <?php echo ($rank=='Kim Cương')?'rank-diamond':(($rank=='Vàng')?'rank-gold':(($rank=='Bạc')?'rank-silver':'rank-bronze')); ?>"><?php echo $rank; ?></span>
                    <span style="color: #666; font-weight: 500;"> > <?php echo number_format($data['point']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


        <div class="card">
            <div class="card-header"><span><i class="fa-solid fa-users-viewfinder"></i> Danh Sách Thành Viên</span></div>
            <div class="card-body">
                <?php if ($message != ""): ?>
                    <div class="alert <?php echo ($msg_type == 'success') ? 'alert-success' : 'alert-error'; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Khách Hàng</th><th>Điểm Tích Lũy</th><th>Hạng Hiện Tại</th><th>Hành Động</th></tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($list_customers)): 
                                $r = $row['hangthanhvien'];
                                $rc = ($r=='Kim Cương')?'rank-diamond':(($r=='Vàng')?'rank-gold':(($r=='Bạc')?'rank-silver':(($r=='Đồng')?'rank-bronze':'rank-none')));
                            ?>
                            <tr>
                                <td><b><?php echo $row['tenkhachhang']; ?></b><br><small style="color:#888"><?php echo $row['makhachhang']; ?></small></td>
                                <td><span style="font-weight: bold; color: var(--primary-color);"><?php echo number_format($row['diemtichluy']); ?></span></td>
                                <td><span class="badge-rank <?php echo $rc; ?>"><?php echo ($r)?$r:'Chưa xếp hạng'; ?></span></td>
                                <td>
                                    <?php if($r && $r!='Chưa xếp hạng'): ?>
                                    <a href="xephangthanhvien.php?action=reset&code=<?php echo $row['makhachhang']; ?>" onclick="return confirm('Hủy hạng?');" class="btn-action-delete"><i class="fa-solid fa-user-slash"></i> Hủy</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1 -->
    <div id="rankModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span style="font-weight: bold;"><i class="fa-solid fa-pen-nib"></i> Xét Duyệt Lên Hạng</span>
                <span class="close-modal-btn" onclick="closeModal('rankModal')">&times;</span>
            </div>
            <form action="xephangthanhvien.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Hạng Mới <span style="color:red">*</span></label>
                        <select name="rank_level" id="rankSelect" class="form-control" onchange="checkCondition()" required>
                            <option value="">-- Chọn hạng --</option>
                            <?php foreach ($rank_rules as $rank => $data): ?>
                                <option value="<?php echo $rank; ?>" data-point="<?php echo $data['point']; ?>"><?php echo $rank; ?> (> <?php echo number_format($data['point']); ?> điểm)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Khách Hàng <span style="color:red">*</span></label>
                        <select name="customer_code" id="customerSelect" class="form-control" onchange="checkCondition()" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php while ($kh = mysqli_fetch_assoc($list_for_modal)): ?>
                                <option value="<?php echo $kh['makhachhang']; ?>" data-current="<?php echo $kh['diemtichluy']; ?>">
                                    <?php echo $kh['tenkhachhang']; ?> (Có <?php echo number_format($kh['diemtichluy']); ?> điểm)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div id="checkResult" class="check-message"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('rankModal')" class="btn btn-secondary">Đóng</button>
                    <button type="submit" name="btn_update_rank" id="btnSave" class="btn btn-primary" disabled>Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>


    <div id="benefitModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header" style="background-color: #17a2b8;">
                <span style="font-weight: bold;"><i class="fa-solid fa-list-check"></i> Bảng Quy Đổi Điểm & Quyền Lợi</span>
                <span class="close-modal-btn" onclick="closeModal('benefitModal')">&times;</span>
            </div>
            <div class="modal-body" style="padding:0;">
                <table class="benefit-table">
                    <thead><tr><th style="width: 25%;">Hạng Thành Viên</th><th style="width: 25%;">Điểm Yêu Cầu</th><th style="width: 50%;">Quyền Lợi</th></tr></thead>
                    <tbody>
                        <?php foreach ($rank_rules as $rank => $data): ?>
                        <tr>
                            <td><span class="badge-rank <?php echo ($rank=='Kim Cương')?'rank-diamond':(($rank=='Vàng')?'rank-gold':(($rank=='Bạc')?'rank-silver':'rank-bronze')); ?>"><?php echo $rank; ?></span></td>
                            <td><strong>> <?php echo number_format($data['point']); ?></strong> điểm</td>
                            <td><?php echo $data['benefit']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer"><button type="button" onclick="closeModal('benefitModal')" class="btn btn-secondary">Đóng</button></div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = "block"; }
        function closeModal(id) { document.getElementById(id).style.display = "none"; }
        
        function checkCondition() {
            var r = document.getElementById("rankSelect");
            var c = document.getElementById("customerSelect");
            var m = document.getElementById("checkResult");
            var b = document.getElementById("btnSave");

            if (r.value == "" || c.value == "") { m.style.display = "none"; b.disabled = true; return; }

            var req = parseInt(r.options[r.selectedIndex].getAttribute("data-point"));
            var curr = parseInt(c.options[c.selectedIndex].getAttribute("data-current"));
            m.style.display = "block";

            if (curr >= req) {
                m.innerHTML = "<i class='fa-solid fa-check-circle'></i> Đủ điều kiện thăng hạng!"; m.className = "check-message msg-ok"; b.disabled = false;
            } else {
                m.innerHTML = "<i class='fa-solid fa-triangle-exclamation'></i> Thiếu " + (req - curr) + " điểm"; m.className = "check-message msg-fail"; b.disabled = true;
            }
        }
    </script>
</body>
</html>