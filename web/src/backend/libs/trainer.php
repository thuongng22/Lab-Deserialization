<?php
#===========================================================================================================#
# [ĐỊNH NGHĨA CLASS CHÍNH]: ĐỐI TƯỢNG TRAINER VÀ TRUMCUOI TRONG GAME                                      #
# - File này chứa:                                                                                          #
#   1. Class Trainer: Đại diện cho nhân vật người chơi, chứa đối tượng Pokemon và các hàm hành động.        #
#   2. Class TrumCuoi: Kế thừa Trainer, ghi đè hàm fight() -> VŨ KHÍ GIẢI MÃ MAP 3.                       #
#===========================================================================================================#
include_once("game_config.php");

class Trainer
{
    // [Thuộc tính 1]: Tên người chơi (string)
    public $name;

    // [Thuộc tính 2]: Đối tượng Pokemon lồng nhau (Instance của Class Pokemon)
    // ĐÂY LÀ ĐỐI TƯỢNG BỊ SERIALIZE KHI NGƯỜI CHƠI BẤM SAVE GAME.
    public $pokemon;

    // [Flow Khởi tạo]: Chỉ chạy khi start.php gọi "new Trainer(...)".
    // LƯU Ý BẢO MẬT: Khi unserialize() chạy ở save-load.php, hàm __construct này HOÀN TOÀN KHÔNG CHẠY!
    // Các giá trị thuộc tính sẽ được nạp trực tiếp từ chuỗi serialized vào RAM.
    public function __construct($name, $pokemon_name, $pokemon_type)
    {
        $this->name = $name;
        // Lấy chỉ số ngẫu nhiên ban đầu từ config (Máu 150-200, Damage 20-50)
        $health = rand(GlobalConfig::BASE_HEALTH_MIN, GlobalConfig::BASE_HEALTH_MAX);
        $damage = rand(GlobalConfig::BASE_DMG_MIN, GlobalConfig::BASE_DMG_MAX);
        // Khởi tạo đối tượng Pokemon con
        $this->pokemon = new Pokemon($health, $damage, $pokemon_name, $pokemon_type);
    }

    /** 
     * [Flow Chiến Đấu]: Được gọi từ map1.php, map2.php, map3.php khi $action == "fight"
     * Code gọi từ map: $result = $_SESSION["trainer"]->fight($_SESSION["enemy"]);
     * 
     * @param Pokemon $wild_pokemon: Quái hoặc Boss cần đánh
     * @return Array: Lịch sử máu qua từng vòng để trả về cho Frontend hiển thị
     */
    public function fight(Pokemon $wild_pokemon)
    {
        // [TẠI SAO LẠI DÙNG MẢNG LỒNG MẢNG (MẢNG 2 CHIỀU) Ở ĐÂY?]:
        // Mỗi trận đấu diễn ra qua nhiều hiệp (rounds).
        // Mỗi hiệp, ta cần lưu 1 cặp số: [Máu của người chơi, Máu của quái].
        // Ví dụ:
        //  - Hiệp 1: Ta còn 85, Quái còn 30  -> Cặp số: array(85, 30)
        //  - Hiệp 2: Ta còn 70, Quái còn 10  -> Cặp số: array(70, 10)
        //  - Hiệp 3: Ta còn 55, Quái còn -10 -> Cặp số: array(55, -10)
        // Lệnh array_push($result, array(...)) sẽ nhét từng cặp số này vào mảng lớn $result.
        // Kết quả trả về cho Frontend JavaScript sẽ là một mảng 2 chiều:
        //   $result = [ [85, 30], [70, 10], [55, -10] ]
        // Frontend JS (battle.js) sẽ đọc mảng này để chạy hiệu ứng đồ họa:
        // Giảm thanh máu của bạn xuống 85, quái xuống 30 -> đợi 0.5s -> giảm tiếp xuống 70, 10...
        $result = array();
        
        // [Vòng lặp trừ máu]: Đánh nhau cho đến khi 1 trong 2 hết máu
        while ($this->pokemon->health > 0 && $wild_pokemon->health > 0) {
            // Máu của ta trừ đi sát thương của quái (SỬA TRỰC TIẾP VÀO RAM QUA $this)
            $this->pokemon->health = $this->pokemon->health - $wild_pokemon->damage;
            // Máu của quái trừ đi sát thương của ta
            $wild_pokemon->health = $wild_pokemon->health - $this->pokemon->damage;
            
            // Nhét cặp máu của hiệp hiện tại vào mảng kết quả
            array_push($result, array($this->pokemon->health, $wild_pokemon->health));
        }
        
        // [ĐIỂM KHAI THÁC MAP 2]: Nếu dùng unserialize sửa $this->pokemon->damage = 99999999,
        // thì ngay hiệp 1, quái mất 99 triệu máu và chết ngay! Vòng while dừng lại, ta thắng.
        $this->pokemon->levelUp();
        
        // Trả về mảng 2 chiều chứa lịch sử các hiệp đấu (cho Frontend hiển thị)
        return $result;
    }

    /** 
     * [Flow Kiểm Tra Sống/Chết]: Được gọi ngay sau hàm fight() ở các file map
     * Code gọi từ map3.php: 
     *      $result = $_SESSION["trainer"]->fight($_SESSION["boss"]);
     *      if ($_SESSION["trainer"]->checkAlive()) { header("Flag: BOSS2_FLAG"); }
     *
     * [BẢN CHẤT TỬ HUYỆT MAP 3]:
     * - Hàm này HOÀN TOÀN KHÔNG CẦN DUYỆT MẢNG $result mà đọc thẳng thuộc tính $this->pokemon->health trong RAM.
     * - Trước khi đánh, map3.php đã ép máu của ta: $this->pokemon->health = 1.
     * - Khi ta đổi Class thành TrumCuoi: hàm TrumCuoi::fight() BỎ QUA HOÀN TOÀN QUY TRÌNH ĐÁNH NHAU (không có vòng while trừ máu).
     * - Do đó, khi dòng code này chạy, máu của ta trong RAM VẪN LÀ 1 (do map3 gán lúc đầu).
     * - So sánh: (1 < 0) là SAI -> hàm trả về TRUE (VẪN CÒN SỐNG)!
     * - Server thấy ta còn sống thì ngây thơ suy đoán là "đã đánh thắng Boss" và TỰ ĐỘNG TRAO CỜ!
     */
    public function checkAlive()
    {
        // Nếu máu < 0 là chết. Nếu máu >= 0 thì coi là còn sống
        if ($this->pokemon->health < 0) {
            return false;
        }
        return true;
    }

    /** 
     * [Flow Chạy Trốn]: Được gọi khi người chơi bấm nút Run ($action == "run") ở map1 -> map4
     * Code gọi từ map: $result = $_SESSION["trainer"]->run();
     * 
     * [TỬ HUYỆT MAP 4 - DUCK TYPING]:
     * - Trong map4.php, server chỉ gọi $_SESSION["trainer"]->run() mà KHÔNG KIỂM TRA kiểu đối tượng.
     * - Nếu hacker nạp vào session một đối tượng Class Calculator (cũng có hàm run()), 
     *   khi người chơi bấm Run, PHP sẽ nhảy vào Calculator::run() thay vì hàm này -> Kích hoạt eval()!
     */
    public function run()
    {
        $rate = rand(0, 100);
        if ($rate > GlobalConfig::RUN_CHANCE) {
            return true;
        }
        $this->pokemon->health = $this->pokemon->health - intval($this->pokemon->health / 10);
        return false;
    }

    // Magic Method __toString: Tự động chạy khi đối tượng bị ép kiểu sang chuỗi
    // Nơi gọi: web/src/backend/helper/info.php dòng "echo $_SESSION['trainer'];"
    public function __toString()
    {
        return json_encode($this);
    }
}

/**
 * [VŨ KHÍ GIẢI MÃ MAP 3]: Class TrumCuoi kế thừa từ Trainer
 * - Cơ chế Đa hình (Polymorphism): TrumCuoi ghi đè (override) phương thức fight().
 * - Ở Map 3, code server ép máu người chơi về 1 ($this->pokemon->health = 1).
 * - Nếu là Trainer thường, vào vòng while sẽ chết chắc.
 * - Nhưng nếu đổi Class thành TrumCuoi qua unserialize, PHP sẽ gọi hàm fight() DƯỚI ĐÂY:
 */
class TrumCuoi extends Trainer
{
    /**
     * [BẢN CHẤT "CHIẾN ĐẤU" CỦA TRUMCUOI]: THỰC CHẤT KHÔNG HỀ ĐÁNH NHAU VỚI QUÁI!
     * 
     * 1. BỎ QUA TOÀN BỘ QUY TRÌNH ĐẤM NHAU:
     *    - Ở class Trainer cha, hàm fight() có vòng lặp "while" để 2 bên trừ máu nhau.
     *    - Nhưng ở TrumCuoi, hàm này GHI ĐÈ (override) và CẮT BỎ HOÀN TOÀN vòng while đó!
     *    - Không có bất kỳ dòng lệnh nào trừ máu của ta ($this->pokemon->health).
     *    - Máu của ta trong RAM giữ nguyên = 1 (chính là con số 1 mà Boss đã ép ta ở đầu Map 3).
     * 
     * 2. TẠI SAO PHẢI RETURN array(array(1, 0))?
     *    - Frontend JavaScript (battle.js) luôn chờ một mảng 2 chiều danh sách các hiệp đấu [[máu_ta, máu_quái]].
     *    - array(array(1, 0)) tương đương JSON [[1, 0]].
     *    - Nó "bịp" Frontend rằng: "Hiệp 1 ta còn 1 máu, Boss còn 0 máu" để JS vẽ hoạt ảnh Boss bị One-hit KO và hiện You Win.
     * 
     * 3. TẠI SAO BỎ QUA ĐÁNH NHAU MÀ SERVER VẪN CHO WIN VÀ NHẢ FLAG?
     *    - Server ở map3.php không hề kiểm tra xem Boss có mất máu hay không!
     *    - Server chỉ chạy dòng tiếp theo: if ($_SESSION["trainer"]->checkAlive())
     *    - Vì máu ta vẫn còn nguyên là 1 (>= 0), checkAlive() trả về TRUE!
     *    - Server ngây thơ cho rằng ta đã sống sót qua trận chiến khốc liệt -> TỰ ĐỘNG TRAO CỜ BOSS2_FLAG!
     */
    public function fight(Pokemon $wild_pokemon)
    {
        return array(array(1, 0));
    }
}
