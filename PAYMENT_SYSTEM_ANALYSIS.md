# DATABASE PAYMENT SYSTEM ANALYSIS & REDESIGN PLAN

**Date:** April 8, 2026  
**Current Status:** Multi-payment system (COD, MoMo QR, MoMo Transfer, VNPAY)  
**Proposed Plan:** Simplify to COD + MoMo QR only

---

## 📊 CURRENT PAYMENT SYSTEM ARCHITECTURE

### 1. Order Type Definitions (orders.order_type)

| Value | Name | Display Name | Frontend | Admin | Status |
|-------|------|--------|----------|-------|--------|
| 1 | COD | Thanh toán khi nhận hàng | ✅ YES | ✅ YES | **KEEP** |
| 2 | MoMo QR | Thanh toán MOMO QR CODE | ✅ YES | ✅ YES | **KEEP** |
| 3 | MoMo Transfer | Thanh toán chuyển khoản MoMo | ❌ NO | ✅ YES | **REMOVE** |
| 4 | VNPAY | Thanh toán chuyển khoản VNPAY | ✅ YES | ✅ YES | **REMOVE** |
| 5 | Direct Purchase | Mua hàng trực tiếp | ❌ NO | ✅ YES | **KEEP** (Admin only) |

---

## 🗄️ RELATED DATABASE TABLES

### A. MAIN TABLES (Core Order System)

#### **orders** (675 records currently)
```sql
CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `order_code` int NOT NULL,
  `order_date` varchar(50),
  `account_id` int NOT NULL,
  `delivery_id` int NOT NULL,
  `total_amount` int NOT NULL,
  `order_type` int NOT NULL,          ← CONTAINS PAYMENT METHOD
  `order_status` int NOT NULL
)
```

**Impact on order_type:**
- Field is REQUIRED in orders table
- Cannot be removed/altered
- Values 2 & 5 stay active
- Hide UI for values 3 & 4


#### **order_detail** (relationship table)
```sql
CREATE TABLE `order_detail` (
  `order_detail_id` int NOT NULL,
  `order_code` int NOT NULL,           ← FK to orders
  `product_id` int NOT NULL,
  `product_quantity` int NOT NULL,
  `product_price` int NOT NULL,
  `product_sale` int NOT NULL
)
```

**Impact:** ⚠️ NONE - Just stores items, not payment-related


### B. PAYMENT TRACKING TABLES (For transparent payment history)

#### **vnpay** (54 records - VNPAY payments)
```sql
CREATE TABLE `vnpay` (
  `vnp_id` int NOT NULL,
  `vnp_amount` varchar(50),
  `vnp_bankcode` varchar(50),
  `vnp_banktranno` varchar(50),
  `vnp_cardtype` varchar(50),
  `vnp_orderinfo` varchar(100),
  `vnp_paydate` varchar(50),
  `vnp_tmncode` varchar(50),
  `vnp_transactionno` varchar(50),
  `order_code` int NOT NULL,           ← FK to orders.order_code
  `payment_status` int NOT NULL        ← 0=pending, 1=success
)
```

**Impact:** 
- ⚠️ **NEEDS ACTION** - Has 54 records (real payment data)
- Option A: Move to archive table `vnpay_archive` (recommended)
- Option B: Keep but don't reference in UI


#### **momo** (3 records - MoMo payments)
```sql
CREATE TABLE `momo` (
  `momo_id` int NOT NULL,
  `partner_code` varchar(50),
  `order_code` int NOT NULL,           ← FK to orders.order_code
  `momo_amount` varchar(50),
  `order_info` varchar(100),
  `order_type` varchar(50),            ← CONFUSING! Copy of orders.order_type
  `trans_id` int NOT NULL,
  `payment_date` varchar(50),
  `pay_type` varchar(50),
  `payment_status` int NOT NULL        ← 0=pending, 1=success
)
```

**Impact:**
- ✅ **KEEP** - Only 3 records, MoMo QR is still used
- Field `order_type` is redundant but harmless
- Continue using as-is


### C. OTHER TABLES (NOT affected by payment redesign)

- **account** (user authentication) - ✅ No payment references
- **customer** (user profile) - ✅ No payment references  
- **delivery** (shipping info) - ✅ No payment references
- **metrics** (sales statistics) - ⚠️ May count orders by type, but doesn't break
- **inventory** (stock management) - ✅ No payment references
- **product** - ✅ No payment references
- **user_cart_items** - ✅ No payment references

---

## 📁 CODE FILES THAT TOUCH PAYMENT METHODS

### Frontend (Customer-facing)

| File | Current Use | Action |
|------|-------------|--------|
| `pages/base/checkout.php` | Shows all 3 payment options (1,2,4) | **EDIT**: Remove VNPAY radio button |
| `pages/main/payment_momo_fake.php` | MoMo QR mock payment page | **KEEP**: No changes |
| `pages/main/payment_vnpay_fake.php` | VNPAY mock payment page | **HIDE/REMOVE** |
| `pages/handle/payment_result.php` | Handles payment callbacks | **EDIT**: Remove VNPAY branch logic |
| `pages/handle/config_vnpay.php` | VNPAY API config | **HIDE/COMMENT** |
| `pages/base/account-order.php` | Order history display | **EDIT**: Remove VNPAY display |
| `pages/base/account-history.php` | Order history display | **EDIT**: Remove VNPAY display |

### Admin Panel

| File | Current Use | Action |
|------|-------------|--------|
| `admin/format/format.php` → `format_order_type()` | Maps order_type to display text | **EDIT**: Keep 1,2,5; deprecate 3,4 |
| `admin/modules/order/lietke.php` | Order list view | **AUTO**: Uses format_order_type |
| `admin/modules/order/chitiet_online.php` | Order detail view | **AUTO**: Uses format_order_type |
| `admin/modules/order/chitiet.php` | Direct order detail | **AUTO**: Uses format_order_type |
| `admin/modules/order/timkiem.php` | Order search | **AUTO**: Uses format_order_type |
| `admin/modules/order/xuly.php` | Order processing logic | **CHECK**: Refund logic for VNPAY |
| `admin/modules/home.php` | Dashboard metrics | **EDIT**: May reference order_type==4 |
| `admin/modules/order/lichsuthanhtoan.php` | Payment history | **EDIT**: Remove VNPAY refund button |

---

## ⚠️ CRITICAL DEPENDENCIES & CONFLICTS

### 1. **Historical Data Problem**
```
Current Database State:
- orders table has 675 records
- Many have order_type = 3 or 4 (VNPAY)
- 54 vnpay records with payment history
- Cannot delete these without data loss
```

**Solution:** Archive, don't delete

### 2. **Payment Logic in xuly.php**
```php
// xuly.php line 256 (refund button in payment history)
<a href="modules/order/xuly.php?reverse=1" class="button__control">
  Hoàn tiền
</a>

// This button handles VNPAY refunds
// Need to hide if removing VNPAY support
```

**Solution:** Check if reverse=1 logic only applies to VNPAY

### 3. **format_order_type() Function**
Currently returns text for order_type 1-5. If we only keep 1,2,5:
```php
// Current: Returns text for 1,2,3,4,5
// Proposed: Keep 1,2,5; Return "Unknown" for 3,4 instead of actual names
```

**Solution:** Modify function to handle gracefully

### 4. **Checkout Radio Buttons**
```html
<!-- Currently shows: COD, MoMo QR, VNPAY -->
<!-- Keep showing: COD, MoMo QR -->
<!-- Hide: VNPAY option -->
```

**Solution:** Comment out VNPAY radio button in checkout.php

---

## 🎯 PROPOSED REDESIGN PLAN (No Code Changes Yet)

### Phase 1: Identification (Current)
✅ Identify all payment-related tables and code
✅ Understand current data state

### Phase 2: Design (Next - This Document)
- ✅ Map all dependencies
- ✅ Identify what MUST change vs what CAN be hidden
- ✅ Create migration strategy

### Phase 3: Implementation (Pending Approval)

**Step 1: Frontend Changes (Safe)**
- Remove VNPAY radio button from `pages/base/checkout.php`
- Remove or hide `pages/main/payment_vnpay_fake.php`
- Edit `pages/handle/payment_result.php` to remove vnpay branch

**Step 2: Admin Panel Changes (Safe)**
- Update `admin/format/format.php` to handle removed types gracefully
- Hide/remove VNPAY buttons from appropriate admin pages
- Deprecate VNPAY display (optional: show as [DEPRECATED - VNPAY])

**Step 3: Database Archival (Safe)**
- Create backup table: `CREATE TABLE vnpay_archive AS SELECT * FROM vnpay`
- Keep original `vnpay` table but:
  - Document it as deprecated
  - Don't add new records
  - Don't reference in new code

**Step 4: Testing**
- Verify COD (order_type=1) still works ✅
- Verify MoMo QR (order_type=2) still works ✅
- Verify admin can view old VNPAY orders (historical data) ✅
- Verify new orders cannot be created with type 3 or 4 ✅

---

## 📋 TABLE SUMMARY: What Must Change vs What Can Stay

| Table | Must Change | Can Hide | Can Archive | Can Keep As-Is |
|-------|------------|----------|------------|----------------|
| orders | ❌ NO | N/A | N/A | ✅ **KEEP** |
| order_detail | ❌ NO | N/A | N/A | ✅ **KEEP** |
| vnpay | ❌ NO | ⚠️ From UI | ✅ **YES** | ✅ Optional |
| momo | ❌ NO | N/A | N/A | ✅ **KEEP** |
| All others | ❌ NO | N/A | N/A | ✅ **KEEP** |

---

## ⚡ ANSWER TO YOUR QUESTIONS

### Q1: "DB hiện tại dùng db cũ - có ảnh hưởng gì?"

**Answer:**
- ✅ **No major impact** if designed carefully
- Historical data (675 orders) can coexist with new payment system
- VNPAY records are fully isolated in `vnpay` table
- Just need to prevent NEW orders from using type 3 or 4

### Q2: "Ảnh hưởng những bảng nào?"

**Answer:**
```
Direct Impact (Must monitor):
- orders: Has order_type field (but don't delete - essential)
- vnpay: 54 historical records (archive then hide)
- momo: Keep using normally

Indirect Impact (Auto-handled):
- All reporting/display code (uses format_order_type function)

NO Impact:
- order_detail, customer, delivery, product, etc.
```

### Q3: "Có ảnh hưởng tới những bảng chính không thay đổi được?"

**Answer:**
- ❌ **NO** to core schema changes
- ✅ **YES** to code that reads/displays these tables
- You CAN'T alter orders table structure
- You CAN hide VNPAY from UI/filters without touching schema

### Q4: "Ẩn được bớt bảng nào?"

**Answer:**
```
Can fully hide/disable:
✅ vnpay table (from all UI - archive data)
✅ payment_vnpay_fake.php (delete or archive)
✅ config_vnpay.php (comment out)

Cannot hide (still needed):
❌ orders table (core table)
❌ momo table (MoMo QR still active)
❌ order_detail table (needed for invoices)
```

### Q5: "Dính các bảng chính không thay đổi được?"

**Answer:**
```
Tables you CANNOT change structure:
❌ orders → order_type field must remain
❌ order_detail → Must stay as-is
❌ deployment → Backward compatibility

What you CAN do:
✅ Add new columns if needed (e.g., cancel_reason)
✅ Hide old columns from UI (don't display order_type=3,4)
✅ Modify application logic without DB changes
```

---

## 🔄 ROLLBACK STRATEGY

If redesign goes wrong:
1. ✅ All original data is preserved (no deletions)
2. ✅ vnpay table archived separately 
3. ✅ Simple revert: Just uncomment code + show VNPAY UI
4. ✅ Zero database rollback needed

---

## 📝 NEXT STEPS

**Once you approve this design:**

1. I modify code files to remove VNPAY UI
2. I update format_order_type() to return safe text for old orders
3. I create vnpay_archive table for historical data
4. I test all payment flows
5. Push to production

**Questions before proceeding?**

- Should we display old VNPAY orders as "Archive" in admin?
- Should we block MoMo transfer (order_type=3) completely?
- Timeline for this change?

