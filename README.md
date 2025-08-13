rubber_management_system/
│
├── css/
│   └── style.css              # ไฟล์ CSS รวมตกแต่ง UI
│
├── js/
│   └── (ถ้ามีไฟล์ JavaScript)  # ตัวอย่าง: main.js (ถ้ามีฟังก์ชัน JS)
│
├── includes/
│   ├── db.php                 # เชื่อมต่อฐานข้อมูล
│   ├── header.php             # ส่วนหัว (header) ที่ใช้ร่วมกัน
│   ├── footer.php             # ส่วนท้าย (footer) ที่ใช้ร่วมกัน
│   └── functions.php          # ฟังก์ชันช่วยเหลือทั่วไป เช่น ตรวจสอบสิทธิ์
│
├── owner/                     # โฟลเดอร์สำหรับเจ้าของสวน
│   ├── owner_dashboard.php    # หน้าแดชบอร์ดเจ้าของสวน
│   ├── add_farmer.php         # ฟอร์มเพิ่มเกษตรกรภายใต้เจ้าของสวน
│   ├── manage_basics.php      # จัดการข้อมูลพื้นฐาน (สวน, พื้นที่ ฯลฯ)
│   ├── report.php             # รายงานผลผลิตหรือข้อมูลสถิติ
│   └── (ไฟล์อื่น ๆ ที่เกี่ยวข้องกับเจ้าของสวน)
│
├── farmer/                    # โฟลเดอร์สำหรับเกษตรกร
│   ├── farmer_dashboard.php   # หน้าแดชบอร์ดเกษตรกร
│   ├── add_latex.php          # ฟอร์มบันทึกน้ำยางที่เก็บได้
│   ├── add_fertilizer.php     # ฟอร์มบันทึกการใส่ปุ๋ย
│   ├── add_tree.php           # ฟอร์มบันทึกข้อมูลต้นยาง
│   └── (ไฟล์อื่น ๆ ที่เกี่ยวข้องกับเกษตรกร)
│
├── admin/                     # (ถ้ามี) สำหรับผู้ดูแลระบบระบบ
│   ├── admin_dashboard.php    # หน้าแดชบอร์ดผู้ดูแลระบบ
│   ├── manage_users.php       # จัดการผู้ใช้งานทั้งหมด
│   └── (ไฟล์อื่น ๆ ของแอดมิน)
│
├── index.php                  # หน้าแรกของระบบ (เมนูหลัก)
├── login.php                  # หน้าเข้าสู่ระบบ
├── logout.php                 # หน้าออกจากระบบ
├── owner_register.php         # สมัครสมาชิกเจ้าของสวน
├── register.php               # สมัครสมาชิกเกษตรกร
└── README.md                  # คำอธิบายโปรเจกต์ (ถ้ามี)
แก้

rubber_management_system/
│
├── css/
│   └── style.css                    # ไฟล์ CSS รวมตกแต่ง UI
│
├── js/
│   ├── main.js                     # ไฟล์ JavaScript หลัก
│   └── (ไฟล์ JavaScript อื่น ๆ)
│
├── includes/
│   ├── db.php                     # เชื่อมต่อฐานข้อมูล
│   ├── header.php                 # ส่วนหัว (header) ที่ใช้ร่วมกัน
│   ├── footer.php                 # ส่วนท้าย (footer) ที่ใช้ร่วมกัน
│   ├── functions.php              # ฟังก์ชันช่วยเหลือทั่วไป เช่น ตรวจสอบสิทธิ์
│   └── (ไฟล์อื่น ๆ ที่เกี่ยวข้อง เช่น validation.php, session.php)
│
├── owner/                         # โฟลเดอร์สำหรับเจ้าของสวน
│   ├── owner_dashboard.php        # หน้าแดชบอร์ดเจ้าของสวน
│   ├── add_farmer.php             # ฟอร์มเพิ่มเกษตรกรภายใต้เจ้าของสวน
│   ├── manage_basics.php          # จัดการข้อมูลพื้นฐาน เช่น สวน, พื้นที่, ประเภทปุ๋ย ฯลฯ
│   ├── report.php                 # รายงานผลผลิตหรือข้อมูลสถิติ
│   ├── notifications.php          # แจ้งเตือนเกษตรกร
│   ├── owner_edit_profile.php     # แก้ไขข้อมูลเจ้าของสวน
│   ├── owner_add_tree.php         # ฟอร์มเพิ่มข้อมูลต้นยาง
│   ├── owner_manage_trees.php     # จัดการข้อมูลต้นยาง
│   ├── owner_view_fertilizer.php  # ดูประวัติการใช้ปุ๋ย/ยา
│   ├── owner_view_latex.php       # ดูประวัติการเก็บน้ำยาง
│   └── (ไฟล์อื่น ๆ ที่เกี่ยวข้องกับเจ้าของสวน เช่น ajax_handler.php, export_report.php)
│
├── farmer/                        # โฟลเดอร์สำหรับเกษตรกร
│   ├── farmer_dashboard.php       # หน้าแดชบอร์ดเกษตรกร
│   ├── add_latex.php              # ฟอร์มบันทึกน้ำยางที่เก็บได้
│   ├── add_fertilizer.php         # ฟอร์มบันทึกการใส่ปุ๋ย/ยา
│   ├── add_tree.php               # ฟอร์มบันทึกข้อมูลต้นยาง
│   ├── farmer_edit_profile.php    # แก้ไขข้อมูลเกษตรกร
│   ├── notifications.php          # แจ้งเตือนเกษตรกร
│   ├── view_my_trees.php          # ดูข้อมูลต้นยางของตนเอง
│   ├── view_latex_history.php     # ดูประวัติการเก็บน้ำยาง
│   └── (ไฟล์อื่น ๆ ที่เกี่ยวข้อง เช่น ajax_fetch.php, download_report.php)
│
├── admin/                        # โฟลเดอร์สำหรับผู้ดูแลระบบ
│   ├── admin_dashboard.php       # หน้าแดชบอร์ดผู้ดูแลระบบ
│   ├── manage_users.php          # จัดการผู้ใช้งานทั้งหมด
│   ├── manage_owners.php         # จัดการเจ้าของสวน
│   ├── manage_farmers.php        # จัดการเกษตรกร
│   ├── manage_plants.php         # จัดการข้อมูลต้นยาง, ปุ๋ย, ยา ฯลฯ
│   ├── reports.php               # รายงานต่าง ๆ ของระบบ
│   └── (ไฟล์อื่น ๆ ของแอดมิน เช่น system_settings.php, logs.php)
│
├── index.php                    # หน้าแรกของระบบ (เมนูหลัก)
├── login.php                    # หน้าเข้าสู่ระบบ
├── logout.php                   # หน้าออกจากระบบ
├── owner_register.php           # สมัครสมาชิกเจ้าของสวน
├── register.php                 # สมัครสมาชิกเกษตรกร
└── README.md                    # คำอธิบายโปรเจกต์ (ถ้ามี)
