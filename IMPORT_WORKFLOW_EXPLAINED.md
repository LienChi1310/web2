# Luồng Nhập Hàng (Import/Inventory Workflow) 📦

## 📋 Tổng Quan
Nhập hàng (phiếu nhập kho) là quá trình nhập sản phẩm vào kho, cập nhật giá chi phí, và tính toán giá bán cuối cùng cho khách hàng.

---

## 🔄 Quy Trình Nhập Hàng

### **Bước 1: Tạo Phiếu Nhập Kho**
**File:** `admin/modules/inventory/them.php`

1. Nhấn "Thêm phiếu nhập"
2. Nhập ngày tháng năm
3. **Đặc biệt:** Thêm các dòng sản phẩm:
   - **Sản phẩm:** Chọn sản phẩm từ dropdown (có thể tìm kiếm)
   - **Số lượng:** Nhập số lượng sản phẩm
   - **Giá nhập:** Nhập giá chi phí mua lẻ
   - **Thành tiền:** Tự động tính = Số lượng × Giá nhập
4. Nhấn nút "+" để thêm nhiều sản phẩm
5. Bấm **"Lưu" để lưu phiếu** (trạng thái: `Chờ hoàn thành`)

### **Bước 2: Xem Chi Tiết Phiếu**
**File:** `admin/modules/inventory/chitiet.php`

- Xem lại toàn bộ thông tin phiếu nhập
- Kiểm tra các sản phẩm, số lượng, giá nhập
- Nhấn nút **"Hoàn thành"** để xác nhận

### **Bước 3: Hoàn Thành Phiếu Nhập (Điều Kiện!)**
**File:** `admin/modules/inventory/xuly.php` → Database

**Điều gì xảy ra khi nhấn "Hoàn thành"?**

Hệ thống sẽ:

#### **a) Cập nhật bảng `product` (Tính giá chi phí mới)**
```
Nếu sản phẩm chưa có giá nhập cũ:
  product_price_import = giá nhập từ phiếu
  
Nếu sản phẩm đã có giá nhập cũ:
  product_price_import = (old_qty × old_price + new_qty × new_price) / (old_qty + new_qty)
  [Bình quân giá nhập theo số lượng]
```

#### **b) Cộng dồn số lượng**
```
product_qty += số lượng nhập
```

#### **c) Tính lại giá bán (product_price)**
```
product_price = product_price_import × (100 + product_profit_percent) / 100

Ví dụ:
- Giá nhập: 100.000 VND
- Lợi nhuận: 30%
- product_price = 100.000 × (100 + 30) / 100 = 130.000 VND
```

#### **d) Cập nhật trạng thái phiếu**
```
phiếu nhập: "Chờ hoàn thành" → "Đã hoàn thành"
```

---

## 💰 Giá Trị Hiển Thị Trên Hệ Thống

### **Bảng `product` có 4 cột giá:**

| Cột | Ý Nghĩa | Ví Dụ |
|-----|---------|-------|
| `product_price_import` | Giá chi phí (giá vốn) | 100.000 |
| `product_profit_percent` | % Lợi nhuận | 30% |
| `product_price` | Giá bán gốc (trước giảm) | 130.000 |
| `product_sale` | % Giảm giá | 10% |

### **Giá Hiển Thị Cho Khách Hàng (Frontend)**
```
Giá bán cuối = product_price - (product_price × product_sale / 100)
            = 130.000 - (130.000 × 10 / 100)
            = 130.000 - 13.000
            = 117.000 VND
```

### **Admin Dashboard Hiển Thị**
- **Giá bán cuối:** 117.000 VND (để so sánh với frontend)
- **Giá import:** 100.000 VND (để kiểm tra chi phí)

---

## 🔀 Quy Trình Hoàn Chỉnh

### **Trường Hợp 1: Sản Phẩm Mới (Chưa từng nhập)**

```
1. Tạo phiếu nhập:
   - Sản phẩm: "Nước Hoa Lavender"
   - Số lượng: 10 cái
   - Giá nhập: 100.000 VND

2. Nhấn "Hoàn thành":
   - product_price_import = 100.000
   - product_qty = 0 + 10 = 10
   - product_price = 100.000 × 1.30 = 130.000 (với lợi nhuận 30%)

3. Trong admin xem:
   - Giá bán cuối = 130.000 - (130.000 × 10/100) = 117.000

4. Trên frontend khách hàng thấy: 117.000 VND
```

### **Trường Hợp 2: Sản Phẩm Cũ (Đã nhập lần trước)**

```
Lần nhập 1 (Đã hoàn thành):
  - Sản phẩm: "Nước Hoa Hoa Hồng"
  - Giá nhập: 80.000 VND × 20 cái = Giá trung bình: 80.000
  - product_qty: 20 cái
  - product_price: 100.000 (tính từ 80.000 × 1.25)

Lần nhập 2 (Phiếu mới):
  - Sản phẩm: "Nước Hoa Hoa Hồng" (cùng sản phẩm)
  - Giá nhập: 90.000 VND × 30 cái
  - Nhấn "Hoàn thành":
    
    Tính giá trung bình:
    product_price_import = (20×80.000 + 30×90.000) / (20+30)
                         = (1.600.000 + 2.700.000) / 50
                         = 4.300.000 / 50
                         = 86.000 VND
    
    product_qty = 20 + 30 = 50 cái
    product_price = 86.000 × 1.25 = 107.500 VND (nếu lợi nhuận 25%)
```

---

## ⚙️ Các Hàm Xử Lý (Backend)

Tất cả logic xử lý nằm trong: `admin/config/helpers.php`

### **Hàm Chính:**

```php
// Hoàn thành phiếu nhập
completeInventoryReceipt($receipt_id)
  ├─ Lấy thông tin phiếu & danh sách sản phẩm
  ├─ Duyệt từng sản phẩm:
  │  ├─ Nếu product chưa có:
  │  │  └─ Gán product_price_import = giá nhập
  │  ├─ Nếu product đã có:
  │  │  └─ Tính bình quân: (old_qty × old_price + new_qty × new_price) / total
  │  ├─ Cộng số lượng: product_qty += nhập_qty
  │  └─ Tính lại giá: product_price = price_import × (100 + profit%) / 100
  └─ Cập nhật trạng thái "Đã hoàn thành"

// Lấy trạng thái văn bản
inventory_status_text($status) → "Chờ hoàn thành" / "Đã hoàn thành"
```

---

## 📊 Mối Quan Hệ Bảng Dữ Liệu

### **Bảng `inventory` (Phiếu Nhập)**
```sql
CREATE TABLE inventory (
  id INT PRIMARY KEY,
  inventory_date DATETIME,
  inventory_status VARCHAR (quan trạng thái: 0=Chờ, 1=Đã hoàn thành)
);
```

### **Bảng `inventory_item` (Chi Tiết Phiếu)**
```sql
CREATE TABLE inventory_item (
  id INT PRIMARY KEY,
  inventory_id INT,
  product_id INT,
  quantity INT,
  price_import DECIMAL (giá nhập lẻ)
);
```

### **Bảng `product` (Sản Phẩm)**
```sql
CREATE TABLE product (
  id INT PRIMARY KEY,
  product_price_import DECIMAL (giá chi phí hiện tại),
  product_qty INT (tổng số lượng),
  product_profit_percent INT (% lợi nhuận),
  product_price DECIMAL (giá bán gốc = import × (100+profit%)/100),
  product_sale INT (% giảm giá)
);
```

---

## ❌ Nếu Không Nhấn "Hoàn Thành"

- Phiếu nhập vẫn ở trạng thái "Chờ hoàn thành"
- **`product` chưa được cập nhật** (số lượng & giá)
- Khách hàng vẫn không thấy sản phẩm này hoặc số lượng cũ

---

## ✅ Tóm Tắt Quy Trình

| Bước | Hành Động | Kết Quả |
|------|-----------|--------|
| 1 | Tạo phiếu + thêm sản phẩm | Phiếu ở trạng thái "Chờ" |
| 2 | Kiểm tra chi tiết phiếu | Xác nhận thông tin đúng |
| 3 | Nhấn "Hoàn thành" | Cập nhật product_price_import, product_qty, product_price |
| 4 | Check frontend | Khách thấy giá mới & có thể mua sản phẩm |

---

## 🔍 Debug Tips

**Nếu giá không khớp:**
1. Kiểm tra `product_price_import` (giá chi phí cuối cùng)
2. Kiểm tra `product_profit_percent` (% lợi nhuận)
3. Kiểm tra `product_sale` (% giảm giá)
4. Tính lại: `final = price × (100 + profit%) / 100 - (price × sale% / 100)`

**Nếu số lượng không đúng:**
1. Kiểm tra tất cả phiếu "Đã hoàn thành" của sản phẩm
2. Tính tổng quantity từ tất cả phiếu hoàn thành

---

## 🎯 Câu Hỏi Thường Gặp

**Q: Tại sao phải "Hoàn thành" riêng?**  
A: Để kiểm tra lại phiếu trước khi cập nhật giá sản phẩm chính.

**Q: Có thể chỉnh sửa phiếu sau khi hoàn thành?**  
A: Không khuyến khích. Phải xoá & tạo lại phiếu mới.

**Q: Phiếu nhập ảnh hưởng đến frontend như thế nào?**  
A: Chỉ qua cột `product` (giá, số lượng), không lưu lịch sử nhập.

**Q: Tại sao dùng bình quân giá?**  
A: Để giá chi phí chính xác khi có nhiều lần nhập ở giá khác nhau.

