# Biến môi trường khi deploy lên Render

Khai báo các biến dưới đây trong **Render Dashboard → Web Service → Environment**.
KHÔNG commit `.env` lên Git — Render đọc trực tiếp từ các Environment Variables này.

## 1. Tạo PostgreSQL trước

Trên Render: **New → PostgreSQL** (gói Free). Sau khi tạo xong, mở trang database,
phần **Connections** sẽ có sẵn các giá trị để điền vào bảng bên dưới.

> Mẹo: nên đặt Web Service và Postgres **cùng region** rồi dùng **Internal Database URL**
> (kết nối nội bộ, nhanh hơn và không tính băng thông). Dùng **External** chỉ khi cần
> kết nối từ ngoài Render.

## 2. Bảng biến môi trường

| Biến | Giá trị | Lấy từ đâu |
|------|---------|------------|
| `APP_KEY` | `base64:qVhwf4J9dcZ1/fsCXStGbfcBwLZy2N9fO+X26O3CqIY=` | Đã sinh sẵn bằng `php artisan key:generate --show` (xem mục 3) |
| `APP_ENV` | `production` | Cố định |
| `APP_DEBUG` | `false` | Cố định (production tuyệt đối không bật debug) |
| `APP_URL` | `https://<ten-service>.onrender.com` | URL Render cấp cho Web Service (cập nhật sau khi service có domain) |
| `DB_CONNECTION` | `pgsql` | Cố định |
| `DB_HOST` | vd: `dpg-xxxxx-a.singapore-postgres.render.com` | **Trang Postgres** → Connections → *Hostname* (Internal hoặc External) |
| `DB_PORT` | `5432` | **Trang Postgres** → Connections → *Port* |
| `DB_DATABASE` | vd: `learning_center_xxxx` | **Trang Postgres** → Connections → *Database* |
| `DB_USERNAME` | vd: `learning_center_user` | **Trang Postgres** → Connections → *Username* |
| `DB_PASSWORD` | (chuỗi bí mật) | **Trang Postgres** → Connections → *Password* |
| `AUTORUN_ENABLED` | `true` | Cố định — bật các tác vụ tự chạy của image serversideup |
| `AUTORUN_LARAVEL_MIGRATION` | `true` | Cố định — tự chạy `php artisan migrate --force` khi container khởi động |
| `AUTORUN_LARAVEL_CONFIG_CACHE` | `true` | Cố định — tự chạy `php artisan config:cache` |

### Các biến nên cân nhắc thêm

| Biến | Giá trị gợi ý | Ghi chú |
|------|---------------|---------|
| `DB_SSLMODE` | `require` | Bật nếu dùng **External** connection của Render (Render bắt buộc SSL khi kết nối ngoài). Với Internal có thể để mặc định `prefer`. |
| `LOG_CHANNEL` | `stderr` | Đẩy log ra stdout/stderr để xem trong tab **Logs** của Render (ổ đĩa bị reset khi restart nên ghi file log không bền). |
| `SESSION_DRIVER` | `database` | Đang dùng `database` — phù hợp vì ổ đĩa local không bền. Bảng `sessions` được tạo qua migration. |
| `CACHE_STORE` | `database` | Tương tự, dùng DB thay vì file. |
| `QUEUE_CONNECTION` | `database` | Nếu cần chạy queue, mở thêm một Background Worker riêng. |
| `APP_NAME` | `Learning Center` | Tuỳ chọn hiển thị. |
| `APP_LOCALE` | `vi` | Tuỳ chọn. |

## 3. APP_KEY

Đã sinh sẵn một key (dùng luôn giá trị trong bảng trên):

```
base64:qVhwf4J9dcZ1/fsCXStGbfcBwLZy2N9fO+X26O3CqIY=
```

Muốn sinh key mới: `php artisan key:generate --show` rồi dán kết quả vào biến `APP_KEY`.

## 4. Lưu ý quan trọng về Render

- **Cổng**: image `serversideup/php:8.4-fpm-nginx` phục vụ thư mục `public/` qua nginx ở
  **cổng 8080**. Render tự dò cổng; nếu cần, đặt thêm biến `PORT=8080`.
- **Ổ đĩa bị reset**: filesystem của Render *ephemeral* — bị xoá mỗi lần restart/deploy.
  KHÔNG lưu file upload, log, hay SQLite vào ổ đĩa app. Dùng:
  - DB Postgres cho dữ liệu,
  - dịch vụ lưu trữ ngoài (S3 / R2…) cho file upload nếu sau này có upload,
  - `LOG_CHANNEL=stderr` cho log.
- **Migration tự chạy**: với `AUTORUN_LARAVEL_MIGRATION=true`, migrations chạy `--force`
  mỗi lần container khởi động. Đảm bảo `DB_*` đã đúng trước khi deploy.
- **Build**: dùng **Docker** runtime (Render đọc `Dockerfile` ở thư mục gốc), không cần
  Build Command / Start Command thủ công.
