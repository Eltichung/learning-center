<?php

namespace Database\Seeders;

use App\Models\CommentTemplate;
use App\Models\CommentType;
use Illuminate\Database\Seeder;

class CommentPresetSeeder extends Seeder
{
    /** Idempotent: bộ loại + mẫu câu nhận xét dùng chung toàn hệ thống (teacher_id = null). */
    public function run(): void
    {
        // Loại nhận xét hệ thống — color = hậu tố class chip (b/a/g/p/r/n)
        $types = [
            ['key' => 'hoc-tap', 'name' => 'Học tập', 'icon' => '📘', 'color' => 'b', 'sort' => 1],
            ['key' => 'bai-tap', 'name' => 'Bài tập', 'icon' => '✍️', 'color' => 'a', 'sort' => 2],
            ['key' => 'thai-do', 'name' => 'Thái độ', 'icon' => '😊', 'color' => 'g', 'sort' => 3],
            ['key' => 'tien-bo', 'name' => 'Tiến bộ', 'icon' => '📈', 'color' => 'p', 'sort' => 4],
            ['key' => 'dan-do',  'name' => 'Dặn dò',  'icon' => '🔔', 'color' => 'r', 'sort' => 5],
        ];

        $typeIds = [];
        foreach ($types as $t) {
            $row = CommentType::updateOrCreate(
                ['teacher_id' => null, 'key' => $t['key']],
                ['name' => $t['name'], 'icon' => $t['icon'], 'color' => $t['color'], 'sort' => $t['sort'], 'is_active' => true]
            );
            $typeIds[$t['key']] = $row->id;
        }

        // Mẫu câu theo từng loại
        $templates = [
            'hoc-tap' => [
                'Em tiếp thu bài tốt, hiểu nhanh nội dung buổi học.',
                'Em nắm vững kiến thức trọng tâm của bài.',
                'Em cần ôn lại phần lý thuyết đã học hôm nay.',
                'Em làm bài còn sai nhiều, cần luyện thêm.',
            ],
            'bai-tap' => [
                'Em hoàn thành đầy đủ bài tập về nhà.',
                'Em trình bày bài sạch đẹp, cẩn thận.',
                'Em chưa làm bài tập về nhà, cần nhắc nhở.',
            ],
            'thai-do' => [
                'Em tích cực phát biểu, xây dựng bài.',
                'Em chăm chú nghe giảng, tập trung tốt.',
                'Em còn nói chuyện riêng trong giờ.',
            ],
            'tien-bo' => [
                'Em có tiến bộ rõ rệt so với buổi trước.',
                'Em ngày càng tự tin hơn khi làm bài.',
            ],
            'dan-do' => [
                'Phụ huynh nhắc em ôn bài trước buổi sau.',
                'Buổi sau mang đầy đủ dụng cụ học tập.',
            ],
        ];

        $sort = 0;
        foreach ($templates as $typeKey => $bodies) {
            foreach ($bodies as $i => $body) {
                $sort++;
                CommentTemplate::updateOrCreate(
                    ['teacher_id' => null, 'key' => $typeKey . '-' . ($i + 1)],
                    [
                        'comment_type_id' => $typeIds[$typeKey],
                        'body' => $body,
                        'sort' => $sort,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
