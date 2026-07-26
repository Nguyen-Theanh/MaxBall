# Quy tắc làm việc (Quy định bắt buộc)

1. **KHÔNG BAO GIỜ** được code trực tiếp trên nhánh `main`.
2. Khi phát triển một tính năng mới hoặc sửa lỗi, luôn phải tuân theo quy trình sau:
   - Tải toàn bộ code mới nhất từ github xuống (`git pull origin main`).
   - Tạo nhánh mới (branch) từ nhánh `main` để code (Ví dụ: `git checkout -b feature/tên-chức-năng`).
   - Thực hiện code trên nhánh này.
   - Kiểm tra kỹ lại toàn bộ dự án, chạy test xem có xuất hiện lỗi phát sinh ở đâu không.
   - Nếu có lỗi, phải thông báo ngay cho người dùng để xem xét hướng giải quyết.
   - Chỉ khi giải quyết xong mọi lỗi và tính năng hoàn thiện, mới được đẩy nhánh (push) lên github và tiến hành merge vào nhánh `main`.
