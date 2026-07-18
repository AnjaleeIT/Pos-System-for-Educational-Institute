Place this folder in your WAMP www directory (e.g., C:\wamp64\www\pos).
1. Create database and tables: import database.sql into phpMyAdmin.
2. Edit config.php DB credentials if needed (default: root with no password).
3. Open browser: http://localhost/pos/index.php
Default admin user created in SQL:
- username: admin
- password: admin123
Default coordinator:
- username: coordinator
- password: coord123
Change passwords after first login.


| username    | password (hashed) | role        |
| ----------- | ----------------- | ----------- |
| admin       | admin123          | admin       |
| coordinator | coord123          | coordinator |
