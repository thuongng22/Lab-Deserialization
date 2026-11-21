<?php
#===========================================================================================================#
# [BƯỚC 4 TRONG FLOW HỆ THỐNG]: XỬ LÝ LOGIC MAP 2 (CHIẾN ĐẤU BOSS 1 & ATTRIBUTE TAMPERING)                  #
# - Kích hoạt khi: index.php nhận tham số level=map2 và include file này.                                   #
# - Biến $action được thừa hưởng trực tiếp từ scope của index.php.                                         #
# - Mục tiêu Pentest: Hạ gục Boss để máy chủ trả về cờ BOSS1_FLAG qua HTTP Response Header.                 #
#===========================================================================================================#

/** @var string $action */

// [Flow 4.1]: Người chơi gặp Pokemon thường trong map
if ($action == "new_battle") {
	$health = rand(70, 100);
	$damage = rand(10, 30);
	$wild_pokemon = new Pokemon($health, $damage);
	$_SESSION["enemy"] = $wild_pokemon; // Lưu kẻ địch vào session
	echo json_encode([$_SESSION["trainer"]->pokemon, $wild_pokemon]);
	die();
} 

// [Flow 4.2]: Người chơi bước vào phòng Boss 1
else if ($action == "boss") {
	// Khởi tạo Boss với máu cực trâu (5000 - 10000) và damage rất mạnh (100 - 300)
	$health = rand(5000, 10000);
	$damage = rand(100, 300);
	$wild_pokemon = new Pokemon($health, $damage);
	// Lưu đối tượng Boss này vào session kẻ địch
	$_SESSION["enemy"] = $wild_pokemon;
	echo json_encode([$_SESSION["trainer"]->pokemon, $wild_pokemon]);
	die();
} 

// [Flow 4.3]: Người chơi bấm nút Đánh Nhau (Fight) - NƠI DIỄN RA TRẬN CHIẾN
else if ($action == "fight") {
	if (!isset($_SESSION["enemy"])) {
		echo json_encode([]);
		die();
	}
	
	// [Flow 4.4]: Gọi phương thức fight() của đối tượng Trainer đang nằm trong session
	// -> Nhảy sang Trainer::fight() trong libs/trainer.php để chạy vòng lặp while trừ máu.
	$result = $_SESSION["trainer"]->fight($_SESSION["enemy"]);
	
	// [Flow 4.5]: ĐIỀU KIỆN NHẬN CỜ BOSS1_FLAG:
	//  1. checkAlive() trả về true (Máu Pokemon người chơi >= 0 sau trận đấu).
	//  2. Sát thương của kẻ địch >= 100 (Chứng minh kẻ địch bị hạ gục là BOSS chứ không phải quái thường).
	if ($_SESSION["trainer"]->checkAlive()) {
		if ($_SESSION["enemy"]->damage >= 100) {
			// [ĐÍCH ĐẾN MAP 2]: Server trả cờ BOSS1_FLAG về trong HTTP Header "Flag"
			header("Flag: " . getenv("BOSS1_FLAG"));
		}
	}
	
	// [Flow 4.6]: Xóa kẻ địch sau trận đấu và trả về JSON lịch sử máu từng round cho trình duyệt hiển thị
	$_SESSION["enemy"] = null;
	echo json_encode($result);
	die();
} 

// [Flow 4.7]: Người chơi bấm nút Chạy Trốn (Run)
else if ($action == "run") {
	$result = $_SESSION["trainer"]->run();
	if ($result == true)
		echo "You escaped";
	else
		echo "You failed to escape, and lost a little HP";
	die();
}
