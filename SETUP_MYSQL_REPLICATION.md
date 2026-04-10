# HƯỚNG DẪN SETUP MYSQL REPLICATION (MASTER-SLAVE)

---

## DEVICE 1 - MASTER SETUP

### File cần sửa
Đường dẫn: C:\Xampp\down\mysql\bin\my.ini

### Bước 1: Mở file my.ini bằng Notepad

### Bước 2: Scroll xuống tìm dòng khoảng số 70-80

Tìm dòng bắt đầu với:
```
log-bin deactivated by default since XAMPP 1.4.11
```

### Bước 3: Xóa nội dung cũ

Xóa toàn bộ từ dòng:
```
log-bin deactivated by default since XAMPP 1.4.11
```

Đến hết dòng:
```
server-id	=1
```

(khoảng 6-7 dòng)

### Bước 4: Gõ lại nội dung mới

Gõ dòng thứ 1:
```
log-bin activated for replication
```

Gõ dòng thứ 2:
```
log-bin=mysql-bin
```

Gõ dòng thứ 3:
```
binlog_do_db=dbperfume_web2
```

Gõ dòng thứ 4: (để trống, nhấn Enter)

Gõ dòng thứ 5:
```
required unique id between 1 and 2^32 - 1
```

Gõ dòng thứ 6:
```
defaults to 1 if master-host is not set
```

Gõ dòng thứ 7:
```
but will not function as a master if omitted
```

Gõ dòng thứ 8:
```
server-id=1
```

### Bước 5: Save file

Nhấn Ctrl + S

### Bước 6: Close file

Đóng Notepad

---

## DEVICE 2 - SLAVE SETUP

### File cần sửa
Đường dẫn: C:\Xampp\down\mysql\bin\my.ini

### Bước 1: Mở file my.ini bằng Notepad

### Bước 2: Scroll xuống tìm dòng khoảng số 100-150

Tìm dòng bắt đầu với:
```
required unique id between 2 and 2^32 - 1
```

### Bước 3: Xóa nội dung cũ

Xóa từ dòng:
```
required unique id between 2 and 2^32 - 1
```

Đến hết dòng:
```
log-bin=mysql-bin
```

(khoảng 20-25 dòng)

### Bước 4: Gõ lại nội dung mới

Gõ dòng thứ 1:
```
required unique id between 2 and 2^32 - 1
```

Gõ dòng thứ 2:
```
(and different from the master)
```

Gõ dòng thứ 3:
```
defaults to 2 if master-host is set
```

Gõ dòng thứ 4:
```
but will not function as a slave if omitted
```

Gõ dòng thứ 5:
```
server-id=2
```

Gõ dòng thứ 6: (để trống)

Gõ dòng thứ 7:
```
Replication Slave Configuration
```

Gõ dòng thứ 8:
```
relay-log=slave-relay-bin
```

Gõ dòng thứ 9:
```
relay-log-index=slave-relay-bin.index
```

Gõ dòng thứ 10:
```
skip-slave-start
```

Gõ dòng thứ 11: (để trống)

Gõ dòng thứ 12:
```
Note: Use CHANGE MASTER TO command instead of master-* options below
```

Gõ dòng thứ 13:
```
All master-* lines are commented out for security
```

Gõ dòng thứ 14: (để trống, để hết các dòng #master-* như cũ)

### Bước 5: Save file

Nhấn Ctrl + S

### Bước 6: Close file

Đóng Notepad

---

## XON - STOP - START LẠI MYSQL

### Bước 1: Mở XAMPP Control Panel

Kích đúp file: C:\Xampp\xampp-control.exe

### Bước 2: Click nút STOP MySQL

Chờ dòng MySQL thành màu đỏ (off hoàn toàn)

### Bước 3: Chờ 3 giây

### Bước 4: Click nút START MySQL

Chờ dòng MySQL thành màu xanh (on)

### Bước 5: Kiểm tra MySQL

Nếu MySQL bật được = thành công

---

## TIẾP THEO - DEVICE 1

### Mở MySQL Console

Mở Command Prompt, gõ:

```
C:\Xampp\mysql\bin\mysql.exe -u root
```

Nhấn Enter

### Gõ từng lệnh sau

Gõ lệnh 1:
```
CREATE USER 'repl_user'@'%' IDENTIFIED BY 'password123';
```

Nhấn Enter

Gõ lệnh 2:
```
GRANT REPLICATION SLAVE ON *.* TO 'repl_user'@'%';
```

Nhấn Enter

Gõ lệnh 3:
```
FLUSH PRIVILEGES;
```

Nhấn Enter

Gõ lệnh 4:
```
SHOW MASTER STATUS;
```

Nhấn Enter

### Ghi lại kết quả

Sẽ có dòng như:
```
File: mysql-bin.000001
Position: 154
```

GHI LẠI:
- File: _______________
- Position: _______________

### Quit MySQL Console

Gõ:
```
exit
```

Nhấn Enter

---

## TIẾP THEO - DEVICE 2

### Mở MySQL Console

Mở Command Prompt, gõ:

```
C:\Xampp\mysql\bin\mysql.exe -u root
```

Nhấn Enter

### Gõ lệnh CHANGE MASTER

Gõ lệnh (thay IP_DEVICE_1 = IP của Device 1, File và Position từ Device 1):

```
CHANGE MASTER TO MASTER_HOST='IP_DEVICE_1', MASTER_USER='repl_user', MASTER_PASSWORD='password123', MASTER_LOG_FILE='mysql-bin.000001', MASTER_LOG_POS=154;
```

Nhấn Enter

### Gõ lệnh START SLAVE

Gõ:
```
START SLAVE;
```

Nhấn Enter

### Kiểm tra status

Gõ:
```
SHOW SLAVE STATUS\G
```

Nhấn Enter

### Thành công khi thấy

Dòng 1:
```
Slave_IO_Running: Yes
```

Dòng 2:
```
Slave_SQL_Running: Yes
```

Dòng 3:
```
Seconds_Behind_Master: 0
```

Nếu cả 3 dòng đều Yes và 0 = THÀNH CÔNG!

### Quit MySQL Console

Gõ:
```
exit
```

Nhấn Enter

---

## TEST THỰC TẾ

### Trên Device 1

Mở MySQL, gõ:
```
INSERT INTO orders VALUES (999, 9999, NOW(), 2, 1, 10000000, 1, 1);
```

Nhấn Enter

### Trên Device 2

Mở MySQL, gõ:
```
SELECT * FROM orders WHERE order_id = 999;
```

Nhấn Enter

Nếu thấy dòng vừa thêm = REAL-TIME SYNC THÀNH CÔNG!

---

## TROUBLESHOOT

Nếu Slave_IO_Running = No:

Bước 1: Kiểm tra ping
```
ping IP_DEVICE_1
```

Bước 2: Reset slave
```
STOP SLAVE;
RESET SLAVE;
```

Bước 3: Redo lệnh CHANGE MASTER TO trên Device 2

---

END
