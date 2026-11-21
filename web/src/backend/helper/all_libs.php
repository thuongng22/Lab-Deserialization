<?php
#===========================================================================================================#
# [BƯỚC 0 TRONG FLOW HỆ THỐNG]: NẠP TẤT CẢ CLASS VÀO BỘ NHỚ (CLASS LOADING INFRASTRUCTURE)               #
# - Mục đích ứng dụng: Tự động nạp mọi thư viện tiện ích trong thư mục /libs/ để các file khác sử dụng.     #
# - Về mặt Pentest (TỬ HUYỆT BẢO MẬT):                                                                     #
#   + Trong PHP, hàm unserialize() chỉ có thể tạo ra 1 Object nếu Class của nó ĐÃ TỒN TẠI TRONG BỘ NHỚ.    #
#   + Đoạn code này dùng glob() include TOÀN BỘ các file: database.php, utils.php, trainer.php,...         #
#   + Vì thế, khi save-load.php gọi all_libs.php, TẤT CẢ các class nguy hiểm (Database, Logger, Calculator)   #
#     đều đã sẵn sàng trong RAM, mở toang khả năng Object Injection / POP Chain cho Hacker.                 #
#===========================================================================================================#

// [Flow 0.1]: Xác định đường dẫn tuyệt đối đến thư mục chứa các class nghiệp vụ (/backend/libs/)
$libs_path = $_SERVER["DOCUMENT_ROOT"] . '/backend/libs/*.php';

// [Flow 0.2]: glob() quét và trả về danh sách toàn bộ các file PHP trong thư mục libs
foreach (glob($libs_path) as $filename) {
	// [Flow 0.3]: include từng file (database.php, game_config.php, pokemon.php, trainer.php, utils.php)
	// Đưa các định nghĩa Class này vào bảng quản lý Class (Class Table) của Zend Engine trong RAM.
	include $filename;
}
