# Coffee Corner
**Café Ordering and Table Reservations** · Web and Internet Technologies · UCA Naryn

Coffee Corner is a three-person course project for a fictional café. Customers browse drinks, choose options and preview prices in Kyrgyz som. The planned full workflow adds pay-at-pickup orders, a staff preparation queue and fixed-hour table bookings.

## Try the first milestone
Open the menu, search for Cappuccino, choose medium, oat milk and vanilla, and set quantity to two. PHP retrieves the base price from the database and returns **490 KGS**: 2 × (190 + 20 + 25 + 10). A preview does not save an order. The menu excludes unavailable drinks.

## Price reference
The [Kulikov Sydykova café menu](https://menu-coffee.kulikov.com/sydykova/products) lists cappuccino from **190 KGS** (270, 400 and 450 ml options) and latte from **230 KGS** (400 and 450 ml options). Coffee Corner uses those starting prices as benchmarks. Our size labels, option surcharges, Masala Chai and Mocha prices are project examples. Existing installations using the original seed prices can apply `database/update_reference_prices.sql` once with a database maintenance account. Fresh installations only need schema.sql.

## Run locally
1. Clone the repository. Install PHP 8.1+ with `pdo_mysql` and MySQL 8.0.16+ or MariaDB 10.4+. XAMPP with a compatible PHP version works. Start the database service.
2. In phpMyAdmin or your SQL client, create a fresh database called `coffee_corner` using `utf8mb4_unicode_ci`. Select it and import `database/schema.sql` once. The file creates five InnoDB tables and sample drinks and tables.
3. Create a local database login with SELECT access to `coffee_corner.*` for this milestone. Copy `config/database.example.php` to `config/database.local.php` and enter that login, password and host there. The local override is ignored by Git.
4. From the repository root, run `php -S 127.0.0.1:8000 -t public`. In Windows PowerShell with XAMPP, use `C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public`. Open http://127.0.0.1:8000. Only public/ should be web-accessible.
5. With the server still running, open another terminal in the repository root and run `php tests/smoke.php` (or use the XAMPP PHP executable). The checks use the original seed records. If PDO reports a missing driver, enable pdo_mysql in the PHP CLI configuration. A generic service error on the menu usually means the database, credentials or import needs checking; see the server terminal.

## How a request works
- GET menu.php reads q and category, binds both filters through PDO, and renders matching drinks.
- GET drink.php?id=1 retrieves an available item and renders the configuration form.
- POST review.php reads the item again, validates the options and quantity, and calculates the preview. Prices sent by the browser have no authority. Dynamic text is escaped on output.
- includes/app.php holds the connection, option prices and validation. Shared layout is in includes/ and the external stylesheet is in public/.

## Data model
| Table | Purpose |
|---|---|
| users | Customer/staff identity and password hashes for the upcoming account workflow |
| menu_items | Searchable catalogue, availability and base prices |
| orders | Links one customer to one configured drink; snapshots the price and item name |
| cafe_tables | Numbered or named tables with seat counts |
| reservations | Links users to tables; the same table and slot cannot be booked twice |

## One-week delivery plan
These weights are team planning estimates, not course marks. The first milestone represents **35 of 100 planned points**.
| Day | Deliverable | Weight |
|---|---|---:|
| 1 | Relational schema and seeds (12), live menu and filters (8), configuration and preview (10), setup and milestone checks (5) | 35 |
| 2 | Registration, sessions, logout, ownership and staff permissions | 12 |
| 3 | Order confirmation, duplicate-submit protection, customer history | 15 |
| 4 | Staff menu CRUD and order status queue | 12 |
| 5 | Table availability, booking and cancellation | 14 |
| 6 | Integrate responsive screens, run and record full workflow/security tests | 7 |
| 7 | Final SQL export, screenshots, architecture report and presentation rehearsal | 5 |

Each member should commit their own finished changes, push a feature branch and have a teammate review it. Suggested parallel ownership: menu/admin, accounts/orders, reservations/testing. Coordinate changes to the shared schema. Loyalty, events and offers are extensions after the course version is ready.
