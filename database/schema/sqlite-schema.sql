CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "sales"(
  "id" integer primary key autoincrement not null,
  "transaction_date" date not null,
  "payment_method_name" varchar check("payment_method_name" in('QRIS', 'Cash', 'Debit', 'Credit')) not null default 'Cash',
  "total_price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  "user_id" integer not null,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "sales_items"(
  "id" integer primary key autoincrement not null,
  "sales_id" integer not null,
  "treatment_id" integer not null,
  "quantity" integer not null,
  "price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("sales_id") references "sales"("id") on delete cascade,
  foreign key("treatment_id") references "treatments"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "stocks"(
  "id" integer primary key autoincrement not null,
  "item_name" varchar not null,
  "qty" numeric not null,
  "buy_price" numeric not null,
  "sell_price" numeric not null,
  "unit" varchar check("unit" in('ml', 'unit')) not null default 'ml'
);
CREATE TABLE IF NOT EXISTS "treatments"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "category" varchar not null,
  "treatment_price" numeric not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE TABLE IF NOT EXISTS "purchases"(
  "id" integer primary key autoincrement not null,
  "price" numeric not null
);
CREATE TABLE IF NOT EXISTS "purchase_items"(
  "purchase_id" integer not null,
  "stocks_id" integer not null,
  "qty" numeric not null,
  foreign key("purchase_id") references "purchases"("id") on delete cascade,
  foreign key("stocks_id") references "stocks"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "sales_treatments"(
  "id" integer primary key autoincrement not null,
  "sales_id" integer not null,
  "treatment_id" integer not null,
  "price" numeric not null,
  foreign key("sales_id") references "sales"("id") on delete cascade,
  foreign key("treatment_id") references "treatments"("id") on delete cascade
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE UNIQUE INDEX "sales_id_unique" on "sales"("id");
CREATE UNIQUE INDEX "users_id_unique" on "users"("id");
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE UNIQUE INDEX "stocks_id_unique" on "stocks"("id");
CREATE UNIQUE INDEX "treatments_id_unique" on "treatments"("id");
CREATE UNIQUE INDEX "purchases_id_unique" on "purchases"("id");
CREATE UNIQUE INDEX "sales_treatments_id_unique" on "sales_treatments"("id");

INSERT INTO migrations VALUES(5,'2025_01_14_122215_create_stoks_table',1);
INSERT INTO migrations VALUES(10,'2025_01_13_131955_create_stok_table',2);
INSERT INTO migrations VALUES(11,'2025_01_14_120446_create_services_table',2);
INSERT INTO migrations VALUES(68,'0001_01_01_000000_create_users_table',3);
INSERT INTO migrations VALUES(69,'0001_01_01_000001_create_cache_table',3);
INSERT INTO migrations VALUES(70,'2025_01_13_131937_create_sales_table',3);
INSERT INTO migrations VALUES(71,'2025_01_13_131955_create_stock_table',3);
INSERT INTO migrations VALUES(72,'2025_01_14_145407_create_treatments_table',3);
INSERT INTO migrations VALUES(73,'2025_01_14_160136_create_purchases_table',3);
INSERT INTO migrations VALUES(74,'2025_01_14_160423_create_purchase_items_table',3);
INSERT INTO migrations VALUES(75,'2025_01_14_160624_create_sales_treatments_table',3);
