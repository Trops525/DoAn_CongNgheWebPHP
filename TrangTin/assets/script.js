/* ============================================
   JAVASCRIPT TỔNG HỢP - ALL SCRIPTS COMBINED
   ============================================ */

/**
 * PHẦN 1: COMMON FUNCTIONS
 * Các hàm dùng chung cho toàn bộ trang
 */
document.addEventListener('DOMContentLoaded', function () {
    // Xử lý nút Tìm kiếm
    var searchButton = document.querySelector('.search-bar button');
    var searchInput = document.querySelector('.search-bar input');

    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function () {
            var value = searchInput.value.trim();
            if (value) {
                // Tìm kiếm sẽ được xử lý bởi form action
            }
        });
    }

    // Xử lý link xác nhận (đòi hỏi confirm trước khi thực hiện)
    var confirmLinks = document.querySelectorAll('a[data-confirm]');
    confirmLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var message = link.getAttribute('data-confirm');
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});

/**
 * PHẦN 2: PRODUCT DETAIL - CHỌN KÍCH THƯỚC
 * Hàm selectSize cho trang chi tiết sản phẩm
 */
function selectSize(element) {
    // 1. Loại bỏ class "active" từ tất cả nút size
    document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
    // 2. Thêm class "active" cho nút được chọn
    element.classList.add('active');

    // 3. Lấy tên size từ nội dung nút
    const selectedSize = element.innerText.trim();
    // 4. Lấy ID sản phẩm từ thuộc tính data-product-id
    const productId = element.getAttribute('data-product-id');
    
    // 5. Cập nhật link nút "Thêm vào giỏ hàng" với size được chọn
    const cartBtn = document.getElementById('add-to-cart-btn');
    if (cartBtn) {
        cartBtn.href = `add_to_cart.php?id=${productId}&size=${encodeURIComponent(selectedSize)}`;
    }
    
    // 6. Cập nhật link nút "Mua ngay" với size được chọn
    const buyNowBtn = document.getElementById('buy-now-btn');
    if (buyNowBtn) {
        buyNowBtn.href = `add_to_cart.php?id=${productId}&size=${encodeURIComponent(selectedSize)}&action=buynow`;
    }

    // 7. Cập nhật URL trình duyệt (không reload) để lưu size hiện tại
    if (window.history && window.history.replaceState) {
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('size', selectedSize);
            window.history.replaceState(null, '', url.pathname + url.search);
        } catch(e) {}
    }

    // 8. Cập nhật các nút màu sắc khác để khi bấm đổi màu vẫn giữ nguyên size này
    document.querySelectorAll('.color-options a[href*="chitiet.php"]').forEach(a => {
        try {
            const url = new URL(a.href, window.location.origin);
            url.searchParams.set('size', selectedSize);
            a.href = url.pathname + url.search;
        } catch(e) {}
    });

    // 9. Cập nhật link Đăng nhập / Đăng ký trên top bar
    document.querySelectorAll('.top-actions a[href*="dangnhap.php"], .top-actions a[href*="dangky.php"]').forEach(a => {
        const nextUrl = `chitiet.php?id=${productId}&size=${encodeURIComponent(selectedSize)}`;
        a.href = a.href.split('?')[0] + '?next=' + encodeURIComponent(nextUrl);
    });
}

/**
 * PHẦN 3: SHOP FILTER - BỘ LỌC VỚI HÀNH VI RADIO BUTTON
 * Làm cho các checkbox hoạt động như radio buttons (chỉ chọn một trong mỗi nhóm)
 * Áp dụng cho: Danh mục, Kích thước, Giới tính, Màu sắc, Giá
 */
document.addEventListener('DOMContentLoaded', function() {
    const filterGroups = {
        'cats[]': document.querySelectorAll('input[name="cats[]"]'),
        'sizes[]': document.querySelectorAll('input[name="sizes[]"]'),
        'genders[]': document.querySelectorAll('input[name="genders[]"]'),
        'colors[]': document.querySelectorAll('input[name="colors[]"]'),
        'prices[]': document.querySelectorAll('input[name="prices[]"]')
    };
    
    let isUpdating = false;
    
    // Áp dụng logic cho từng nhóm filter
    Object.keys(filterGroups).forEach(filterName => {
        const checkboxes = filterGroups[filterName];
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Ngăn chặn xử lý lặp lại
                if (isUpdating) return;
                
                isUpdating = true;
                
                // Nếu checkbox được tick, bỏ tick các checkbox khác trong nhóm
                if (this.checked) {
                    checkboxes.forEach(cb => {
                        if (cb !== this) {
                            cb.checked = false;
                        }
                    });
                }
                
                isUpdating = false;
                
                // Tự động submit form để cập nhật kết quả lọc
                const filterForm = document.getElementById('filterForm');
                if (filterForm) {
                    filterForm.submit();
                }
            });
        });
    });
});

/**
 * PHẦN 4: BANNER SLIDER TỰ ĐỘNG
 * Xử lý tự động chuyển ảnh banner mỗi 4 giây
 */
document.addEventListener('DOMContentLoaded', function () {
    let slideIndex = 0;
    const slides = document.querySelectorAll('.slide');
    const prevButton = document.querySelector('.prev-arrow');
    const nextButton = document.querySelector('.next-arrow');
    let slideTimer = null;

    // Chỉ chạy logic nếu trang web hiện tại có banner
    if (slides.length > 0) {
        function showSlides(index) {
            slides.forEach(slide => {
                slide.style.display = 'none';
            });

            if (index > slides.length) {
                slideIndex = 1;
            } else if (index < 1) {
                slideIndex = slides.length;
            } else {
                slideIndex = index;
            }

            slides[slideIndex - 1].style.display = 'block';
        }

        function nextSlide() {
            showSlides(slideIndex + 1);
        }

        function prevSlide() {
            showSlides(slideIndex - 1);
        }

        function startAuto() {
            slideTimer = setInterval(nextSlide, 4000);
        }

        function resetAuto() {
            if (slideTimer) {
                clearInterval(slideTimer);
            }
            startAuto();
        }

        // Khởi tạo slide đầu tiên và bắt đầu tự động
        showSlides(1);
        startAuto();

        if (prevButton) {
            prevButton.addEventListener('click', function () {
                prevSlide();
                resetAuto();
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                nextSlide();
                resetAuto();
            });
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var chatToggle = document.getElementById('chat-toggle');
    var chatDrawer = document.getElementById('chatbox-drawer');
    var chatClose = document.getElementById('chatbox-close');

    var chatOpenButton = document.getElementById('open-chat-drawer') || document.getElementById('open-feedback-chat');

    if (chatToggle && chatDrawer) {
        chatToggle.addEventListener('click', function (event) {
            if (window.location.pathname.indexOf('cskh.php') !== -1) {
                event.preventDefault();
                chatDrawer.classList.toggle('open');
            }
        });
    }

    if (chatOpenButton && chatDrawer) {
        chatOpenButton.addEventListener('click', function (event) {
            if (window.location.pathname.indexOf('cskh.php') !== -1) {
                event.preventDefault();
                chatDrawer.classList.add('open');
            }
        });
    }

    if (window.location.pathname.indexOf('cskh.php') !== -1 && window.location.search.indexOf('openChat=1') !== -1) {
        if (chatDrawer) {
            chatDrawer.classList.add('open');
        }
    }

    if (chatClose && chatDrawer) {
        chatClose.addEventListener('click', function () {
            chatDrawer.classList.remove('open');
        });
    }
});

/**
 * PHẦN 5: TOAST NOTIFICATION VÀ THÊM VÀO GIỎ HÀNG NHANH (AJAX)
 */
window.showToast = function(title, message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    const iconClass = type === 'error' ? 'fa-circle-xmark' : (type === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-check');
    const toast = document.createElement('div');
    toast.className = `toast-popup toast-${type}`;
    toast.innerHTML = `
        <div class="toast-icon-wrap"><i class="fa-solid ${iconClass}"></i></div>
        <div class="toast-body">
            <div class="toast-title">${title}</div>
            <div class="toast-msg">${message}</div>
            <a href="cart.php" class="toast-btn-cart">Xem giỏ hàng <i class="fa fa-arrow-right"></i></a>
        </div>
        <button type="button" class="toast-close-btn" onclick="const p = this.closest('.toast-popup'); p.classList.add('toast-fade-out'); setTimeout(() => p.remove(), 300);">&times;</button>
        <div class="toast-progress-bar"></div>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.classList.add('toast-fade-out');
        setTimeout(() => toast.remove(), 320);
    }, 3800);
};

document.addEventListener('DOMContentLoaded', function () {
    const addCartBtn = document.getElementById('add-to-cart-btn');
    if (addCartBtn) {
        addCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetUrl = this.href + (this.href.includes('?') ? '&' : '?') + 'ajax=1';
            
            const oldText = this.innerText;
            this.innerText = 'ĐANG THÊM...';
            this.style.pointerEvents = 'none';

            fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => {
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }
                return res.json();
            })
            .then(data => {
                this.innerText = oldText;
                this.style.pointerEvents = 'auto';
                if (!data) return;
                
                if (data.status === 'redirect' && data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                if (data.status === 'success') {
                    window.showToast('Thành công!', data.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success');
                    
                    const cartBadges = document.querySelectorAll('.icon-badge[title="Giỏ hàng"] span, a[href="cart.php"] span');
                    if (cartBadges.length > 0) {
                        cartBadges.forEach(b => b.innerText = data.cart_count);
                    } else if (data.cart_count > 0) {
                        const cartIcon = document.querySelector('.icon-badge[title="Giỏ hàng"]');
                        if (cartIcon) {
                            const newBadge = document.createElement('span');
                            newBadge.innerText = data.cart_count;
                            cartIcon.appendChild(newBadge);
                        }
                    }
                }
            })
            .catch(() => {
                this.innerText = oldText;
                this.style.pointerEvents = 'auto';
                window.location.href = this.href;
            });
        });
    }
});