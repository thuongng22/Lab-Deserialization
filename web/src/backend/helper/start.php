<?php
#===========================================================================================================#
# [BƯỚC 1 TRONG FLOW HỆ THỐNG]: KHỞI TẠO DỮ LIỆU NHÂN VẬT BAN ĐẦU (GAME INITIALIZATION)                   #
# - Kích hoạt khi: Người chơi đăng ký/đăng nhập xong và chọn tên + loại Pokemon ở màn hình new_game.html.    #
# - Nhiệm vụ: Tạo cây đối tượng Trainer -> Pokemon trong RAM và lưu vào $_SESSION["trainer"].              #
#===========================================================================================================#

// [Flow 1.1]: Nạp toàn bộ class thư viện từ libs/ (bao gồm Trainer và Pokemon) vào RAM
include("all_libs.php");

// [Flow 1.2]: Khởi tạo hoặc khôi phục phiên làm việc HTTP Session (đọc cookie PHPSESSID từ client)
session_start();

// [Flow 1.3]: Kiểm tra dữ liệu đầu vào người chơi gửi lên qua phương thức POST
if (isset($_POST["pokemon_name"]) && isset($_POST["pokemon_type"])) {
	// [Flow 1.4]: Gọi hàm khởi tạo new Trainer(...):
	//  -> Nhảy vào Trainer::__construct() trong libs/trainer.php.
	//  -> Trainer tự động tính ngẫu nhiên máu (150-200) và damage (20-50).
	//  -> Trainer gọi tiếp new Pokemon(...) trong libs/pokemon.php để tạo thuộc tính $pokemon lồng nhau.
	//  -> Kết quả là một cây Object hoàn chỉnh được lưu vào $_SESSION["trainer"] (lưu trữ phiên trên server).
	$_SESSION["trainer"] = new Trainer($_SESSION["username"], $_POST["pokemon_name"], $_POST["pokemon_type"]);
}

// [Flow 1.5]: Chuyển hướng trình duyệt của người chơi vào giao diện màn chơi đầu tiên (Map 1)
die(header("Location: ../../frontend/html/map1.html"));
