<?php
#===========================================================================================================#
# [BƯỚC 7 TRONG FLOW HỆ THỐNG]: KIỂM TRA BÍ MẬT ĐỂ MỞ CỬA SANG MAP 5 (MAP 4 CHECKPOINT)                     #
# - Kích hoạt khi: Người chơi submit form nhập "secret" ở Map 4.                                            #
# - File này đọc nội dung file /flag trong container Docker và so sánh với giá trị người chơi gửi lên.     #
# - Nếu khớp: Chuyển hướng người chơi sang map5.html.                                                      #
#===========================================================================================================#

// [Flow 7.1]: Đọc trực tiếp nội dung file /flag trên hệ điều hành của server
$flag = file_get_contents("/flag");

// [Flow 7.2]: Kiểm tra tham số bí mật do người chơi gửi lên qua phương thức GET (?secret=...)
if (isset($_GET["secret"])) {
	// [Flow 7.3]: So sánh nghiêm ngặt (===) giữa flag server và chuỗi người chơi nhập
	if ($flag === $_GET["secret"]) {
		// [ĐÍCH ĐẾN CUỐI CÙNG]: Đúng bí mật -> Chuyển hướng sang Map 5 (Hoàn thành bài Lab)
		die(header("Location: ../../frontend/html/map5.html"));
	}
	// Sai bí mật -> Đẩy ngược về Map 4
	die(header("Location: ../../frontend/html/map4.html"));
}
