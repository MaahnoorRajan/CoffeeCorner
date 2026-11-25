# Coffee Corner
Café ordering and table reservations · Web and Internet Technologies · UCA Naryn

Browse the searchable menu, customise drinks and preview prices in Kyrgyz som. Sign in to place a pickup order, view its status, reserve a table or cancel an eligible order or booking. The staff desk manages the preparation queue, daily bookings and café menu.

## Run locally
1. Install PHP 8.1+ with PDO MySQL and MySQL 8.0.16+ or MariaDB 10.4+. XAMPP is suitable. Start the database service.
2. Create an empty database named **coffee_corner** with utf8mb4_unicode_ci collation. Import **database/schema.sql** once using your database administration account. It creates five tables and sample menu items and tables.
3. Give a local application account permission to read and write application records. For example, in the database administration console, replace the password below with a local password:

   ```sql
   CREATE USER 'coffee_app'@'localhost' IDENTIFIED BY 'replace-with-your-local-password';
   GRANT SELECT, INSERT, UPDATE, DELETE ON coffee_corner.* TO 'coffee_app'@'localhost';
   ```

   If the account already exists, run the GRANT only and keep its existing password. Copy **config/database.example.php** to **config/database.local.php** and set the corresponding credentials. The override is ignored by Git.
4. From the repository root run **php -S 127.0.0.1:8000 -t public**. On Windows with XAMPP you can use **C:\\xampp\\php\\php.exe** in place of php. Open http://127.0.0.1:8000. For Apache, set the web document root to public/.
5. Register customer and staff demo accounts using separate email addresses. From the administrator SQL console, grant the intended staff account its role, then sign in again:

   ```sql
   UPDATE users SET role='staff' WHERE email='your-staff-account@example.com';
   ```

## Upgrading the first milestone
The core schema is compatible. Existing installations should keep their data, grant the app the write permissions above and apply **database/update_reference_prices.sql** if still using the original seed prices. Existing passwords and configuration belong only on your machine.

## Prices and booking rules
[Kulikov’s café menu](https://menu-coffee.kulikov.com/sydykova/products) is the reference for cappuccino from 190 KGS and latte from 230 KGS. Size, milk and syrup adjustments are project examples. Two medium oat cappuccinos with vanilla cost **2 × (190 + 20 + 25 + 10) = 490 KGS**. Orders are paid at pickup. Each table booking lasts one hour; choose a start time from 09:00 through 17:00 in Naryn.

## Code and handoff
| Location | Responsibility |
|---|---|
| public/ | Customer pages, staff desk, catalogue management and responsive stylesheet |
| includes/ | Connection, session handling, permissions, request validation, pricing and shared layout |
| config/ and database/ | Local connection template, schema, seeds and price update |
| tests/ | Public HTTP checks and multi-user acceptance tests |
| [Course report](docs/report.md) | Architecture, database relationships, HTTP flows and demo script |

## Tests and screenshots
GitHub Actions imports the schema into a disposable MariaDB database, checks PHP syntax, starts the app, runs both test suites, then captures representative pages. Open a successful run under **Actions → Backend smoke tests** and download **coffee-corner-screenshots**. For a local read-only smoke check, run **php tests/smoke.php** against the seed data. Run the full workflow suite in CI or against a disposable test installation; it creates accounts and orders and expects the CI database login.
