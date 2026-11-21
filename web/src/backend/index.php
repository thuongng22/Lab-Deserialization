<?php
#===========================================================================================================#
# [BƯỚC 3 TRONG FLOW HỆ THỐNG]: ROUTER ĐIỀU PHỐI HÀNH ĐỘNG CỦA GAME (CENTRAL CONTROLLER)                   #
# - Kích hoạt khi: Frontend JavaScript (battle.js, map2.js...) gửi request AJAX để chơi game.               #
# - URL gọi: POST /backend/index.php?level=mapX kèm tham số body POST action=...                            #
# - Nhiệm vụ: Xác thực session, điều hướng nạp file code xử lý của từng Map tương ứng.                     #
#===========================================================================================================#

// [Flow 3.1]: Nạp toàn bộ Class thư viện
include("helper/all_libs.php");

// [Flow 3.2]: Khôi phục phiên làm việc -> Lấy đối tượng $_SESSION["trainer"] (do start.php hoặc save-load.php tạo)
session_start();

// [Flow 3.3]: Kiểm tra người chơi đã đăng nhập chưa
if (!isset($_SESSION["username"]))
    die('{"msg": "You are not logged in"}');

// [Flow 3.4]: Kiểm tra xem người chơi đã có nhân vật trong session chưa
if (!isset($_SESSION["trainer"]))
    die('{"msg": "You havent started the game yet"}');

// [Flow 3.5]: Nhận 2 tham số quan trọng nhất của lượt chơi:
//  - $level từ URL query string (ví dụ: map1, map2, map3, map4)
//  - $action từ HTTP POST body (ví dụ: new_battle, fight, boss, fight_boss, run)
if (isset($_GET["level"]) && isset($_POST["action"])) {
    $level = $_GET["level"];
    $action = $_POST["action"];

    // [Flow 3.6 - ĐIỀU HƯỚNG THEO MAP]:
    // LƯU Ý QUAN TRỌNG VỀ PHẠM VI BIẾN (VARIABLE SCOPE):
    // Trong PHP, lệnh include() không tạo scope mới mà chạy trực tiếp trong ngữ cảnh của index.php.
    // Do đó, các file map1.php, map2.php... sẽ KẾ THỪA TRỰC TIẾP biến $action và $_SESSION["trainer"]!
    switch ($level) {
        case 'map1':
            include('map1.php');
            break;
        case 'map2':
            // Chuyển tiếp luồng xử lý sang map2.php (Nơi đánh Boss 1 lấy BOSS1_FLAG)
            include('map2.php');
            break;
        case 'map3':
            // Chuyển tiếp luồng xử lý sang map3.php (Nơi đánh Boss 2 lấy BOSS2_FLAG qua TrumCuoi)
            include('map3.php');
            break;
        case 'map4':
            // Chuyển tiếp luồng xử lý sang map4.php (Nơi kích hoạt RCE qua hàm run())
            include('map4.php');
            break;
        case 'map5':
            // =====================================================================================
            // [BỨC TƯỜNG BẾ TẮC Ở MAP 5 - ĐỘNG LỰC TÌM ĐẾN MAGIC METHOD]:
            // - Ở Map 5, hoàn toàn KHÔNG CÓ file map5.php, không có bất kỳ action nào (fight, run...).
            // - Mọi API nghiệp vụ đều đóng kín. Không có dòng code nào trong game gọi hàm của đối tượng!
            // - CÂU HỎI CHO PENTESTER: Làm sao để ép server chạy code khi không có API nào gọi hàm?
            // - GIẢI PHÁP: Dùng MAGIC METHOD __destruct()!
            //   -> Không cần gửi request vào map5.php (vì không có file này).
            //   -> Chỉ cần upload payload lên save-load.php?action=load.
            //   -> Khi save-load.php chạy xong dòng cuối cùng, Zend Engine TỰ ĐỘNG GỌI Database::__destruct()
            //      khi thu gom rác bộ nhớ, kích hoạt chuỗi POP Chain RCE đọc /flag mà không cần đến map5.php!
            // =====================================================================================
            break;
        default:
            break;
    }
}
