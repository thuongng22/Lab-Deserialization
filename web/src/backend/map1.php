<?php
#=============================#
#    Đoạn code xử lí map 1    #
#=============================#

/** @var string $action */
if ($action == "new_battle") {
    $health = rand(20, 40);
    $damage = rand(10, 15);
    $wild_pokemon = new Pokemon($health, $damage);
    $_SESSION["enemy"] = $wild_pokemon;
    echo json_encode([$_SESSION["trainer"]->pokemon, $wild_pokemon]);
    die();
} else if ($action == "fight") {
    if (!isset($_SESSION["enemy"])) {
        // Khi chưa khởi tạo đối thủ
        // Trả về mảng rỗng thể hiện không có sự đánh nhau nào
        echo json_encode([]);
        die();
    }
    $result = $_SESSION["trainer"]->fight($_SESSION["enemy"]);
    $_SESSION["enemy"] = null;
    echo json_encode($result);
    die();
} else if ($action == "run") {
    // =====================================================================================
    // [ĐIỂM KÍCH HOẠT DUCK TYPING RCE - NƠI BẢNG PHPINFO XUẤT HIỆN]:
    // 1. Nếu $_SESSION["trainer"] là đối tượng Calculator (được nạp từ calc.sav):
    //    -> Dòng này gọi: Calculator::run() trong libs/utils.php.
    //    -> Calculator::run() gọi: eval("phpinfo();").
    // 2. BẢN CHẤT CỦA HÀM phpinfo():
    //    -> phpinfo() KHÔNG trả về chuỗi HTML mà TỰ ĐỘNG PHUN THẲNG toàn bộ mã HTML của bảng tím
    //       vào luồng xuất (Output Stream / HTTP Response Body) của PHP!
    //    -> Bản thân hàm phpinfo() chỉ return true -> $result = true.
    // 3. TIẾP THEO:
    //    -> Vì $result == true, dòng echo "You escaped" chạy tiếp và nối thêm vào sau bảng phpinfo.
    // 4. FRONTEND RENDER RA SAO?
    //    -> Toàn bộ HTML này gửi về file frontend/js/battle.js (hàm $.post().done()).
    //    -> battle.js chạy: $("#battle-result").html(data) -> nhét thẳng bảng HTML vào thẻ <p>.
    //    -> battle.js chạy: document.getElementById('dialog-default').showModal() -> BẬT POPUP HIỆN BẢNG!
    // =====================================================================================
    $result = $_SESSION["trainer"]->run();
    if ($result == true)
        echo "You escaped";
    else
        echo "You failed to escape, and lost a little HP";
    die();
}
