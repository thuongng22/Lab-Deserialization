<?php
#===========================================================================================================#
# [BƯỚC 5 TRONG FLOW HỆ THỐNG]: XỬ LÝ LOGIC MAP 3 (BẪY RESET CHỈ SỐ & CLASS SUBSTITUTION)                   #
# - Kích hoạt khi: index.php nhận tham số level=map3.                                                       #
# - Mục tiêu Pentest: Hạ gục Boss 2 để lấy BOSS2_FLAG qua HTTP Response Header.                             #
# - BẪY CỦA DEV: Máy chủ ép toàn bộ máu và sát thương của người chơi về số 1 trước khi giao chiến!          #
#===========================================================================================================#

/** @var string $action */

// [Flow 5.1]: Đánh quái thường trong Map 3
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

// [Flow 5.2]: Khởi tạo Boss 2 (Gọi khi bước vào cửa Boss)
else if ($action == "boss") {
    $health = rand(5000, 10000);
    $damage = rand(100, 300);
    $wild_pokemon = new Pokemon($health, $damage);
    
    // =====================================================================================
    // [TẠI SAO LÚC NÀY LẠI GÁN: $_SESSION["boss"] = $wild_pokemon ?]:
    // - HTTP là giao thức STATELESS (Mỗi request gửi lên là một tiến trình độc lập, xong là tắt).
    // - Request này (action=boss) chỉ làm nhiệm vụ "Tạo ra Boss và gửi hình ảnh về cho bạn".
    // - Server BẮT BUỘC phải cất đối tượng Boss vào $_SESSION["boss"] để "ghi nhớ" con Boss này trong RAM.
    // - Nếu không cất vào Session, thì tí nữa bạn bấm nút Đánh nhau (Request action=fight_boss gửi lên),
    //   server sẽ KHÔNG BIẾT bạn đang đánh với con Boss nào, máu bao nhiêu!
    // =====================================================================================
    $_SESSION["boss"] = $wild_pokemon;
    
    // [CẢNH BÁO BẪY SỐ 1]: Server tự động gán máu và sát thương của bạn về 1
    $_SESSION["trainer"]->pokemon->health = 1;
    $_SESSION["trainer"]->pokemon->damage = 1;
    echo json_encode([$_SESSION["trainer"]->pokemon, $wild_pokemon]);
    die();
} 

// [Flow 5.3]: Người chơi bấm đánh Boss 2 - ĐÂY LÀ ĐOẠN CODE BẪY QUYẾT ĐỊNH
else if ($action == "fight_boss") {
    // [KIỂM TRA AN TOÀN]: Nếu chưa vào phòng Boss (chưa có $_SESSION["boss"]) thì không cho đánh!
    if (!isset($_SESSION["boss"])) {
        echo json_encode([]);
        die();
    }
    
    // [CẢNH BÁO BẪY SỐ 2]: Dù bạn có hack máu 99999999 ở file save tải lên, 
    // đến dòng này máy chủ VẪN ÉP MÁU VÀ DAMAGE VỀ 1!
    $_SESSION["trainer"]->pokemon->health = 1;
    $_SESSION["trainer"]->pokemon->damage = 1;
    
    // [Flow 5.4 - ĐIỀU PHỐI ĐA HÌNH / POLYMORPHISM]:
    //  - Nếu $_SESSION["trainer"] là Trainer thường:
    //      -> Gọi Trainer::fight() -> vào vòng while trừ máu -> Bạn máu 1 bị Boss đánh 300 dame -> Chết ngay!
    //  - NẾU TẬN DỤNG UNSERIALIZE ĐỔI CLASS THÀNH "TrumCuoi":
    //      -> PHP gọi thẳng TrumCuoi::fight() trong libs/trainer.php!
    //      -> TrumCuoi::fight() trả thẳng [[1, 0]] (Máu boss về 0 ngay lập tức, không chạy vòng while).
    $result = $_SESSION["trainer"]->fight($_SESSION["boss"]);
    
    // [Flow 5.5]: Kiểm tra điều kiện cờ BOSS2_FLAG:
    // Vì máu người chơi vẫn giữ nguyên là 1 (>= 0), checkAlive() trả về TRUE!
    if ($_SESSION["trainer"]->checkAlive()) {
        // [ĐÍCH ĐẾN MAP 3]: Trả cờ BOSS2_FLAG qua HTTP Response Header!
        header("Flag: " . getenv("BOSS2_FLAG"));
    }
    
    // =====================================================================================
    // [TẠI SAO LÚC NÀY LẠI GÁN: $_SESSION["boss"] = null ?]:
    // - Trận đấu lúc này ĐÃ KẾT THÚC (đã phân xử xong và đã tính cờ).
    // - Server phải gán = null để XÓA CON BOSS CŨ ĐI (Dọn dẹp phiên đấu).
    // - NẾU KHÔNG GÁN NULL: Con Boss vẫn nằm lì trong Session, bạn chỉ việc bấm F5 hoặc gửi tiếp 
    //   request action=fight_boss là server cứ lấy Boss cũ ra cho bạn đánh ăn cờ hoài mà không cần 
    //   phải đi vào phòng Boss nữa! Gán null để ép bạn nếu muốn đánh tiếp thì phải tạo Boss mới.
    // =====================================================================================
    $_SESSION["boss"] = null;
    echo json_encode($result);
    die();
} 

// [Flow 5.6]: Chạy trốn
else if ($action == "run") {
    $result = $_SESSION["trainer"]->run();
    if ($result == true)
        echo "You escaped";
    else
        echo "You failed to escape, and lost a little HP";
    die();
}
