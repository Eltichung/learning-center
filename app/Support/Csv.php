<?php

namespace App\Support;

class Csv
{
    /**
     * Dựng nội dung 1 file CSV dạng chuỗi.
     *
     * - UTF-8 + BOM để Excel đọc đúng tiếng Việt (không lỗi font).
     * - Phân tách bằng ';' vì Excel bản Việt tách cột theo dấu chấm phẩy.
     * - Tiền để số nguyên thô (không format) để Excel SUM/lọc được.
     *
     * @param  string[]  $header  dòng tiêu đề
     * @param  iterable  $rows    mỗi phần tử là mảng giá trị theo đúng thứ tự $header
     */
    public static function toString(array $header, iterable $rows): string
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF"); // BOM

        fputcsv($fh, $header, ';', '"', '');
        foreach ($rows as $row) {
            $row = array_map(fn ($v) => is_null($v) ? '' : $v, (array) $row);
            fputcsv($fh, $row, ';', '"', '');
        }

        rewind($fh);
        $out = stream_get_contents($fh);
        fclose($fh);

        return $out;
    }
}
