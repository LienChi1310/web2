# 📋 GUHA Store - Hệ thống Bán Hàng Nước Hoa Trực Tuyến

## 📑 Mục Lục
1. [Tổng Quan Hệ Thống](#tổng-quan-hệ-thống)
2. [Kiến Trúc Dự Án](#kiến-trúc-dự-án)
3. [Công Nghệ Sử Dụng](#công-nghệ-sử-dụng)
4. [Cấu Trúc Cơ Sở Dữ Liệu](#cấu-trúc-cơ-sở-dữ-liệu)
5. [Các Module Chính](#các-module-chính)
6. [Tính Năng Hệ Thống](#tính-năng-hệ-thống)
7. [Luồng Hoạt Động](#luồng-hoạt-động)

---

## 🎯 Tổng Quan Hệ Thống

**GUHA Store** là một nền tảng thương mại điện tử chuyên bán nước hoa trực tuyến. Hệ thống cung cấp đầy đủ các chức năng cho khách hàng mua sắm và quản lý hành chính để người quản trị quản lý cửa hàng.

### Thông Tin Cơ Bản
- **Tên Hệ Thống**: GUHA Store - Perfume Shop
- **Loại Ứng Dụng**: E-Commerce Web Application
- **Ngôn Ngữ Chính**: PHP, JavaScript, HTML/CSS
- **Cơ Sở Dữ Liệu**: MySQL 8.0+
- **Khung Công Việc**: Custom PHP (không dùng framework như Laravel)
- **Triển Khai**: Docker + Docker Compose hoặc XAMPP (local)

---

## 🏗️ Kiến Trúc Dự Án

### Cấu Trúc Thư Mục

```
perfume1/
├── db/                          # Database initialization
│   └── init/
│       ├── dbperfume_clone.sql  # Full database schema
│       ├── dbperfume-final.sql  # Final version
│       └── dbperfume.sql        # Original version
├── full/                         # Main application
│   ├── index.php               # Frontend entry point
│   ├── composer.json           # PHP dependencies
│   ├── config_momo.json        # MoMo payment config
│   ├── admin/                  # Admin panel
│   │   ├── index.php           # Admin entry point
│   │   ├── login.php           # Admin login
│   │   ├── where.php           # Navigation
│   │   ├── config/
│   │   │   └── config.php      # Database configuration
│   │   ├── format/
│   │   │   └── format.php      # Formatting utilities
│   │   ├── css/                # Admin styling
│   │   ├── fonts/              # Font resources
│   │   ├── images/             # Image assets
│   │   ├── js/                 # Admin JavaScript
│   │   ├── modules/            # Admin functional modules
│   │   ├── pages/              # Admin page templates
│   │   ├── partials/           # Reusable components
│   │   └── vendors/            # Third-party libraries
│   ├── pages/                  # Frontend pages
│   │   ├── main.php            # Frontend routing
│   │   ├── base/               # Base templates
│   │   ├── handle/             # Request handlers
│   │   └── main/               # Page contents
│   ├── src/                    # Core business logic (PSR-4)
│   │   ├── Auth/               # Authentication
│   │   ├── Cart/               # Shopping cart logic
│   │   ├── Catalog/            # Product catalog
│   │   └── Order/              # Order processing
│   ├── assets/                 # Frontend assets
│   │   ├── css/                # Stylesheets
│   │   ├── images/             # Images
│   │   └── js/                 # Frontend JavaScript
│   ├── tests/                  # Unit tests
│   ├── vendor/                 # Composer dependencies
│   ├── coverage/               # Test coverage reports
│   ├── fpdf/                   # PDF generation library
│   ├── mail/                   # Email handling
│   ├── fonts/                  # Font files
│   ├── tfpdf/                  # Thai PDF library
│   └── carbon/                 # Date/time utilities
├── docker-compose.yml          # Docker orchestration (local)
├── Dockerfile                  # Main app Docker image
└── Dockerfile.webfull          # Alternative Docker config
```

### Kiến Trúc Phân Tầng

```
┌─────────────────────────────────────────┐
│         Frontend (Client-side)          │
│  HTML/CSS/JavaScript - User Interface   │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│      Pages & Route Controllers          │
│  pages/main.php, pages/main/*.php       │
│  pages/handle/*.php, admin/modules/     │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│    Business Logic Layer (src/)          │
│  Auth, Cart, Catalog, Order Services    │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│      Data Access Layer (MySQLi)         │
│  Database Connection & Queries          │
└──────────────────┬──────────────────────┘
                   │
┌──────────────────▼──────────────────────┐
│       MySQL Database (dbperfume)        │
│  19 Tables - Product, Order, User, etc  │
└─────────────────────────────────────────┘
```

---

## 💻 Công Nghệ Sử Dụng

### Backend
| Công Nghệ | Phiên Bản | Mục Đích |
|-----------|----------|---------|
| PHP | 8.3.26+ | Server-side logic |
| MySQL | 8.0.43+ | Relational database |
| MySQLi | Built-in | Database connection |
| PHPUnit | ^10.0 | Unit testing |
| PHPOffice/PHPSpreadsheet | ^1.29 | Excel export |
| FPDF | Latest | PDF generation |
| TFPDF | Latest | Thai character PDF |
| Carbon | - | Date/time handling |

### Frontend
| Công Nghệ | Chức Năng |
|-----------|---------|
| HTML5 | Markup |
| CSS3 | Styling + Responsive design |
| JavaScript (jQuery) | DOM manipulation & AJAX |
| Bootstrap | UI framework |
| Font Awesome 5 | Icons |
| Google Fonts (Manrope) | Typography |
| Ionicons | Additional icons |

### External Services
| Dịch Vụ | Mục Đích |
|--------|---------|
| VNPay | Payment gateway (Vietnam) |
| MoMo | Mobile payment (Vietnam) |
| Facebook Chat | Customer support |

### Infrastructure
| Công Cụ | Mục Đích |
|--------|---------|
| Docker | Containerization |
| Docker Compose | Orchestration |
| XAMPP | Local development |
| Git | Version control |

---

## 🗄️ Cấu Trúc Cơ Sở Dữ Liệu

### Tổng Quan
- **Database**: `dbperfume_clone`
- **Character Set**: UTF8MB4 (Unicode support for Vietnamese)
- **Total Tables**: 19
- **Total Rows (Dữ liệu mẫu)**: ~350 đơn hàng, 42+ khách hàng, 20+ sản phẩm

### Các Bảng Chính

#### 1. **account** - Tài Khoản Users (7 cột)
```sql
Mục Đích: Lưu trữ thông tin tài khoản (Admin, Staff, Customer)
Kiểu Dữ Liệu:
  - account_id: INT(11) - ID duy nhất (Primary Key, Auto Increment)
  - account_name: VARCHAR(255) UTF8MB4 - Tên tài khoản
  - account_password: VARCHAR(100) UTF8MB4 - Mật khẩu (hashed - MD5, SHA1 hoặc bcrypt)
  - account_email: VARCHAR(255) UTF8MB4 - Email
  - account_phone: VARCHAR(20) UTF8MB4 DEFAULT NULL - Số điện thoại
  - account_type: INT(11) - Loại (0=Customer, 1=Staff, 2=Admin)
  - account_status: INT(11) DEFAULT 1 - Trạng thái (1=Active, 0=Inactive, -1=Deleted)

Dữ Liệu Mẫu:
  - ID 1: admin (account_type=2, status=1)
  - Tổng: 21 accounts (1 admin + 20 users/staff)
```

#### 2. **product** - Sản Phẩm Nước Hoa (14 cột)
```sql
Mục Đích: Thông tin chi tiết sản phẩm nước hoa
Kiểu Dữ Liệu:
  - product_id: INT(11) - ID duy nhất (Primary Key, Auto Increment)
  - product_name: VARCHAR(100) UTF8MB4 NOT NULL - Tên sản phẩm
  - product_category: INT(11) NOT NULL - FK: category.category_id
  - product_brand: INT(11) NOT NULL - FK: brand.brand_id
  - capacity_id: INT(11) NOT NULL - FK: capacity.capacity_id
  - product_quantity: INT(11) NOT NULL DEFAULT 0 - Số lượng tồn kho
  - quantity_sales: INT(11) NOT NULL DEFAULT 0 - Số lượng đã bán
  - product_price_import: INT(11) NOT NULL - Giá nhập từ nhà cung cấp
  - product_profit_percent: INT(11) NOT NULL DEFAULT 20 - % lợi nhuận
  - product_price: INT(11) NOT NULL - Giá bán lẻ
  - product_sale: INT(11) NOT NULL - % giảm giá
  - product_description: TEXT NOT NULL - Mô tả chi tiết
  - product_image: TEXT NOT NULL - Đường dẫn ảnh chính
  - product_status: INT(11) NOT NULL DEFAULT 1 - Trạng thái (1=Active, 0=Inactive)

Dữ Liệu Mẫu:
  ✅ 14 sản phẩm (Brands: Chanel, Gucci, Louis Vuitton, Dior)
```

#### 3. **orders** - Đơn Hàng (8 cột)
```sql
Mục Đích: Quản lý đơn hàng khách hàng
Kiểu Dữ Liệu:
  - order_id: INT(11) - ID đơn hàng (Primary Key)
  - order_code: INT(11) - Mã đơn hàng
  - order_date: VARCHAR(50) - Ngày đặt (YYYY-MM-DD HH:MM:SS)
  - account_id: INT(11) - ID khách hàng (FK: account)
  - delivery_id: INT(11) - FK: delivery.delivery_id
  - total_amount: INT(11) - Tổng tiền (VND)
  - order_type: INT(11) - Loại đơn (0-5)
  - order_status: INT(11) - Trạng thái (3=Delivered, 2=Shipping, 1=Pending, -1=Cancelled, 0=New)

Ghi Chú:
  ❌ KHÔNG CÓ: payment_method, payment_status, shipping_address
  ✅ Thông tin giao: xem delivery table; Thanh toán: xem vnpay/momo
  ✅ 160 đơn hàng (mẫu)
```

#### 4. **order_detail** - Chi Tiết Đơn Hàng (5 cột)
```sql
Mục Đích: Chi tiết sản phẩm trong từng đơn hàng
Kiểu Dữ Liệu:
  - order_detail_id: INT(11) - ID (Primary Key)
  - order_code: INT(11) - FK: orders.order_code
  - product_id: INT(11) - FK: product.product_id
  - product_quantity: INT(11) - Số lượng mua
  - product_price: INT(11) - Giá sản phẩm khi mua
  - product_sale: INT(11) - Phần trăm giảm giá (%)

Ghi Chú:
  ❌ KHÔNG CÓ: order_id (dùng order_code)
  ✅ 199 chi tiết đơn hàng (mẫu)
```

#### 5. **customer** - Thông Tin Khách Hàng
```sql
Mục Đích: Thông tin chi tiết khách hàng (mở rộng từ account)
Cột Chính:
  - customer_id: int(11) - ID duy nhất
  - account_id: int(11) - Liên kết với tài khoản (account.account_id)
  - customer_name: varchar(100) UTF8MB4 - Tên đầy đủ khách hàng
  - customer_gender: int(11) - Giới tính (0=Nam, 1=Nữ, 2=Khác)
  - customer_email: varchar(100) UTF8MB4 - Email liên hệ
  - customer_phone: varchar(50) UTF8MB4 - Số điện thoại
  - customer_address: text UTF8MB4 - Địa chỉ đầy đủ (Hiện tại DB chỉ lưu 1 địa chỉ)

Dữ Liệu:
  ✅ 27 khách hàng

Lưu Ý:
  ⚠️ Hiện tại database N/A các trường "city/province", "district", "ward"
  Toàn bộ địa chỉ được ghi chung trong trường "customer_address"
  Không có trường "customer_status" như tài liệu cũ
```

#### 6. **category** - Danh Mục Sản Phẩm (4 cột)
```sql
Mục Đích: Phân loại nước hoa theo giới tính
Kiểu Dữ Liệu:
  - category_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - category_name: VARCHAR(100) NOT NULL - Tên danh mục (Nam, Nữ, Unisex)
  - category_description: TEXT NOT NULL - Mô tả danh mục
  - category_image: VARCHAR(100) NOT NULL - Hình ảnh danh mục

Dữ Liệu:
  - ID 2: Nước hoa nam
  - ID 3: Nước hoa nữ
  ✅ 2 categories
```

#### 7. **brand** - Thương Hiệu (2 cột)
```sql
Mục Đích: Tập hợp các thương hiệu nước hoa
Kiểu Dữ Liệu:
  - brand_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - brand_name: VARCHAR(50) NOT NULL - Tên thương hiệu

Dữ Liệu:
  - Chanel, Gucci, Louis Vuitton, Dior
  ✅ 4 brands
```

#### 8. **capacity** - Dung Tích Nước Hoa (2 cột)
```sql
Mục Đích: Các kích cỡ/dung tích sản phẩm
Kiểu Dữ Liệu:
  - capacity_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - capacity_name: VARCHAR(50) NOT NULL - Tên dung tích (10ml, 30ml, 50ml, 100ml, v.v.)

Dữ Liệu:
  ✅ 10 dung tích có sẵn
```

#### 9. **collection** - Bộ Sưu Tập (7 cột)
```sql
Mục Đích: Nhóm sản phẩm thành các bộ sưu tập/collection
Kiểu Dữ Liệu:
  - collection_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - collection_name: VARCHAR(100) NOT NULL - Tên bộ sưu tập
  - collection_keyword: VARCHAR(100) NOT NULL - Keywords tìm kiếm
  - collection_image: VARCHAR(100) NOT NULL - Hình ảnh collection
  - collection_description: VARCHAR(255) NOT NULL - Mô tả
  - collection_order: INT(11) NOT NULL - Thứ tự hiển thị
  - collection_type: INT(11) NOT NULL - Loại collection

Dữ Liệu:
  ✅ 1 bộ sưu tập (Chanel Collection)
```

#### 10. **article** - Bài Viết/Blog (8 cột)
```sql
Mục Đích: Nội dung blog marketing
Kiểu Dữ Liệu:
  - article_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - article_author: VARCHAR(100) NOT NULL - Tên tác giả
  - article_title: VARCHAR(255) NOT NULL - Tiêu đề bài viết
  - article_summary: TEXT NOT NULL - Tóm tắt nội dung
  - article_content: TEXT NOT NULL - Nội dung HTML
  - article_image: VARCHAR(100) NOT NULL - Ảnh minh họa
  - article_date: DATE NOT NULL - Ngày đăng
  - article_status: INT(11) NOT NULL - Trạng thái (0=Draft, 1=Published)

Dữ Liệu:
  ✅ 4 bài viết
```

#### 11. **comment** - Bình Luận (7 cột)
```sql
Mục Đích: Bình luận/comments trên bài viết blog
Kiểu Dữ Liệu:
  - comment_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - article_id: INT(11) NOT NULL - FK: article.article_id
  - comment_name: VARCHAR(50) NOT NULL - Tên người bình luận
  - comment_email: VARCHAR(50) NOT NULL - Email
  - comment_content: TEXT NOT NULL - Nội dung bình luận
  - comment_date: DATE NOT NULL - Ngày bình luận
  - comment_status: INT(11) NOT NULL - Trạng thái (0=Pending, 1=Approved)

Dữ Liệu:
  ✅ 6 bình luận
```

#### 12. **evaluate** - Đánh Giá/Rating (8 cột)
```sql
Mục Đích: Đánh giá sản phẩm từ khách hàng
Kiểu Dữ Liệu:
  - evaluate_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - account_id: INT(11) NOT NULL - FK: account.account_id
  - product_id: INT(11) NOT NULL - FK: product.product_id
  - account_name: VARCHAR(50) NOT NULL - Tên người rating
  - evaluate_rate: INT(11) NOT NULL - Số sao (1-5) ⭐
  - evaluate_content: TEXT NOT NULL - Nội dung review
  - evaluate_date: VARCHAR(50) NOT NULL - Ngày đánh giá (YYYY-MM-DD HH:MM:SS)
  - evaluate_status: INT(11) NOT NULL - Trạng thái (0=Pending, 1=Approved)

Dữ Liệu:
  ✅ 10 bản đánh giá
```

#### 13. **delivery** - Giao Hàng (6 cột)
```sql
Mục Đích: Thông tin địa chỉ giao hàng của khách hàng
Kiểu Dữ Liệu:
  - delivery_id: INT(11) NOT NULL (Primary Key, NON auto_increment)
  - account_id: INT(11) NOT NULL - FK: account.account_id
  - delivery_name: VARCHAR(100) NOT NULL - Tên người nhận
  - delivery_phone: VARCHAR(20) NOT NULL - Số điện thoại
  - delivery_address: VARCHAR(100) NOT NULL - Địa chỉ giao hàng
  - delivery_note: VARCHAR(100) NOT NULL - Ghi chú thêm

Ghi Chú:
  ❌ KHÔNG CÓ: order_id, delivery_date, delivery_status, delivery_tracking_number
  ✅ 213 địa chỉ giao hàng
  ⚠️ Dùng account_id để liên kết: 1 account có thể có nhiều delivery addresses
```

#### 14. **inventory** - Quản Lý Kho (3 cột)
```sql
Mục Đích: Nhật ký nhập/xuất hàng
Kiểu Dữ Liệu:
  - inventory_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - inventory_date: DATETIME NULL - Ngày lập phiếu
  - inventory_status: INT(11) NULL DEFAULT 0 - Loại phiếu (1=Nhập, -1=Xuất, 0=Khác)

Ghi Chú:
  ❌ KHÔNG CÓ: inventory_type, quantity_before, quantity_after
  ✅ 6 phiếu kho
```

#### 15. **inventory_detail** - Chi Tiết Kho (5 cột)
```sql
Mục Đích: Chi tiết sản phẩm trong mỗi phiếu nhập/xuất kho
Kiểu Dữ Liệu:
  - id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - inventory_id: INT(11) NULL - FK: inventory.inventory_id
  - product_id: INT(11) NULL - FK: product.product_id
  - quantity: INT(11) NULL - Số lượng nhập/xuất
  - price_import: INT(11) NULL - Giá nhập/xuất tại thời điểm

Ghi Chú:
  ✅ 8 dòng chi tiết kho
```

#### 16. **vnpay** - Lịch Sử VNPay (11 cột)
```sql
Mục Đích: Theo dõi giao dịch thanh toán VNPay (Cổng thanh toán Vietnam)
Kiểu Dữ Liệu:
  - vnp_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - vnp_amount: VARCHAR(50) NOT NULL - Số tiền giao dịch (VND)
  - vnp_bankcode: VARCHAR(50) NOT NULL - Mã ngân hàng (MB, ACB, TCB, v.v.)
  - vnp_banktranno: VARCHAR(50) NOT NULL - Mã tham chiếu từ ngân hàng
  - vnp_cardtype: VARCHAR(50) NOT NULL - Loại thẻ (ATM, CC, v.v.)
  - vnp_orderinfo: VARCHAR(100) NOT NULL - Thông tin đơn hàng
  - vnp_paydate: VARCHAR(50) NOT NULL - Ngày/giờ thanh toán (YYYYMMDDHHMMSS)
  - vnp_tmncode: VARCHAR(50) NOT NULL - Terminal code VNPay
  - vnp_transactionno: VARCHAR(50) NOT NULL - Mã giao dịch VNPay
  - order_code: INT(11) NOT NULL - FK: orders.order_code
  - payment_status: INT(11) NOT NULL - Trạng thái (0=Chờ, 1=Thành công)

Ghi Chú:
  ✅ 48 giao dịch VNPay
```

#### 17. **momo** - Lịch Sử MoMo (10 cột)
```sql
Mục Đích: Theo dõi giao dịch thanh toán MoMo (Mobile Money)
Kiểu Dữ Liệu:
  - momo_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - partner_code: VARCHAR(50) NOT NULL - Mã đối tác MoMo
  - order_code: INT(11) NOT NULL - FK: orders.order_code
  - momo_amount: VARCHAR(50) NOT NULL - Số tiền giao dịch (VND)
  - order_info: VARCHAR(100) NOT NULL - Thông tin đơn hàng
  - order_type: VARCHAR(50) NOT NULL - Loại đơn hàng
  - trans_id: INT(11) NOT NULL - Mã giao dịch MoMo
  - payment_date: VARCHAR(50) NOT NULL - Ngày thanh toán
  - pay_type: VARCHAR(50) NOT NULL - Loại thanh toán
  - payment_status: INT(11) NOT NULL - Trạng thái (0=Chờ, 1=Thành công)

Ghi Chú:
  ✅ 3 giao dịch MoMo
```

#### 18. **metrics** - Thống Kê Hệ Thống (5 cột)
```sql
Mục Đích: Lưu trữ KPI và metrics hệ thống theo ngày
Kiểu Dữ Liệu:
  - metric_id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - metric_date: DATE NOT NULL - Ngày thống kê
  - metric_order: INT(11) NOT NULL - Tổng số đơn hàng trong ngày
  - metric_sales: VARCHAR(100) NOT NULL - Tổng doanh số (VND)
  - metric_quantity: INT(11) NOT NULL - Tổng sản phẩm bán trong ngày

Ghi Chú:
  ✅ 39 bản ghi metric (từ 2023-2026)
```

#### 19. **user_cart_items** - Giỏ Hàng Session (4 cột)
```sql
Mục Đích: Lưu trữ giỏ hàng của user (nếu ưu tiên DB hơn $_SESSION)
Kiểu Dữ Liệu:
  - id: INT(11) NOT NULL (Primary Key, Auto Increment)
  - account_email: VARCHAR(190) NOT NULL (Indexed) - Email/account khách hàng
  - product_id: INT(11) NOT NULL - FK: product.product_id
  - qty: INT(11) NOT NULL DEFAULT 1 - Số lượng sản phẩm trong giỏ

Ghi Chú:
  ⚠️ Bảng hiện tại TRỐNG (0 records)
  ✅ Tồn tại nhưng không sử dụng
  ⚠️ Hệ thống ưu tiên dùng $_SESSION['cart'] cho session-based cart
  💡 Tùy chọn cho tương lai: có thể kích hoạt persistent database-backed cart
```

---

## 🔧 Các Module Chính

### 1. **Module Auth** (`src/Auth/`)
**Mục Đích**: Xác thực và quản lý cấp mật khẩu

**Tệp**:
- `PasswordVerifier.php` - Xác minh mật khẩu
  - Hỗ trợ multiple hash algorithms: bcrypt, SHA1, MD5
  - Tương thích với legacy passwords

**Tính Năng**:
```php
- verify(string $plain, string $dbHash): bool
  Kiểm tra mật khẩu nhập vào với hash trong DB
```

---

### 2. **Module Cart** (`src/Cart/`)
**Mục Đích**: Quản lý giỏ hàng

**Tệp**:
- `CartService.php` - Xử lý logic giỏ hàng

**Tính Năng**:
```php
- increase(array $cart, int $productId, int $stockMax): array
  Tăng số lượng sản phẩm (kiểm tra tồn kho)
  
- decrease(array $cart, int $productId): array
  Giảm số lượng sản phẩm (xóa nếu xuống 0)
  
- updateQty(array $cart, int $productId, int $qtyInput, int $stockMax): array
  Cập nhật số lượng bằng tay
  
- removeItem(array $cart, int $productId): array
  Xóa sản phẩm khỏi giỏ
```

---

### 3. **Module Catalog** (`src/Catalog/`)
**Mục Đích**: Lọc và hiển thị danh sách sản phẩm

**Tệp**:
- `CatalogFilter.php` - Lọc sản phẩm
- `ProductCountQueryBuilder.php` - Đếm sản phẩm

**Tính Năng**:
```php
CatalogFilter:
- normalizePage(int $pagenumber): array
  Chuẩn hóa trang, tính offset
  
- normalizePriceRange(int $from, int $to): array
  Xác thực và chuẩn hóa khoảng giá
  
- normalizePriceSort(string $pricesort): string
  Xác thực sắp xếp (asc/desc)
```

**Hằng Số**:
```php
- DEFAULT_MIN = 0 VND
- DEFAULT_MAX = 15,000,000 VND
- DEFAULT_PER_PAGE = 9
```

---

### 4. **Module Order** (`src/Order/`)
**Mục Đích**: Tính toán giá và kiểm tra tồn kho

**Tệp**:
- `PricingService.php` - Tính giá tiền

**Tính Năng**:
```php
- priceAfterSale(float $price, float $salePercent): float
  Tính giá sau khi áp dụng giảm giá (%)
  
- cartTotal(array $cartItems): float
  Tính tổng tiền giỏ hàng (xét từng item có sale riêng)
  
- isQtyWithinStock(int $qtyInCart, int $stockQty): bool
  Kiểm tra số lượng có hợp lệ không
```

---

## 📱 Tính Năng Hệ Thống

### A. Chức Năng Khách Hàng (Frontend)

#### 1. **Trang Chủ** (`pages/main/home.php`)
- Hiển thị slider sản phẩm nổi bật
- Thẻ khuyến mãi hôm nay
- Các bộ sưu tập hot
- Blog bài viết mới nhất
- Liên kết nhanh đến các danh mục

#### 2. **Xem Danh Sách Sản Phẩm** (`pages/main/products.php`)
- Hiển thị lưới sản phẩm
- Filter theo tiêu chí:
  - **Khoảng giá**: 0 - 15,000,000 VND
  - **Thương hiệu**: Chanel, Gucci, Louis Vuitton, Dior
  - **Dung tích**: 10ml - 100ml
  - **Danh mục**: Nam/Nữ
- **Sắp xếp**: Mặc định, Giá tăng/giảm
- **Phân trang**: 9 sản phẩm/trang

#### 3. **Chi Tiết Sản Phẩm** (`pages/main/product_detail.php`)
- Hiển thị ảnh sản phẩm (zoom, gallery)
- Thông tin sản phẩm đầy đủ
- Đánh giá từ khách hàng (sao 1-5)
- Bình luận/review từ users
- Nút "Thêm vào giỏ hàng", "Mua ngay"
- Sản phẩm tương tự (recommendation)

#### 4. **Giỏ Hàng** (`pages/base/cart.php`)
- Hiển thị tất cả sản phẩm trong giỏ
- Chỉnh sửa số lượng (tăng/giảm/xóa)
- Tính tổng tiền (xét sale của từng sản phẩm)
- Nút "Thanh toán"
- Tiếp tục mua sắm

#### 5. **Thanh Toán** (`pages/main/checkout.php`)
- Form nhập thông tin giao hàng
- Chọn phương thức thanh toán:
  - Thanh toán khi nhận (COD)
  - VNPay (cổng thanh toán online)
  - MoMo (Mobile money)
- Xem lại chi tiết đơn hàng
- Nút "Đặt hàng"

#### 6. **Đăng Nhập** (`pages/base/login.php`)
- Form email/username + password
- Đăng nhập = session cookie
- Link "Quên mật khẩu"
- Link "Đăng ký"

#### 7. **Đăng Ký** (`pages/base/register.php`)
- Form tạo tài khoản mới
- Validate thông tin nhập
- Hash mật khẩu an toàn
- Tự động đăng nhập sau đăng ký

#### 8. **Tài Khoản Cá Nhân** (`pages/main/my_account.php`)
- **Tab Thông Tin**: Sửa tên, email, phone, địa chỉ
- **Tab Đơn Hàng**: Lịch sử đơn hàng
- **Tab Theo Dõi**: Trạng thái giao hàng
- **Tab Cài Đặt**: Đổi mật khẩu, cài đặt notification

#### 9. **Danh Mục Theo Thương Hiệu** (`pages/main/product_brand.php`)
- Lọc sản phẩm theo brand
- Áp dụng các filter khác

#### 10. **Danh Mục Theo Loại** (`pages/main/product_category.php`)
- Lọc sản phẩm theo category (Nam/Nữ)
- Áp dụng các filter khác

#### 11. **Tìm Kiếm** (`pages/main/search.php`)
- Tìm kiếm theo keyword trên tên sản phẩm
- Hiển thị kết quả tương ứng

#### 12. **Blog/Bài Viết** (`pages/main/article.php`)
- Danh sách bài viết marketing
- Xem chi tiết (title, content, author, date)
- Bình luận trên bài viết

#### 13. **Về Chúng Tôi** (`pages/main/about.php`)
- Giới thiệu shop, lịch sử
- Tầm nhìn, sứ mệnh
- Đội ngũ

#### 14. **Liên Hệ** (`pages/main/contact.php`)
- Form liên hệ (name, email, message)
- Thông tin liên hệ của shop
- Facebook Messenger chat

#### 15. **Quên Mật Khẩu** (`pages/main/forget_password.php`)
- Nhập email
- Gửi link reset mật khẩu
- Tạo mật khẩu mới

---

### B. Chức Năng Admin (Backend)

#### **Modul Sản Phẩm** (`admin/modules/product/`)
- `them.php` - Thêm sản phẩm mới
- `sua.php` - Chỉnh sửa sản phẩm
- `xuly.php` - Xử lý thêm/sửa/xóa
- `lietke.php` - Danh sách sản phẩm với phân trang
- `tonkho.php` - Quản lý tồn kho
- `timkiem.php` - Tìm kiếm sản phẩm
- `import.php` - Import từ Excel
- `export.php` - Export ra Excel
- `uploads/` - Thư mục lưu ảnh sản phẩm

**Tính Năng**:
- Create: Thêm sản phẩm (tên, giá, sale, ảnh, mô tả, category, brand)
- Read: Liệt kê tất cả sản phẩm
- Update: Sửa thông tin sản phẩm, tồn kho
- Delete: Xóa sản phẩm
- Bulk: Import/export Excel
- Search: Tìm kiếm theo tên/code/brand

#### **Modul Đơn Hàng** (`admin/modules/order/`)
- `lietke.php` - Danh sách đơn hàng
- `chitiet.php` - Chi tiết đơn hàng online
- `chitiet_online.php` - Chi tiết đơn hàng
- `donhangtructiep.php` - Đơn hàng tại quầy (offline)
- `them.php` - Tạo đơn hàng manual
- `xuly.php` - Cập nhật trạng thái
- `timkiem.php` - Tìm kiếm đơn hàng
- `lichsuthanhtoan.php` - Lịch sử thanh toán
- `truyvansoluong.php` - Kiểm tra số lượng

**Tính Năng**:
- Create: Tạo đơn hàng (manual hoặc từ online)
- Read: Xem danh sách và chi tiết
- Update: Thay đổi trạng thái (Pending → Confirmed → Shipping → Delivered)
- Payment: Quản lý thanh toán (COD, VNPay, MoMo)
- Delivery: Quản lý vận chuyển
- Refund: Xử lý hoàn tiền

#### **Modul Khách Hàng** (`admin/modules/customer/`)
- `lietke.php` - Danh sách khách hàng
- `xuly.php` - Update thông tin
- `export.php` - Export Excel
- `customer-data_2025-11-20.xlsx` - File template

**Tính Năng**:
- Read: Xem danh sách khách hàng liên hệ
- Create: Thêm khách hàng (group import)
- Update: Sửa thông tin (email, phone, address)
- Export: Xuất dữ liệu

#### **Modul Thương Hiệu** (`admin/modules/brand/`)
- CRUD thương hiệu (Chanel, Gucci, Louis Vuitton, Dior)

#### **Modul Danh Mục** (`admin/modules/category/`)
- CRUD danh mục (Nước hoa nam, Nước hoa nữ)

#### **Modul Bộ Sưu Tập** (`admin/modules/collection/`)
- CRUD bộ sưu tập (tên, ảnh, mô tả)

#### **Modul Tài Khoản** (`admin/modules/account/`)
- CRUD tài khoản (Admin, Staff, Customer)
- Gán quyền hạn

#### **Modul Cài Đặt** (`admin/modules/settings/`)
- Cấu hình chung
- Thông tin shop
- Cách thức vận chuyển

#### **Modul Báo Cáo** (`admin/modules/report/`)
- Doanh thu theo kỳ
- Top sản phẩm bán chạy
- Top khách hàng
- Phân tích xu hướng

#### **Modul Thống Kê** (`admin/modules/dashboard.php`)
- KPI tổng quan (hôm nay, tháng, năm)
- Biểu đồ doanh thu
- Biểu đồ đơn hàng theo loại
- Biểu đồ TOP 10 sản phẩm

#### **Modul Kho** (`admin/modules/inventory/`)
- Nhập hàng
- Xuất hàng
- Kiểm kho
- Lịch sử kho

#### **Modul Menu** (`admin/modules/menu.php`)
- Quản lý cấu trúc menu sidebar
- Active/inactive menu items

---

### C. Ngôn Ngữ & Hỗ Trợ

#### **Frontend Pages**
- 🇻🇳 **TIẾNG VIỆT** (HTML lang="vi")
- Responsive design (mobile, tablet, desktop)

#### **Admin Pages**
- 🇻🇳 Tiếng Việt
- Bảng điều khiển người quản trị
- Thống kê chi tiết
- Xuất báo cáo (PDF, Excel)

#### **Ghi Chú**
- Facebook Chat: `vi_VN` (Tiếng Việt - Việt Nam)
- Toàn bộ text, label, message: Tiếng Việt
- Nếu cần multi-language cần implement thêm i18n library

---

## 🔄 Luồng Hoạt Động

### 1. **Luồng Mua Hàng**

```
Customer → Browse Products
         → Filter/Search
         → View Product Detail
         → Add to Cart
         → Proceed to Checkout
         → Enter Shipping Info
         → Choose Payment Method:
            ├─ COD (Cash on Delivery)
            ├─ VNPay (Online Payment) → VNPay Gateway → Callback
            └─ MoMo (Mobile Payment) → MoMo Gateway → Callback
         → Place Order
         → Order Confirmation Email
         → Order Status Updates (Email/SMS)
         → Delivery → Received
         → Rating & Review
```

### 2. **Luồng Quản Lý Đơn Hàng**

```
Online Order Placed
    ↓
Pending → Admin Confirms
    ↓
Confirmed → Warehouse Prepares
    ↓
Shipping → Delivery Partner
    ↓
In Transit → Customer Tracking
    ↓
Delivered → Customer Receives
    ↓
Complete/Return → Refund (if needed)
```

### 3. **Luồng Thanh Toán VNPay**

```
Customer Click "Pay VNPay"
    ↓
Create Transaction (vnpay table)
    ↓
Redirect to VNPay Gateway
    ↓
Customer Enters Card Info (VNPay)
    ↓
VNPay Authenticates & Charges
    ↓
Callback to Server (payment_result.php)
    ↓
Verify Signature
    ↓
Update Order Status (PAID)
    ↓
Update vnpay table (SUCCESS/FAILURE)
    ↓
Redirect Customer to Order Confirmation
```

### 4. **Luồng Thanh Toán MoMo**

```
Customer Click "Pay MoMo"
    ↓
Create Transaction (momo table)
    ↓
Redirect to MoMo Gateway
    ↓
Customer Approves in MoMo App
    ↓
MoMo Returns Status
    ↓
Callback to Server (payment_result.php)
    ↓
Verify Signature
    ↓
Update Order Status (PAID)
    ↓
Update momo table (SUCCESS/FAILURE)
    ↓
Redirect Customer
```

### 5. **Luồng Tìm Kiếm & Lọc Sản Phẩm**

```
User Enters Query
    ↓
pages/main.php (Routing)
    ↓
Determine Page Type (search/products/brand/category)
    ↓
Get GET Parameters (keyword, page, priceFrom, priceTo, sort)
    ↓
CatalogFilter::normalizePage() → Calculate offset
    ↓
CatalogFilter::normalizePriceRange() → Validate prices
    ↓
CatalogFilter::normalizePriceSort() → Validate sort
    ↓
Query Database with Filters
    ↓
Apply Pagination (LIMIT, OFFSET)
    ↓
Return Results to View
    ↓
Template Renders Product List
```

### 6. **Luồng Giỏ Hàng**

```
Add to Cart →
    ├─ SessionCart: Store in $_SESSION['cart']
    └─ DatabaseCart: Store in user_cart_items (optional)
    ↓
View Cart → Fetch from SessionCart
    ↓
Update Qty (User adjusts) →
    ├─ CartService::increase()
    ├─ CartService::decrease()
    └─ CartService::updateQty()
    ↓
PricingService::cartTotal() → Calculate Total
    ↓
Proceed to Checkout
    ↓
Clear Cart (after order complete)
```

### 7. **Luồng Xác Thực Người Dùng**

```
Login Form Submission
    ↓
validate input (email/username, password)
    ↓
Query account table
    ↓
PasswordVerifier::verify() (Support multiple hash algo)
    ↓
Success? → Set $_SESSION['login'], $_SESSION['account_id']
    ↓
Generate session cookie (guha)
    ↓
Redirect to Dashboard/Home
    ↓
On Logout → session_unset() → session_destroy()
```

---

## 🧪 Testing

### Unit Tests (`tests/`)

**Test Files**:
1. `CartServiceTest.php` - Kiểm tra logic giỏ hàng
   - Test tăng/giảm số lượng
   - Test kiểm tra tồn kho
   - Test xóa item

2. `CatalogFilterTest.php` - Kiểm tra logic lọc sản phẩm
   - Test normalize page
   - Test normalize price range
   - Test normalize sort

3. `PasswordVerifierTest.php` - Kiểm tra xác thực mật khẩu
   - Test bcrypt
   - Test SHA1
   - Test MD5
   - Test plaintext

4. `PricingServiceTest.php` - Kiểm tra tính toán giá
   - Test price after sale
   - Test cart total
   - Test quantity validation

5. `ProductCountQueryBuilderTest.php` - Kiểm tra đếm sản phẩm

**Coverage Reports**: `coverage/` (HTML format)

**Chạy Tests**:
```bash
cd full/
vendor/bin/phpunit tests/
# Xem coverage report
vendor/bin/phpunit tests/ --coverage-html=coverage
```

---

## 🚀 Triển Khai & Cấu Hình

### Local Development (XAMPP)

```bash
# 1. Copy project vào htdocs
cp -r perfume1/full C:\xampp\htdocs\

# 2. Import database
- Mở phpMyAdmin
- Create database: dbperfume_clone
- Import: db/init/dbperfume_clone.sql

# 3. Update config
edit admin/config/config.php
- $host = 'localhost'
- $db = 'dbperfume_clone'

# 4. Start XAMPP (Apache + MySQL)
# 5. Open http://localhost/full/
```

### Docker Deployment

```bash
# 1. Build & Run
docker-compose up -d

# 2. Access
- Frontend: http://localhost
- Admin: http://localhost/admin/
- PHPMyAdmin: http://localhost:8081

# 3. Container names:
- webfull (PHP + Web Server)
- db (MySQL)
- phpmyadmin (Database GUI)
```

**docker-compose.yml**:
```yaml
version: '3.8'
services:
  webfull:
    build: .
    ports:
      - "80:80"
    environment:
      DOCKERIZED: 1
    volumes:
      - ./full:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: dbperfume_clone
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - ./db/init:/docker-entrypoint-initdb.d
      - db_data:/var/lib/mysql
    ports:
      - "3306:3306"

  phpmyadmin:
    image: phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root
    depends_on:
      - db

volumes:
  db_data:
```

---

## 🔐 Bảo Mật

### Session Management
```php
- Session name: 'guha'
- Session mode: Strict (use_strict_mode = 1)
- Cookie: HTTP-only, SameSite=Lax
- Timeout: Default 24 minutes
```

### Password Security
```php
- Hash algo: password_hash() (bcrypt by default)
- Support legacy: SHA1, MD5
- Verify: PasswordVerifier::verify()
```

### Database
```php
- Character set: UTF8MB4 (Unicode safe)
- Prepared statements recommended (prevent SQL injection)
- Real escape string for legacy code
```

---

## 📊 Database Queries Example

### 1. Lấy Sản Phẩm Theo Giá

```sql
SELECT product_id, product_name, product_price, product_sale
FROM product
WHERE product_price BETWEEN 1000000 AND 5000000
  AND product_status = 1
ORDER BY product_price ASC
LIMIT 9 OFFSET 0;
```

### 2. Tính Tổng Doanh Thu Hôm Nay

```sql
SELECT SUM(order_total) as revenue
FROM orders
WHERE DATE(order_date) = CURDATE()
  AND order_status != 'Cancelled';
```

### 3. TOP 10 Sản Phẩm Bán Chạy

```sql
SELECT p.product_id, p.product_name, COUNT(od.order_detail_id) as qty_sold
FROM product p
JOIN order_detail od ON p.product_id = od.product_id
GROUP BY p.product_id
ORDER BY qty_sold DESC
LIMIT 10;
```

### 4. Lịch Sử Đơn Hàng Của Khách Hàng

```sql
SELECT o.order_id, o.order_date, o.order_total, o.order_status
FROM orders o
WHERE o.account_id = ?
ORDER BY o.order_date DESC;
```

---

## 📝 Ghi Chú Quan Trọng

#### ⚠️ **Khác Biệt Thực Tế vs Lý Thuyết**
- **Ngôn ngữ**: 
  - ❌ Documentation nói hỗ trợ English + Vietnamese → **SAI**
  - ✅ Thực tế: **CHỈ TIẾNG VIỆT** (HTML lang="vi")
  - Không có language switcher hay i18n
  
- **Bảng customer**:
  - ❌ Documentation liệt kê: customer_city/province, district, ward, customer_status → **SAI**
  - ✅ Thực tế: Chỉ có `customer_address` (text) chứa toàn bộ địa chỉ
  - Không có trường `customer_status` trong database

1. **Session Cookies**:
   - Tất cả pages sử dụng chung session name: `guha`
   - Session lưu trữ: user info, cart (optional)

2. **File Uploads**:
   - Ảnh sản phẩm: `admin/modules/product/uploads/`
   - Max size: Được cấu hình trong `admin/config/config.php` hoặc PHP ini

3. **Payment Integration**:
   - VNPay Config: `pages/handle/config_vnpay.php`
   - MoMo Config: `config_momo.json`
   - Test/Live: Cần chuyển URL/credential khi production

4. **Email Notifications**:
   - Mail handler: `mail/sendmail.php` (PHPMailer)
   - Gửi khi: Order placed, Status updated, Password reset

5. **PDF Generation**:
   - Invoice PDF: `fpdf/` hoặc `tfpdf/`
   - Dùng cho hoá đơn, báo cáo

6. **Performance**:
   - Images tối ưu (compress trước upload)
   - DB indexes trên: product_id, order_id, account_id, category_id
   - Cache tĩnh cho CSS/JS

---

## 🎨 Frontend Assets

### CSS Files
- `assets/css/helper.css` - Utilities
- `assets/css/layout.css` - Grid & layout
- `assets/css/main.css` - Main styles
- `assets/css/responsive.css` - Mobile responsive
- `assets/css/login.css` - Login page
- `assets/css/toast.css` - Toast notifications

### JavaScript Files
- `assets/js/main.js` - Main logic
- `assets/js/navigation.js` - Menu navigation
- `assets/js/select-number.js` - Quantity selector
- `assets/js/payment.js` - Payment form handling
- `assets/js/validator.js` - Form validation
- `assets/js/toast_message.js` - Notification system

### External Libraries
- jQuery 3.1.1
- Bootstrap (via CSS)
- Font Awesome 5
- Ionicons 5.5.2
- Google Fonts (Manrope)

---

## 📞 Liên Hệ & Support

| Thông Tin | Chi Tiết |
|-----------|---------|
| Admin URL | `/admin/` |
| Admin Login | `admin` / password |
| Database | `dbperfume_clone` |
| Main Database File | `db/init/dbperfume_clone.sql` |
| Local Dev | XAMPP: localhost:80 hoặc Docker:80 |
| Test Coverage | `full/coverage/index.html` |

---

## 🗂️ Tài Liệu Liên Quan

- [Database Schema](db/init/dbperfume_clone.sql)
- [Docker Setup](docker-compose.yml)

