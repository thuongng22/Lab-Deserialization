<?php
#===========================================================================================================#
# [BƯỚC 2 TRONG FLOW HỆ THỐNG]: TÍNH NĂNG SAVE & LOAD (ĐIỂM XÂM NHẬP CHÍNH - SOURCE CỦA LỖ HỔNG)           #
# - Mục đích ứng dụng: Cho phép người chơi lưu trạng thái nhân vật về máy và tải lại khi cần.             #
# - Về mặt Pentest:                                                                                         #
#   + Chứa hàm serialize() biến Object thành chuỗi văn bản gửi cho Client (Hacker).                        #
#   + Chứa hàm unserialize() phục hồi Object từ chuỗi người dùng gửi lên KHÔNG KIỂM TRA TÍNH TOÀN VẸN.     #
#   + Đây là nguồn phát sinh toàn bộ các chuỗi tấn công của Map 2, Map 3, Map 4!                             #
#===========================================================================================================#

// [Flow 2.1]: Nạp toàn bộ các class từ /backend/libs/*.php vào RAM (Database, Logger, Calculator...)
include("helper/all_libs.php");

// [Flow 2.2]: Khởi tạo phiên làm việc để truy cập siêu toàn cục $_SESSION
session_start();

// [Flow 2.3]: Kiểm tra đăng nhập
if (!isset($_SESSION["username"]))
	die('{"msg": "You are not logged in"}');

if (isset($_GET["action"])) {
	
	// =====================================================================================
	// NHÁNH 1: LƯU TRẠNG THÁI GAME (SAVE)
	// =====================================================================================
	if ($_GET["action"] == "save") {
		if (!isset($_SESSION["trainer"]))
			die('{"msg": "You havent started the game yet"}');
				
		// [Flow 2.4]: serialize() quét đối tượng $_SESSION["trainer"] trong RAM:
		//  - Trích xuất tên thuộc tính & giá trị của Trainer và Pokemon.
		//  - "Ép phẳng" thành một chuỗi quy chuẩn (Ví dụ: O:7:"Trainer":2:{...}).
		$message = serialize($_SESSION["trainer"]);

		// [Flow 2.5]: Gửi chuỗi này về trình duyệt dưới dạng file tải về "pokemon.sav"
		header('Content-Type: application/octet-stream');
		header("Content-disposition: attachment; filename=pokemon.sav");
		header("Content-Length: " . strlen($message));
		echo $message;
	}
	
	// =====================================================================================
	// NHÁNH 2: TẢI TRẠNG THÁI GAME (LOAD) - ĐÂY CHÍNH LÀ ĐIỂM BÙNG NỔ LỖ HỔNG (SOURCE)
	// =====================================================================================
	else if ($_GET["action"] == "load") {
		// [Flow 2.6]: Nhận file tải lên từ phía người dùng
		if (isset($_FILES["data"])) {
			// Đọc chuỗi serialized thô do người dùng gửi lên
			$data = file_get_contents($_FILES["data"]["tmp_name"]);
			
			// [Flow 2.7 - TỬ HUYỆT]: Gọi hàm unserialize() mà KHÔNG CÓ chữ ký HMAC để xác thực!
			// Cơ chế low-level của PHP lúc này:
			// 1. Phân tích tên Class trong chuỗi (Trainer, TrumCuoi, Database, Calculator...).
			// 2. Cấp phát vùng nhớ cho Object rỗng tương ứng. (KHÔNG GỌI __construct).
			// 3. Bơm toàn bộ các giá trị thuộc tính (Properties) từ chuỗi vào Object.
			$trainer = @unserialize($data);
			
			if ($trainer == null) {
				echo "Something went wrong";
			}
			else {
				// [Flow 2.8]: Gán Object do Hacker thao túng thẳng vào Session của phiên làm việc!
				// Từ thời điểm này, toàn bộ game sẽ dùng Object này để chạy logic!
				$_SESSION["trainer"] = $trainer;
				echo "Load successfully";
				
				// [Flow 2.9 - POP CHAIN KÍCH HOẠT TẠI ĐÂY NẾU NẠP CLASS DATABASE]:
				// Khi script save-load.php chạy đến dòng này và kết thúc (Request Teardown):
				//  -> PHP dọn dẹp các biến cục bộ trong RAM.
				//  -> Nếu $trainer là Object Database, PHP TỰ ĐỘNG GỌI Database::__destruct()!
				//  -> Kích hoạt chuỗi RCE Database::__destruct() -> Logger::close() -> system()!
			}
		}
		else {
			echo "Empty data";
		}
	}
}
else {
	echo '{"msg": "Empty action"}';
}
