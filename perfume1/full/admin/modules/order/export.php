<?php
include('../../config/config.php');
require("../../../vendor/autoload.php");
include('../format/format.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Tạo một đối tượng Spreadsheet mới
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cài đặt tiêu đề cho các cột
$headers = ['STT', 'Mã đơn hàng', 'Thời gian', 'Tên khách hàng', 'Địa chỉ giao hàng', 'Phương thức', 'Tình trạng', 'Tổng tiền'];
foreach ($headers as $index => $header) {
    $sheet->setCellValue(chr(65 + $index) . '1', $header);
    // Làm đậm tiêu đề
    $sheet->getStyle(chr(65 + $index) . '1')->getFont()->setBold(true);
}

// Fetch records from database
$sql_orders = "
        SELECT orders.*, account.account_name, delivery.delivery_address
        FROM orders
        JOIN account ON orders.account_id = account.account_id
        LEFT JOIN delivery ON orders.delivery_id = delivery.delivery_id
        ORDER BY orders.order_id DESC
    ";
$query_orders = mysqli_query($mysqli, $sql_orders);

$rowIndex = 2;
$stt = 1;

if (mysqli_num_rows($query_orders) > 0) {
    // Lặp qua từng dòng dữ liệu và ghi vào file Excel
    while ($row = mysqli_fetch_array($query_orders)) {
        // Format order type (payment method)
        $order_type = '';
        if ($row['order_type'] == 1) {
            $order_type = 'Thanh toán khi nhận hàng (COD)';
        } elseif ($row['order_type'] == 2) {
            $order_type = 'Thanh toán MOMO QR CODE';
        } elseif ($row['order_type'] == 3) {
            $order_type = 'Thanh toán chuyển khoản MoMo';
        } elseif ($row['order_type'] == 4) {
            $order_type = 'Thanh toán chuyển khoản VNPAY';
        } elseif ($row['order_type'] == 5) {
            $order_type = 'Mua hàng trực tiếp';
        }

        // Format order status
        $order_status = '';
        if ($row['order_status'] == -1) {
            $order_status = 'Đơn hàng đã hủy';
        } elseif ($row['order_status'] == 0) {
            $order_status = 'Đang xử lý';
        } elseif ($row['order_status'] == 1) {
            $order_status = 'Đang chuẩn bị';
        } elseif ($row['order_status'] == 2) {
            $order_status = 'Đang giao hàng';
        } elseif ($row['order_status'] == 3) {
            $order_status = 'Đã giao hàng';
        } elseif ($row['order_status'] == 4) {
            $order_status = 'Đơn hàng hoàn trả';
        } else {
            $order_status = 'Đã hoàn thành';
        }

        $sheet->setCellValue('A' . $rowIndex, $stt);
        $sheet->setCellValue('B' . $rowIndex, $row['order_code']);
        $sheet->setCellValue('C' . $rowIndex, $row['order_date']);
        $sheet->setCellValue('D' . $rowIndex, $row['account_name']);
        $sheet->setCellValue('E' . $rowIndex, $row['delivery_address']);
        $sheet->setCellValue('F' . $rowIndex, $order_type);
        $sheet->setCellValue('G' . $rowIndex, $order_status);
        $sheet->setCellValue('H' . $rowIndex, $row['total_amount']);

        // Căn chỉnh cột H (tổng tiền) sang phải
        $sheet->getStyle('H' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $rowIndex++;
        $stt++;
    }
} else {
    $sheet->setCellValue('A' . $rowIndex, 'Không có dữ liệu...');
}

// Cài đặt độ rộng cột tự động
$sheet->getColumnDimension('A')->setWidth(6);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(12);
$sheet->getColumnDimension('D')->setWidth(20);
$sheet->getColumnDimension('E')->setWidth(25);
$sheet->getColumnDimension('F')->setWidth(30);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);

// Lưu file Excel
$writer = new Xlsx($spreadsheet);
$fileName = 'orders_' . date('Y-m-d_H-i-s') . '.xlsx';

// Đặt header cho việc tải xuống file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');

// Đọc dữ liệu từ file và ghi vào output buffer
$writer->save('php://output');
