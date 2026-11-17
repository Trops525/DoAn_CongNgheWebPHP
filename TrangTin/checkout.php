<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/../model/giohang.php';
require_once __DIR__ . '/../model/donhang.php';
require_once __DIR__ . '/../model/nguoidung.php';
requireLogin();

$gh = new GIOHANG();
$dh = new DONHANG();
$nd = new NGUOIDUNG();

$checkoutType = isset($_GET['type']) ? $_GET['type'] : (isset($_POST['checkout_type']) ? $_POST['checkout_type'] : 'cart');

if ($checkoutType === 'buynow') {
    $items = $gh->laygiohang('buynow');
} else {
    // Nếu có chọn lọc các item từ cart
    $allItems = $gh->laygiohang('cart');
    if (!empty($_SESSION['checkout_items'])) {
        $selectedKeys = $_SESSION['checkout_items'];
        $items = [];
        foreach ($allItems as $it) {
            if (in_array($it['cart_key'], $selectedKeys)) {
                $items[] = $it;
            }
        }
    } else {
        $items = $allItems;
    }
}

if (empty($items)) {
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['total_price'];
}

$userId = (int)$_SESSION['user_id'];
$user = $nd->laynguoidungtheoid($userId);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diachi = trim($_POST['diachi'] ?? '');
    $dienthoai = trim($_POST['dienthoai'] ?? '');
    $ghichu = trim($_POST['ghichu'] ?? '');
    $phuongthuc = trim($_POST['phuongthuc'] ?? 'COD'); 
    $submittedCheckoutType = $_POST['checkout_type'] ?? 'cart';

    // Kiểm tra địa chỉ phải có đầy đủ 4 cấp: Số nhà/đường, Phường/Xã, Quận/Huyện, Tỉnh/Thành phố
    $addressParts = array_map('trim', explode(',', $diachi));
    if ($diachi === '' || count($addressParts) < 4 || empty($addressParts[0]) || empty($addressParts[1]) || empty($addressParts[2]) || empty($addressParts[3])) {
        $error = 'Vui lòng chọn đúng và đầy đủ địa điểm thật gồm Tỉnh/Thành phố, Quận/Huyện, Phường/Xã và số nhà cụ thể.';
    } else if ($dienthoai === '') {
        $error = 'Vui lòng điền số điện thoại nhận hàng.';
    } else if (!preg_match('/^\d{10}$/', $dienthoai)) {
        $error = 'Số điện thoại phải là 10 chữ số.';
    } else {
        $orderId = $dh->taodonhang($userId, $total, $diachi, $dienthoai, $phuongthuc, $ghichu, $items);
        
        if ($orderId) {
            if ($submittedCheckoutType === 'buynow') { 
                $gh->xoagiohang('buynow'); 
            } else { 
                if (!empty($_SESSION['checkout_items'])) {
                    foreach ($_SESSION['checkout_items'] as $ck) {
                        $gh->xoamotmathang($ck, 'cart');
                    }
                    unset($_SESSION['checkout_items']);
                } else {
                    $gh->xoagiohang('cart');
                }
            }
            
            $_SESSION['flash_message'] = 'Đơn hàng đã được đặt thành công! Vui lòng chờ shop xác nhận.';
            header('Location: donhang.php');
            exit;
        } else {
            $error = 'Không thể lưu đơn hàng. Vui lòng thử lại sau.';
        }
    }
}
include 'header.php';
?>
<main class="checkout-page">
    <div class="container">
        <h1>Thanh toán</h1>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="checkout.php" id="checkout-form">
            <input type="hidden" name="checkout_type" value="<?= htmlspecialchars($checkoutType) ?>">
            
            <div class="checkout-grid">
                <!-- CỘT TRÁI: THÔNG TIN GIAO HÀNG -->
                <div class="checkout-form-section">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #222; border-bottom: 1px solid #eee; padding-bottom: 10px;">Thông tin giao hàng</h3>
                    
                    <label>Họ và tên</label>
                    <input type="text" value="<?= htmlspecialchars($user['HoTen'] ?? '') ?>" disabled style="background:#f5f5f5;">

                    <label>Địa chỉ nhận hàng <span style="color:red">*</span></label>

                    <!-- 3 CẤP HÀNH CHÍNH VIỆT NAM (TỈNH / HUYỆN / XÃ) -->
                    <div class="address-select-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 10px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px;">Tỉnh / Thành phố <span style="color:red">*</span></label>
                            <select id="select-tinh" class="form-control" required style="margin-bottom: 0;">
                                <option value="">-- Chọn Tỉnh / Thành phố --</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px;">Quận / Huyện <span style="color:red">*</span></label>
                            <select id="select-huyen" class="form-control" disabled required style="margin-bottom: 0;">
                                <option value="">-- Chọn Quận / Huyện --</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px;">Phường / Xã <span style="color:red">*</span></label>
                            <select id="select-xa" class="form-control" disabled required style="margin-bottom: 0;">
                                <option value="">-- Chọn Phường / Xã --</option>
                            </select>
                        </div>
                    </div>

                    <!-- ĐỊA CHỈ CHI TIẾT (SỐ NHÀ, TÊN ĐƯỜNG) -->
                    <div style="margin-bottom: 18px;">
                        <label style="font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px;">Số nhà, tên đường cụ thể <span style="color:red">*</span></label>
                        <input type="text" id="diachi-chitiet" placeholder="Ví dụ: Số 18, Ngõ 25, Đường Kim Mã, Tòa nhà Vincom..." required style="margin-bottom: 6px;">

                        <!-- Input ẩn lưu chuỗi chuẩn hóa gửi lên Server -->
                        <input type="hidden" name="diachi" id="diachi-tong" value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>" required>

                        <!-- Box xem trước địa chỉ thực tế đã được chuẩn hóa -->
                        <div id="diachi-preview" style="display: none; padding: 10px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; font-size: 13px; color: #166534; line-height: 1.5;">
                            <i class="fa-solid fa-location-dot" style="color: #16a34a; margin-right: 6px;"></i>
                            <strong>Địa chỉ nhận hàng:</strong> <span id="diachi-text"></span>
                        </div>
                    </div>

                    <label>Số điện thoại <span style="color:red">*</span></label>
                    <input type="text" name="dienthoai" value="<?= htmlspecialchars($_POST['dienthoai'] ?? $user['DienThoai'] ?? '') ?>" required placeholder="Nhập 10 chữ số" pattern="^[0-9]{10}$" title="Số điện thoại phải là 10 chữ số">

                    <label>Ghi chú đơn hàng (nếu có)</label>
                    <textarea name="ghichu" rows="3" placeholder="Ghi chú về giao hàng, thời gian nhận hàng..."><?= htmlspecialchars($_POST['ghichu'] ?? '') ?></textarea>
                </div>

                <!-- CỘT PHẢI: ĐƠN HÀNG, TỔNG TIỀN, PHƯƠNG THỨC THANH TOÁN & NÚT XÁC NHẬN -->
                <div class="checkout-summary-section">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px; color: #222; border-bottom: 1px solid #eee; padding-bottom: 10px;">Đơn hàng của bạn (<?= count($items) ?> sản phẩm)</h3>
                    <div class="summary-items" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">
                        <?php foreach ($items as $item): ?>
                            <div class="summary-item" style="display: flex; gap: 15px; margin-bottom: 15px;">
                                <img src="<?= htmlspecialchars($item['HinhAnh']) ?>" alt="<?= htmlspecialchars($item['TenSanPham']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 500;"><?= htmlspecialchars($item['TenSanPham']) ?></div>
                                    <div style="font-size: 13px; color: #666;">Size: <strong><?= htmlspecialchars($item['size']) ?></strong> | SL: <?= $item['quantity'] ?></div>
                                    <div style="font-size: 13px; color: #d0021b; font-weight: bold;"><?= number_format($item['total_price'], 0, ',', '.') ?> đ</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; margin-bottom: 6px;">
                        <span>Tổng tiền:</span>
                        <span style="color: #d0021b; font-size: 20px;"><?= number_format($total, 0, ',', '.') ?> đ</span>
                    </div>
                    <div style="font-size: 12px; color: #888; margin-bottom: 22px;">(Đã bao gồm thuế GTGT nếu có)</div>

                    <!-- PHƯƠNG THỨC THANH TOÁN (NẰM DƯỚI TỔNG TIỀN) -->
                    <label style="margin-bottom: 12px; display: block; font-weight: bold; font-size: 15px; color: #222;">Phương thức thanh toán <span style="color:red">*</span></label>
                    <div class="payment-methods-grid">
                        <label class="payment-method-card active" id="card-cod" onclick="selectPaymentMethod('COD')">
                            <input type="radio" name="phuongthuc" value="COD" checked onchange="updatePaymentUI()">
                            <div class="method-icon"><i class="fa-solid fa-truck-fast"></i></div>
                            <div class="method-info">
                                <div class="method-title">Thanh toán khi nhận hàng (COD)</div>
                                <div class="method-desc">Thanh toán bằng tiền mặt khi shipper giao hàng tận nơi.</div>
                            </div>
                        </label>

                        <label class="payment-method-card" id="card-qr" onclick="selectPaymentMethod('Chuyển khoản')">
                            <input type="radio" name="phuongthuc" value="Chuyển khoản" onchange="updatePaymentUI()">
                            <div class="method-icon"><i class="fa-solid fa-qrcode" style="color: #0056b3;"></i></div>
                            <div class="method-info">
                                <div class="method-title">Chuyển khoản ngân hàng (Qua mã QR)</div>
                                <div class="method-desc">Quét mã VietQR bằng app ngân hàng hoặc Momo để thanh toán ngay.</div>
                            </div>
                        </label>
                    </div>

                    <!-- KHỐI THÔNG TIN CHUYỂN KHOẢN NGÂN HÀNG (VIETQR) -->
                    <div id="bank-transfer-info" style="display: none; background: #f0f7ff; border: 1.5px dashed #0066cc; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                        <div style="font-weight: 700; color: #004d99; margin-bottom: 14px; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-building-columns"></i> THÔNG TIN CHUYỂN KHOẢN VIETQR
                        </div>
                        <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
                            <div style="text-align: center; flex: 0 0 140px; margin: 0 auto;">
                                <img src="https://img.vietqr.io/image/MB-0768890632-compact2.png?amount=<?= $total ?>&addInfo=<?= urlencode('QualityFashion') ?>&accountName=QUALITY%20FASHION" 
                                     alt="Mã VietQR" 
                                     style="width: 140px; height: 140px; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; padding: 4px; box-sizing: border-box;"
                                     onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=0768946274';">
                                <span style="display: block; font-size: 11px; color: #64748b; margin-top: 5px; font-weight: 600;">Quét mã tự động điền</span>
                            </div>
                            <div style="flex: 1; min-width: 210px; font-size: 14px; line-height: 1.8;">
                                <div>Ngân hàng: <strong>MB Bank (Ngân hàng Quân Đội)</strong></div>
                                <div>Số tài khoản: <strong style="color: #d0021b; font-size: 16px; letter-spacing: 0.5px;">0768946274</strong></div>
                                <div>Chủ tài khoản: <strong>CÔNG TY QUALITY FASHION</strong></div>
                                <div>Số tiền thanh toán: <strong style="color: #d0021b; font-size: 17px;"><?= number_format($total, 0, ',', '.') ?> đ</strong></div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 6px; font-style: italic;">
                                    <i class="fa-solid fa-circle-info"></i> Sau khi quét mã chuyển khoản, bạn chỉ cần bấm nút <strong>"Xác nhận đặt hàng"</strong> bên dưới để hoàn tất.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- NÚT XÁC NHẬN ĐẶT HÀNG (NẰM DƯỚI PHƯƠNG THỨC THANH TOÁN VÀ TỔNG TIỀN) -->
                    <button type="submit" class="btn" style="width: 100%; padding: 14px; background: #28a745; color: #fff; font-size: 16px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(40,167,69,0.3); margin-top: 10px;">Xác nhận đặt hàng</button>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="assets/vietnam_address.js"></script>
<script>
function selectPaymentMethod(method) {
    const radio = document.querySelector(`input[name="phuongthuc"][value="${method}"]`);
    if (radio) {
        radio.checked = true;
        updatePaymentUI();
    }
}

function updatePaymentUI() {
    const qrRadio = document.querySelector('input[name="phuongthuc"][value="Chuyển khoản"]');
    const bankBox = document.getElementById('bank-transfer-info');
    const codCard = document.getElementById('card-cod');
    const qrCard = document.getElementById('card-qr');

    if (qrRadio && qrRadio.checked) {
        if (bankBox) bankBox.style.display = 'block';
        if (qrCard) qrCard.classList.add('active');
        if (codCard) codCard.classList.remove('active');
    } else {
        if (bankBox) bankBox.style.display = 'none';
        if (codCard) codCard.classList.add('active');
        if (qrCard) qrCard.classList.remove('active');
    }
}

// ==========================================
// XỬ LÝ CHỌN ĐỊA CHỈ HÀNH CHÍNH THẬT VIỆT NAM
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentUI();

    const selectTinh = document.getElementById('select-tinh');
    const selectHuyen = document.getElementById('select-huyen');
    const selectXa = document.getElementById('select-xa');
    const diachiChitiet = document.getElementById('diachi-chitiet');
    const diachiTong = document.getElementById('diachi-tong');
    const diachiPreview = document.getElementById('diachi-preview');
    const diachiText = document.getElementById('diachi-text');

    function initAddressData(data) {
        if (!data || !Array.isArray(data)) return;

        // Đổ danh sách Tỉnh/Thành phố
        selectTinh.innerHTML = '<option value="">-- Chọn Tỉnh / Thành phố --</option>';
        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.textContent = p.name;
            selectTinh.appendChild(opt);
        });

        // Khi chọn Tỉnh/Thành phố -> Đổ danh sách Quận/Huyện thật tương ứng
        selectTinh.addEventListener('change', function() {
            const pName = this.value;
            selectHuyen.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
            selectXa.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
            selectXa.disabled = true;

            if (!pName) {
                selectHuyen.disabled = true;
                updateFullAddress();
                return;
            }

            const pFound = data.find(x => x.name === pName);
            if (pFound && pFound.districts) {
                pFound.districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.textContent = d.name;
                    selectHuyen.appendChild(opt);
                });
                selectHuyen.disabled = false;
            }
            updateFullAddress();
        });

        // Khi chọn Quận/Huyện -> Đổ danh sách Phường/Xã thật tương ứng
        selectHuyen.addEventListener('change', function() {
            const pName = selectTinh.value;
            const dName = this.value;
            selectXa.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';

            if (!dName) {
                selectXa.disabled = true;
                updateFullAddress();
                return;
            }

            const pFound = data.find(x => x.name === pName);
            if (pFound && pFound.districts) {
                const dFound = pFound.districts.find(x => x.name === dName);
                if (dFound && dFound.wards) {
                    dFound.wards.forEach(w => {
                        const opt = document.createElement('option');
                        opt.value = w;
                        opt.textContent = w;
                        selectXa.appendChild(opt);
                    });
                    selectXa.disabled = false;
                }
            }
            updateFullAddress();
        });

        // Khi chọn Phường/Xã hoặc nhập số nhà
        selectXa.addEventListener('change', updateFullAddress);
        diachiChitiet.addEventListener('input', updateFullAddress);

        function updateFullAddress() {
            const tinh = selectTinh.value.trim();
            const huyen = selectHuyen.value.trim();
            const xa = selectXa.value.trim();
            const soNha = diachiChitiet.value.trim();

            if (tinh && huyen && xa && soNha) {
                const full = `${soNha}, ${xa}, ${huyen}, ${tinh}`;
                diachiTong.value = full;
                diachiText.textContent = full;
                diachiPreview.style.display = 'block';
            } else {
                diachiTong.value = '';
                diachiPreview.style.display = 'none';
            }
        }

        // Tự động điền lại địa chỉ cũ nếu đã từng nhập
        const prevAddress = <?= json_encode($_POST['diachi'] ?? '') ?>;
        if (prevAddress && prevAddress.includes(',')) {
            const parts = prevAddress.split(',').map(s => s.trim());
            if (parts.length >= 4) {
                const pName = parts[parts.length - 1];
                const dName = parts[parts.length - 2];
                const wName = parts[parts.length - 3];
                const detail = parts.slice(0, parts.length - 3).join(', ');

                selectTinh.value = pName;
                selectTinh.dispatchEvent(new Event('change'));
                selectHuyen.value = dName;
                selectHuyen.dispatchEvent(new Event('change'));
                selectXa.value = wName;
                selectXa.dispatchEvent(new Event('change'));
                diachiChitiet.value = detail;
                updateFullAddress();
            }
        }
    }

    // Nạp dữ liệu địa chỉ
    if (window.VIETNAM_ADDRESS_DATA && Array.isArray(window.VIETNAM_ADDRESS_DATA)) {
        initAddressData(window.VIETNAM_ADDRESS_DATA);
    } else {
        fetch('assets/vietnam_address.json')
            .then(res => res.json())
            .then(data => initAddressData(data))
            .catch(err => console.error('Lỗi tải dữ liệu địa lý:', err));
    }

    // Xác thực bắt buộc trước khi Submit
    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const tinh = selectTinh.value.trim();
            const huyen = selectHuyen.value.trim();
            const xa = selectXa.value.trim();
            const soNha = diachiChitiet.value.trim();

            if (!tinh) {
                e.preventDefault();
                alert('Vui lòng chọn Tỉnh / Thành phố thật từ danh sách!');
                selectTinh.focus();
                return false;
            }
            if (!huyen) {
                e.preventDefault();
                alert('Vui lòng chọn Quận / Huyện thật từ danh sách!');
                selectHuyen.focus();
                return false;
            }
            if (!xa) {
                e.preventDefault();
                alert('Vui lòng chọn Phường / Xã thật từ danh sách!');
                selectXa.focus();
                return false;
            }
            if (!soNha || soNha.length < 3) {
                e.preventDefault();
                alert('Vui lòng nhập số nhà, tên đường cụ thể!');
                diachiChitiet.focus();
                return false;
            }

            diachiTong.value = `${soNha}, ${xa}, ${huyen}, ${tinh}`;
        });
    }
});
</script>

<?php include 'footer.php'; ?>
