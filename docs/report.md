# Coffee Corner | Implementation report
Coffee Corner lets a customer configure one drink per pickup order and reserve a table for one hour. Staff maintain the catalogue and run the pickup queue. Prices are in KGS, reservation times use Asia/Bishkek, and customers pay at pickup.

## Request architecture
Browser → Apache or PHP development server → public PHP page → PDO prepared query → MySQL/MariaDB. The page renders escaped HTML using shared navigation and external CSS. PHP sessions store a customer ID, CSRF token and the most recent reviewed configuration.

GET menu.php?q=Latte reads matching available rows. POST review.php validates size, milk, syrup and quantity against server rules. A signed-in customer confirms using a session-bound request key. PHP locks the current menu row, checks its price and availability, and inserts one receipt with a price snapshot. A unique key prevents a duplicate order. Redirecting to My Activity makes refresh safe.

## Tables and rules
| Table | Relationship and responsibility |
|---|---|
| users | Each account owns many orders and reservations. New accounts are customers; staff privileges are assigned locally. |
| menu_items | One current menu item can appear in many orders. Referenced items can be hidden while receipts remain intact. |
| orders | References the purchaser and menu item. Stores name, options, quantity, unit price, total and status at purchase. |
| cafe_tables | One named table has a fixed capacity and can be reserved across different hours. |
| reservations | Links a user to a table and start time. A unique table/time pair prevents double booking even when requests arrive together. |

Customers may cancel their own placed orders. Staff move orders from placed to preparing to ready to collected. Each update checks the expected old status. Cancelling a future reservation deletes the booking and makes the slot available again. Staff catalogue changes use create, read, update and delete operations.

## Demonstration
1. Register and sign in. Search Cappuccino and preview two medium oat cappuccinos with vanilla. Confirm 490 KGS and open My Activity.
2. In a separate staff session, advance the order. Refresh the customer page to see the new state.
3. Book a Window table for tomorrow at 09:00. Try the same table and hour with another account, then cancel the original booking and book again.
4. Create a temporary tea, edit its price, search for it, then delete it. Show how the application protects a drink already referenced by an order.

## Verification
tests/smoke.php checks public navigation, search, unavailable products, server-calculated pricing and invalid inputs. tests/workflows.py exercises the live browser-to-PHP-to-MariaDB workflow with cookie-bearing clients, two customers and a staff account. The GitHub Actions run records the outcome for each commit. The workflow also saves desktop and mobile screenshots for the course submission.

## Team presentation
Each member can present one path end to end: menu management, account and order processing, or table reservations. Use the actual Git history when describing contributions.

## Recorded outcome
On 25 November 2026 the PHP lint, MariaDB schema import, 10 public HTTP checks, multi-user workflow suite and screenshot capture passed on [GitHub Actions run 45589809921](https://github.com/MaahnoorRajan/CoffeeCorner/actions/runs/45589809921). See the [nine captured screens](screenshots/), including the [mobile menu](screenshots/mobile-menu.png), [confirmation preview](screenshots/review.png), and [staff desk](screenshots/staff.png).

### Local demonstration on 28 November 2026
On the Windows demo machine, PHP 8.4.28 served the OneDrive checkout on loopback port 8000 with MariaDB 13.1.2 on loopback port 3307. All ten public smoke checks passed. A customer registered, signed in and confirmed two medium oat cappuccinos with vanilla for 490 KGS. A separately signed-in staff user advanced the order from placed through preparing to ready. The customer's activity page displayed Ready for that same receipt.
