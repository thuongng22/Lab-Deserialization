<?php
#===========================================================================================================#
# [ĐỊNH NGHĨA CÁC CLASS TIỆN ÍCH]: NƠI CHỨA CÁC SINK THỰC THI MÃ NGUY HIỂM (EVAL & SYSTEM)                  #
# - Class Calculator: Chứa hàm eval() -> Dùng cho kỹ thuật Duck Typing kích hoạt qua nút Run ở Map 4.       #
# - Class Logger: Chứa hàm system() -> Dùng làm Gadget cuối (Sink) của POP Chain kết hợp với Database.     #
#===========================================================================================================#

/**
 * =====================================================================================
 * SINK 1: CLASS CALCULATOR (MỤC TIÊU DUCK TYPING)
 * =====================================================================================
 */
class Calculator {
    // Thuộc tính chứa đoạn mã PHP cần thực thi (ví dụ: "die(file_get_contents('/flag'));")
    public $expression;

    public function __construct($expr) {
        $this->expression = $expr;
    }

    /**
     * [Flow Kích hoạt Sink 1]: 
     * - Được gọi khi nạp Calculator vào $_SESSION["trainer"], sau đó bấm nút Run ở map4.php.
     * - map4.php gọi: $_SESSION["trainer"]->run() -> Nhảy vào hàm run() này!
     */
    public function run() {
        // [TỬ HUYỆT RCE 1]: eval() thực thi trực tiếp chuỗi PHP string trong $this->expression
        $result = eval($this->expression);
        return $result;
    }
}

/**
 * =====================================================================================
 * SINK 2: CLASS LOGGER (MỤC TIÊU CỦA POP CHAIN TỪ DATABASE::__destruct)
 * =====================================================================================
 */
class Logger {
    // Thuộc tính đường dẫn file cần xóa (BỊ DÍNH LỖI OS COMMAND INJECTION)
    // Ví dụ payload: "dummy; cat /flag > /var/www/html/frontend/materials/flag.txt"
    public $filepath;

    public function __construct($filepath) {
        $this->filepath = $filepath;    
    }

    public function log($data) {
        $file = fopen($this->filepath, "w");
        fwrite($file, $data);
        fclose($file);
    }

    /**
     * [Flow Kích hoạt Sink 2]:
     * - Được gọi từ Database::__destruct() khi script save-load.php kết thúc.
     * - Database::__destruct() gọi: $this->conn->close().
     * - Vì $conn là đối tượng Logger, PHP thực thi hàm close() DƯỚI ĐÂY:
     */
    public function close() {
        // [TỬ HUYỆT RCE 2]: Nối chuỗi trực tiếp vào lệnh bash "rm " mà không lọc dữ liệu!
        // Khi $this->filepath chứa dấu chấm phẩy (;), lệnh phía sau dấu ; sẽ được thực thi trong Terminal!
        system("rm " . $this->filepath);
    }
}

class JSONResponse {
    public static function encode($status, $data) {
        $result = new stdClass();
        $result["status"] = $status;
        $result["data"] = $data;
        return json_encode($result);
    }
    public static function decode($json_str) {
        $obj = json_decode($json_str, true);
        return $obj;
    }
}