<?php
#===========================================================================================================#
# [ĐỊNH NGHĨA CLASS DỊCH VỤ DATABASE]: NƠI CHỨA MAGIC METHOD __destruct() (GADGET ĐẦU TRONG POP CHAIN)    #
# - Mục đích ứng dụng: Kết nối MySQL, kiểm tra đăng ký, đăng nhập.                                         #
# - Về mặt Pentest (ĐIỂM KÍCH HOẠT POP CHAIN):                                                             #
#   + Chứa Magic Method __destruct() tự động chạy khi một Object bị giải phóng khỏi RAM.                   #
#   + Gọi hàm $this->conn->close().                                                                        #
#   + Hacker lợi dụng việc PHP không kiểm tra kiểu để gán $conn thành đối tượng Class Logger!               #
#===========================================================================================================#

class Database {
    // [Thuộc tính tử huyệt]: Dev thiết kế biến này để chứa kết nối MySQL (mysqli).
    // Nhưng khi unserialize(), hacker có thể gán thuộc tính này thành một Object Logger lồng nhau!
    public $conn;

    // Hàm khởi tạo bình thường khi code web tự chạy (khi unserialize hàm này KHÔNG CHẠY)
    public function __construct() {
        $this->conn = new mysqli("db","admin","password","myDB");
    }

    public function check_user($username) {
        $sth = $this->conn->prepare("SELECT username FROM users WHERE username = ?");
        $sth->bind_param("s", $username);
        $sth->execute();
        $result = $sth->get_result();
        $user = $result->fetch_assoc();
        $sth->close();
        if (isset($user))
            return false;
        return true;
    }

    public function insert_user($username, $password) {
        $sth = $this->conn->prepare("INSERT INTO users(username, password) VALUES (?, ?)");
        $sth->bind_param("ss", $username, $password);
        $sth->execute();
        $sth->close();
    }

    public function query_user($username, $password) {
        $sth = $this->conn->prepare("SELECT username FROM users WHERE username = ? AND password = ?");
        $sth->bind_param("ss", $username, $password);
        $sth->execute();
        $result = $sth->get_result();
        $user = $result->fetch_assoc();
        $sth->close();
        return $user;
    }

    // =====================================================================================
    // [ĐIỂM KÍCH HOẠT MAGIC METHOD TRONG POP CHAIN]:
    // - Khi nào chạy? Tự động chạy khi đối tượng Database bị hủy (ví dụ khi kết thúc file save-load.php).
    // - Lỗ hổng: Dev tin rằng $this->conn luôn có hàm close() của mysqli.
    // - Khi bị tấn công: Nếu $this->conn là Class Logger, PHP sẽ chuyển hướng gọi Logger::close()!
    // =====================================================================================
    public function __destruct() {
        $this->conn->close();
    }
}

$db = new Database();
?>