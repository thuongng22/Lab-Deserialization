<?php
#===========================================================================================================#
# [ĐỊNH NGHĨA CLASS ĐỐI TƯỢNG CON]: POKEMON                                                                 #
# - Thuộc tính của Class này là MỤC TIÊU TẤN CÔNG CHÍNH CỦA MAP 2 (Attribute Tampering).                   #
# - Chứa các biến dữ liệu: $name, $type, $health, $damage.                                                 #
#===========================================================================================================#
include_once("game_config.php");

class Pokemon
{
    public $name;
    public $type;
    // [Biến mục tiêu Map 2]: Lượng máu của Pokemon
    // Khi serialize: s:6:"health";i:180; -> Hacker sửa thành: s:6:"health";i:99999999;
    public $health;

    // [Biến mục tiêu Map 2]: Sát thương gây ra mỗi vòng đánh
    // Khi serialize: s:6:"damage";i:35; -> Hacker sửa thành: s:6:"damage";i:99999999;
    public $damage;

    // [Flow Khởi tạo]: Được gọi từ Trainer::__construct() hoặc khi tạo quái/boss mới trong map2.php
    public function __construct($health, $damage, $name = "", $type = "pikachu")
    {
        $this->name = $name;
        $this->type = $type;
        $this->health = $health;
        $this->damage = $damage;
    }

    // [Flow Thăng cấp]: Được gọi sau mỗi trận thắng trong Trainer::fight()
    public function levelUp()
    {
        $this->damage = $this->damage + GlobalConfig::DMG_INCREASE;
        $this->health = $this->health + GlobalConfig::HEALTH_INCREASE;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}
