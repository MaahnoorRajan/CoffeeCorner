-- Import once into an empty coffee_corner database.
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','staff') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    category VARCHAR(40) NOT NULL,
    description VARCHAR(255) NOT NULL,
    base_price_kgs SMALLINT UNSIGNED NOT NULL,
    available BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    item_name_snapshot VARCHAR(80) NOT NULL,
    size ENUM('small','medium','large') NOT NULL,
    milk ENUM('regular','oat','almond') NOT NULL,
    syrup ENUM('none','vanilla','caramel') NOT NULL,
    quantity TINYINT UNSIGNED NOT NULL CHECK (quantity BETWEEN 1 AND 5),
    unit_price_kgs INT UNSIGNED NOT NULL,
    total_kgs INT UNSIGNED NOT NULL,
    status ENUM('placed','preparing','ready','collected','cancelled') NOT NULL DEFAULT 'placed',
    request_key CHAR(64) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES menu_items(id),
    CHECK (total_kgs = unit_price_kgs * quantity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cafe_tables (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(40) NOT NULL UNIQUE,
    seats TINYINT UNSIGNED NOT NULL CHECK (seats > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    table_id INT UNSIGNED NOT NULL,
    slot_start DATETIME NOT NULL,
    party_size TINYINT UNSIGNED NOT NULL CHECK (party_size > 0),
    UNIQUE (table_id, slot_start),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (table_id) REFERENCES cafe_tables(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO menu_items (name, category, description, base_price_kgs, available) VALUES
('Cappuccino','Coffee','Espresso, steamed milk and foam.',140,1),
('Latte','Coffee','Espresso with a generous pour of milk.',150,1),
('Masala Chai','Tea','Spiced black tea brewed with milk.',100,1),
('Mocha','Coffee','Espresso and chocolate.',165,0);

INSERT INTO cafe_tables (label, seats) VALUES ('Window',2),('Corner',4),('Shared',6);
