<?php
#===========================================================================================================#
# [BƯỚC 6 TRONG FLOW HỆ THỐNG]: XỬ LÝ LOGIC MAP 4 (ĐIỂM KÍCH HOẠT DUCK TYPING RCE QUA NÚT RUN)             #
# - Kích hoạt khi: index.php nhận tham số level=map4.                                                       #
# - Map này không có Boss mà có 1 NPC đứng canh cổng đòi hỏi "Secret from server" (nội dung file /flag).    #
# - TỬ HUYỆT: Nhánh $action == "run" gọi $_SESSION["trainer"]->run() mà KHÔNG KIỂM TRA KIỂU DỮ LIỆU!       #
#===========================================================================================================#

/** @var string $action */

// [Flow 6.1]: Khởi tạo trận đấu thường
if ($action == "new_battle") {
	$health = rand(70, 100);
	$damage = rand(10, 30);
	$wild_pokemon = new Pokemon($health, $damage);
	$_SESSION["enemy"] = $wild_pokemon;
	echo json_encode([$_SESSION["trainer"]->pokemon, $wild_pokemon]);
	die();
} 
else if ($action == "fight") {
	if (!isset($_SESSION["enemy"])) {
		echo json_encode([]);
		die();
	}
	$result = $_SESSION["trainer"]->fight($_SESSION["enemy"]);
	$_SESSION["enemy"] = null;
	echo json_encode($result);
	die();
} 

// =====================================================================================
// [Flow 6.2 - ĐIỂM BÙNG NỔ RCE QUA DUCK TYPING]:
// Kích hoạt khi: Người chơi gửi request POST index.php?level=map4 với action=run
// =====================================================================================
else if ($action == "run") {
	// [CƠ CHẾ LOW-LEVEL CỦA DUCK TYPING TRONG PHP]:
	// 1. Dev nghĩ rằng $_SESSION["trainer"] luôn luôn là đối tượng thuộc Class Trainer.
	// 2. NHƯNG PHP là ngôn ngữ kiểu động (Dynamic Typing). Nó chỉ tìm xem đối tượng hiện tại
	//    có phương thức tên là "run()" hay không mà thôi.
	// 3. Nếu hacker dùng save-load.php nạp vào một đối tượng Class Calculator (có sẵn hàm run()):
	//      -> Dòng code dưới đây sẽ nhảy thẳng vào Calculator::run() trong libs/utils.php!
	//      -> Calculator::run() gọi eval($this->expression).
	//      -> Toàn bộ mã độc đọc file /flag sẽ được thực thi ngay tại dòng này!
	$result = $_SESSION["trainer"]->run();
	
	if ($result == true)
		echo "You escaped";
	else
		echo "You failed to escape, and lost a little HP";
	die();
}
