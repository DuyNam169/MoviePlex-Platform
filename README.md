# MoviePlex - Platform

Ứng dụng đặt vé xem phim nhóm xây dựng bằng PHP thuần, tổ chức theo mô hình MVC, chạy trên môi trường Docker (Apache + MySQL).

---

## Tính năng

- Đăng ký, đăng nhập, quên mật khẩu (gửi email qua SMTP)
- Xem danh sách phim, chi tiết phim, lịch chiếu theo rạp
- Chọn ghế, đặt vé, thanh toán, áp mã giảm giá (voucher)
- Quản lý vé cá nhân và hồ sơ người dùng
- Trang quản trị (Admin): quản lý phim, rạp, lịch chiếu, voucher, tài khoản, doanh thu, nhật ký hoạt động
- Giao diện nhân viên rạp

---

## Công nghệ sử dụng

| Thành phần       | Công nghệ                     |
| ---------------- | ----------------------------- |
| Backend          | PHP 8.2 (thuần)               |
| Frontend         | HTML / CSS / JavaScript thuần |
| Database         | MySQL 8                       |
| Web Server       | Apache 2 (mod_rewrite)        |
| Môi trường       | Docker + Docker Compose       |
| Gửi email        | PHPMailer (Composer)          |
| DB GUI           | phpMyAdmin                    |
| Quản lý mã nguồn | Git (GitHub) + SVN            |

---

## Hạ tầng triển khai

Dự án chạy trên **máy chủ Linux (Ubuntu Server 24.04 LTS)** cài trong VirtualBox.

| Dịch vụ    | Mô tả                                              |
| ---------- | -------------------------------------------------- |
| SSH        | Đăng nhập và quản lý server từ xa                  |
| SFTP       | Truyền file lên/xuống server qua SSH               |
| SVN Server | Quản lý mã nguồn tập trung (Apache + mod_dav_svn)  |
| Docker     | Chạy web server (PHP/Apache) và database (MySQL) trong container |

---

## Cấu trúc thư mục

```
MoviePlex-Platform/
├── docker/             # Cấu hình Docker: Dockerfile, Apache, PHP, init SQL
│   ├── mysql/
│   │   └── init.sql
│   └── php/
│       ├── Dockerfile
│       ├── apache.conf
│       └── php.ini
├── fe/                 # Frontend: pages, admin panel, components, assets
├── be/                 # Backend: config, routes, middleware, controllers, services, models, core
├── docker-compose.yml
├── composer.json
├── .env.example
└── README.md
```

---

## Yêu cầu môi trường

| Công cụ | Ghi chú |
| ------- | ------- |
| VirtualBox | Chạy máy ảo Ubuntu Server |
| Docker Engine + Docker Compose | Cài trên máy ảo Linux |
| Git | Cài trên máy thật |
| TortoiseSVN | Cài trên máy thật (Windows) |
| Composer | Cài trên máy ảo hoặc máy thật |

---

## Cài đặt lần đầu (chạy trên máy ảo Linux)

### Bước 1 — SSH vào máy ảo từ máy thật

Mở CMD hoặc PowerShell trên Windows:

```cmd
ssh us@192.168.1.45
```

Nhập mật khẩu khi được hỏi.

### Bước 2 — Clone project từ GitHub về máy ảo

```bash
cd ~
git clone https://github.com/DuyNam169/MoviePlex-Platform
cd MoviePlex-Platform
```

### Bước 3 — Tạo file `.env`

```bash
cp .env.example .env
```

Mở file `.env` và điền các giá trị:

```env
DB_HOST=mysql
DB_NAME=movieflex_db
DB_USER=movieplex_user
DB_PASSWORD=secret
MYSQL_ROOT_PASSWORD=rootsecret

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM=your_email@gmail.com
```

### Bước 4 — Khởi động Docker

```bash
docker compose up -d --build
```

Lần đầu chạy sẽ mất vài phút để tải image và build. Docker tự động:
- Build PHP/Apache image từ `docker/php/Dockerfile`
- Khởi tạo database từ `docker/mysql/init.sql`
- Tạo volume `mysql_data` để lưu dữ liệu bền vững

### Bước 5 — Kiểm tra

```bash
docker compose ps
```

Ba container phải có trạng thái `running` / `healthy`:

```
movieplex_app   Up
movieplex_db    Up (healthy)
movieplex_pma   Up
```

### Bước 6 — Truy cập từ máy thật

| Dịch vụ    | URL                          |
| ---------- | ---------------------------- |
| Ứng dụng   | http://192.168.1.45:8080     |
| phpMyAdmin | http://192.168.1.45:8081     |
| MySQL      | 192.168.1.45:3306            |

---

## Workflow phát triển

Mỗi thành viên code trên máy thật, GitHub là kho chính. SVN dùng để lưu trữ tập trung theo yêu cầu môn học.

```
[Máy thật] Sửa code
     │
     ├─► git push → GitHub (kho chính)
     │
     └─► svn commit → SVN Server trên máy ảo (yêu cầu môn học)

[Máy ảo Linux] git pull + docker compose restart → website cập nhật
```

---

## Cập nhật code lên GitHub

Thực hiện trên **máy thật**:

```bash
git add .
git commit -m "Mô tả thay đổi"
git push
```

---

## Cập nhật code lên SVN

Thực hiện trên **máy thật** (dùng command line hoặc TortoiseSVN):

**Nếu đã checkout SVN rồi** (lần trước đã checkout):

```bash
cd duong_dan_thu_muc_svn
# Copy file vừa sửa vào thư mục SVN, rồi commit
svn commit --username sv01 -m "Mô tả thay đổi"
```

**Nếu chưa checkout SVN lần nào** (lần đầu):

```bash
svn checkout http://192.168.1.45/svn/duan_phanmem/trunk ten_thu_muc --username sv01
```

> SVN **không tự động đồng bộ** với GitHub. Khi cần cập nhật SVN, phải copy file mới vào thư mục đã checkout rồi commit thủ công.

---

## Cập nhật website trên máy ảo sau khi push GitHub

SVN và máy ảo Linux **không tự động cập nhật** khi có thay đổi trên GitHub. Phải thực hiện thủ công:

**SSH vào máy ảo:**

```cmd
ssh us@192.168.1.45
```

**Kéo code mới từ GitHub:**

```bash
cd ~/MoviePlex-Platform
git pull
```

**Restart container để áp dụng:**

```bash
docker compose restart php-apache
```

> Vì `docker-compose.yml` mount toàn bộ thư mục project vào container (`.:/var/www/html`), chỉ cần `git pull` + `restart` là website cập nhật ngay — **không cần build lại image**.

**Trường hợp ngoại lệ** — cần build lại (`--build`) khi:
- Sửa `Dockerfile`
- Thêm thư viện PHP mới
- Thay đổi `composer.json`

```bash
docker compose up -d --build
```

---

## Một số lệnh Docker hữu ích

```bash
# Xem container đang chạy
docker compose ps

# Xem log của web server
docker compose logs -f php-apache

# Truy cập shell bên trong container
docker exec -it movieplex_app bash

# Dừng tất cả container (giữ dữ liệu)
docker compose down

# Dừng và xóa toàn bộ dữ liệu (reset database)
docker compose down -v
docker compose up -d --build
```

---

## Truyền file lên server (SFTP)

SFTP chạy trên nền SSH, không cần cài thêm phần mềm. Mở CMD trên máy thật:

```cmd
sftp us@192.168.1.45
```

Một số lệnh trong SFTP:

```
ls              # xem danh sách file trên server
put ten_file    # upload file từ máy thật lên server
get ten_file    # download file từ server về máy thật
exit            # thoát
```

---

## Quản lý SVN Server

**Thông tin kết nối:**

| Thông tin | Giá trị |
| --------- | ------- |
| SVN URL   | http://192.168.1.45/svn/duan_phanmem/trunk |
| User sv01 | Quyền đọc/ghi toàn bộ repo |
| User sv02 | Quyền đọc/ghi toàn bộ repo |

**Checkout lần đầu (từ máy thật):**

```bash
svn checkout http://192.168.1.45/svn/duan_phanmem/trunk movieplex_local --username sv01
```

**Commit code:**

```bash
svn add ten_file      # chỉ cần khi thêm file MỚI
svn commit --username sv01 -m "Mô tả thay đổi"
```

**Cập nhật từ server (khi thành viên khác đã commit):**

```bash
svn update
```

> Nếu Apache trên máy ảo bị tắt, SVN sẽ không kết nối được. SSH vào máy ảo và chạy `sudo systemctl start apache2` để khởi động lại.

---

## Thành viên nhóm

| Tên | Vai trò | GitHub |
| --- | ------- | ------ |
|     |         |        |

---

## Ghi chú

Dự án phục vụ mục đích học tập — nhóm môn học tại Trường Đại học Công nghệ Giao thông Vận tải (UTT).
