import {
  mysqlTable,
  mysqlSchema,
  AnyMySqlColumn,
  index,
  primaryKey,
  bigint,
  varchar,
  text,
  json,
  char,
  timestamp,
  foreignKey,
  unique,
  int,
  date,
  tinyint,
  decimal,
  longtext,
  time,
} from "drizzle-orm/mysql-core";
import { sql } from "drizzle-orm";

export const activity_log = mysqlTable(
  "activity_log",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    log_name: varchar({ length: 255 }),
    description: text().notNull(),
    subject_type: varchar({ length: 255 }),
    event: varchar({ length: 255 }),
    subject_id: bigint({ mode: "number", unsigned: true }),
    causer_type: varchar({ length: 255 }),
    causer_id: bigint({ mode: "number", unsigned: true }),
    properties: json(),
    batch_uuid: char({ length: 36 }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    index("activity_log_log_name_index").on(table.log_name),
    index("causer").on(table.causer_type, table.causer_id),
    index("subject").on(table.subject_type, table.subject_id),
    primaryKey({ columns: [table.id], name: "activity_log_id" }),
  ]
);

export const amenities = mysqlTable(
  "amenities",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    icon: text(),
    general_amenity_id: bigint({ mode: "number", unsigned: true }),
  },
  (table) => [
    foreignKey({
      columns: [table.general_amenity_id],
      foreignColumns: [table.id],
      name: "amenities_general_amenity_id_foreign",
    }).onDelete("set null"),
    primaryKey({ columns: [table.id], name: "amenities_id" }),
  ]
);

export const amenity_hotel = mysqlTable("amenity_hotel", {
  amenity_id: bigint({ mode: "number", unsigned: true }).references(
    () => amenities.id,
    { onDelete: "cascade" }
  ),
  hotel_id: bigint({ mode: "number", unsigned: true }).references(
    () => hotels.id,
    { onDelete: "cascade" }
  ),
});

export const booking_invoices = mysqlTable(
  "booking_invoices",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    reference: varchar({ length: 255 }).notNull(),
    amount: int().notNull(),
    commission_amount: int(),
    hotel_payable: int(),
    status: varchar({ length: 255 }).default("unpaid"),
    customer_id: bigint({ mode: "number", unsigned: true }).references(
      () => customers.id,
      { onDelete: "set null" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    payed_on: date({ mode: "string" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    deleted_at: timestamp({ mode: "string" }),
    is_settled: tinyint().default(0),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_settled: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_in: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_out: date({ mode: "string" }),
    type: varchar({ length: 255 }).default("online"),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "booking_invoices_id" }),
    unique("booking_invoices_reference_unique").on(table.reference),
  ]
);

export const bookings = mysqlTable(
  "bookings",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: varchar({ length: 255 }).notNull(),
    bookable_id: int().notNull(),
    bookable_type: varchar({ length: 255 }).notNull(),
    due: bigint({ mode: "number" }),
    discount: bigint({ mode: "number" }),
    amount_paid: bigint({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_in: date({ mode: "string" }).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_out: date({ mode: "string" }).notNull(),
    status: varchar({ length: 255 }).default("booked"),
    type: varchar({ length: 255 }).default("room"),
    agent_id: bigint({ mode: "number", unsigned: true }).references(
      () => users.id,
      { onDelete: "set null" }
    ),
    customer_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => customers.id, { onDelete: "cascade" }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    mode_of_payment: varchar({ length: 255 }),
    via: varchar({ length: 255 }).default("9jahotel"),
    booking_invoice_id: bigint({ mode: "number", unsigned: true }).references(
      () => booking_invoices.id,
      { onDelete: "cascade" }
    ),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "bookings_id" }),
    unique("bookings_code_unique").on(table.code),
  ]
);

export const businesses = mysqlTable(
  "businesses",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    phone: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }).notNull(),
    address: varchar({ length: 255 }),
    status: varchar({ length: 255 }),
    city_id: bigint({ mode: "number", unsigned: true }).references(
      () => cities.id,
      { onDelete: "set null" }
    ),
    balance: bigint({ mode: "number" }),
    limit: bigint({ mode: "number" }).default(500000),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "businesses_id" })]
);

export const categories = mysqlTable(
  "categories",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    image: varchar({ length: 255 }),
    type: varchar({ length: 255 }).default("supply"),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    deleted_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "categories_id" })]
);

export const cities = mysqlTable(
  "cities",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: bigint({ mode: "number", unsigned: true }).notNull(),
    state_id: bigint({ mode: "number", unsigned: true }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    country_code: varchar({ length: 3 }).notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "cities_id" })]
);

export const countries = mysqlTable(
  "countries",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    iso2: varchar({ length: 2 }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    status: tinyint().default(1).notNull(),
    phone_code: varchar({ length: 5 }).notNull(),
    iso3: varchar({ length: 3 }).notNull(),
    region: varchar({ length: 255 }).notNull(),
    subregion: varchar({ length: 255 }).notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "countries_id" })]
);

export const credit_payments = mysqlTable(
  "credit_payments",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    amount_paid: int().notNull(),
    amount_owed: int(),
    business_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => businesses.id, { onDelete: "cascade" }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "credit_payments_id" })]
);

export const currencies = mysqlTable(
  "currencies",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: bigint({ mode: "number", unsigned: true }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    code: varchar({ length: 255 }).notNull(),
    precision: tinyint().default(2).notNull(),
    symbol: varchar({ length: 255 }).notNull(),
    symbol_native: varchar({ length: 255 }).notNull(),
    symbol_first: tinyint().default(1).notNull(),
    decimal_mark: varchar({ length: 1 }).default(".").notNull(),
    thousands_separator: varchar({ length: 1 }).default(",").notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "currencies_id" })]
);

export const customer_hotel = mysqlTable("customer_hotel", {
  customer_id: bigint({ mode: "number", unsigned: true })
    .notNull()
    .references(() => customers.id, { onDelete: "cascade" }),
  hotel_id: bigint({ mode: "number", unsigned: true })
    .notNull()
    .references(() => hotels.id, { onDelete: "cascade" }),
  created_at: timestamp({ mode: "string" }),
  updated_at: timestamp({ mode: "string" }),
});

export const customers = mysqlTable(
  "customers",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    parent_id: varchar({ length: 255 }),
    referral_code: varchar({ length: 255 }),
    firstname: varchar({ length: 255 }).notNull(),
    lastname: varchar({ length: 255 }).notNull(),
    phone: varchar({ length: 255 }).notNull(),
    otp: varchar({ length: 255 }),
    email: varchar({ length: 255 }),
    nok_name: varchar({ length: 255 }),
    nok_phone: varchar({ length: 255 }),
    nok_email: varchar({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_birth: date({ mode: "string" }),
    avatar: varchar({ length: 255 }),
    gender: varchar({ length: 255 }),
    address: varchar({ length: 255 }),
    city_id: bigint({ mode: "number", unsigned: true }).references(
      () => cities.id,
      { onDelete: "set null" }
    ),
    state_id: bigint({ mode: "number", unsigned: true }).references(
      () => states.id,
      { onDelete: "set null" }
    ),
    country_id: bigint({ mode: "number", unsigned: true }).references(
      () => countries.id,
      { onDelete: "set null" }
    ),
    nationality: varchar({ length: 255 }),
    business_id: bigint({ mode: "number", unsigned: true }).references(
      () => businesses.id,
      { onDelete: "cascade" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    password: varchar({ length: 255 }).default(
      "$2y$12$f1kewYVMIb1n4YlWEKmKu.z6MO/89qJ/UtzpzHoJOvWMAUGqVzi6q"
    ),
    device_name: varchar({ length: 255 }),
    remember_token: varchar({ length: 100 }),
    id_type: varchar({ length: 255 }),
    id_number: varchar({ length: 255 }),
    location: json(),
    latitude: decimal({ precision: 10, scale: 8 }),
    longitude: decimal({ precision: 11, scale: 8 }),
    token_version: bigint({ mode: "number" }).notNull(),
    socialProvider: text("social_provider"),
    socialId: text("social_id"),
  },
  (table) => [primaryKey({ columns: [table.id], name: "customers_id" })]
);

export const departments = mysqlTable(
  "departments",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    user_id: bigint({ mode: "number", unsigned: true }).references(
      (): AnyMySqlColumn => users.id,
      { onDelete: "set null" }
    ),
  },
  (table) => [primaryKey({ columns: [table.id], name: "departments_id" })]
);

export const expenditures = mysqlTable(
  "expenditures",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    source: varchar({ length: 255 }).notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "expenditures_id" })]
);

export const failed_jobs = mysqlTable(
  "failed_jobs",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    uuid: varchar({ length: 255 }).notNull(),
    connection: text().notNull(),
    queue: text().notNull(),
    payload: longtext().notNull(),
    exception: longtext().notNull(),
    failed_at: timestamp({ mode: "string" }).defaultNow().notNull(),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "failed_jobs_id" }),
    unique("failed_jobs_uuid_unique").on(table.uuid),
  ]
);

export const faqs = mysqlTable(
  "faqs",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    answer: longtext(),
    hotel_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => hotels.id, { onDelete: "cascade" }),
    question_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => questions.id, { onDelete: "cascade" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "faqs_id" })]
);

export const favourites = mysqlTable(
  "favourites",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    customer_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => customers.id, { onDelete: "cascade" }),
    hotel_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => hotels.id, { onDelete: "cascade" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "favourites_id" })]
);

export const finances = mysqlTable(
  "finances",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: varchar({ length: 255 }),
    amount: bigint({ mode: "number" }).notNull(),
    description: text(),
    financeable_id: int().notNull(),
    financeable_type: varchar({ length: 255 }).notNull(),
    type: varchar({ length: 255 }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "finances_id" }),
    unique("finances_code_unique").on(table.code),
  ]
);

export const floors = mysqlTable(
  "floors",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "floors_id" }),
    unique("floors_name_unique").on(table.name),
  ]
);

export const general_amenities = mysqlTable(
  "general_amenities",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    icon: text(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "general_amenities_id" }),
    unique("general_amenities_name_unique").on(table.name),
  ]
);

export const general_items = mysqlTable(
  "general_items",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    code: varchar({ length: 255 }),
    image: varchar({ length: 255 }),
    image_url: varchar({ length: 255 }),
    price: bigint({ mode: "number" }),
    description: text(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "general_items_id" }),
    unique("general_items_code_unique").on(table.code),
  ]
);

export const group_booking_hotel = mysqlTable("group_booking_hotel", {
  group_booking_id: bigint({ mode: "number", unsigned: true }).references(
    () => group_bookings.id,
    { onDelete: "cascade" }
  ),
  hotel_id: bigint({ mode: "number", unsigned: true }).references(
    () => hotels.id,
    { onDelete: "cascade" }
  ),
});

export const group_bookings = mysqlTable(
  "group_bookings",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    city_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => cities.id, { onDelete: "cascade" }),
    phone: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }),
    organisation: varchar({ length: 255 }),
    expected_guest: int().notNull(),
    room_needed: int(),
    room_type: varchar({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    room_from: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    room_to: date({ mode: "string" }),
    room_budget: int(),
    need_hall: tinyint().default(0).notNull(),
    hall_capacity: int(),
    hall_budget: int(),
    need_meal: tinyint().default(0).notNull(),
    meal_budget: int(),
    meal_attendees: int(),
    meal_options: json(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    hall_from: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    hall_to: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    meal_from: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    meal_to: date({ mode: "string" }),
    hotels: json(),
    allow_external_bids: tinyint().notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "group_bookings_id" })]
);

export const halls = mysqlTable(
  "halls",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    price: int(),
    capacity: int().notNull(),
    status: varchar({ length: 255 }).default("available"),
    description: text(),
    image: varchar({ length: 255 }),
    is_available: tinyint().default(1).notNull(),
    type_id: bigint({ mode: "number", unsigned: true }).references(
      () => types.id,
      { onDelete: "cascade" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    deposit: bigint({ mode: "number" }),
    promo_price: bigint({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_start_day: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_end_day: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    reference: date({ mode: "string" }),
    number: varchar({ length: 255 }),
    remarks: varchar({ length: 255 }).default("sellable"),
  },
  (table) => [primaryKey({ columns: [table.id], name: "halls_id" })]
);

export const hotel_galleries = mysqlTable(
  "hotel_galleries",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => hotels.id, { onDelete: "cascade" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    from: date({ mode: "string" }).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    to: date({ mode: "string" }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "hotel_galleries_id" })]
);

export const hotel_user = mysqlTable(
  "hotel_user",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => hotels.id, { onDelete: "cascade" }),
    user_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "hotel_user_id" })]
);

export const hotels = mysqlTable(
  "hotels",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    hall_lowest_price: bigint({ mode: "number" }),
    room_lowest_price: bigint({ mode: "number" }),
    code: varchar({ length: 255 }).notNull(),
    background_image: varchar({ length: 255 }),
    address: varchar({ length: 255 }).notNull(),
    primary_currency: varchar({ length: 255 }),
    secondary_currency: varchar({ length: 255 }),
    city_id: bigint({ mode: "number", unsigned: true }).references(
      () => cities.id,
      { onDelete: "set null" }
    ),
    zip_code: varchar({ length: 255 }),
    service_charge: int(),
    vat: tinyint().default(0),
    surcharge: int(),
    logo: varchar({ length: 255 }),
    primary_color: varchar({ length: 255 }),
    secondary_color: varchar({ length: 255 }),
    checked_out: time(),
    telephone: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    status: varchar({ length: 255 }).default("active"),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    renew: date({ mode: "string" }).default("2024-06-16"),
    percentage_charge: int().default(10),
    online_price: int().default(10),
    non_refundable_price: int().default(15),
    deleted_at: timestamp({ mode: "string" }),
    type: varchar({ length: 255 }).default("both"),
    team_id: bigint({ mode: "number", unsigned: true }).references(
      () => teams.id,
      { onDelete: "set null" }
    ),
    star_rating: int(),
    state_id: bigint({ mode: "number", unsigned: true }).references(
      () => states.id,
      { onDelete: "set null" }
    ),
    country_id: bigint({ mode: "number", unsigned: true }).references(
      () => states.id,
      { onDelete: "set null" }
    ),
    description: longtext(),
    created_by: bigint({ mode: "number", unsigned: true }).references(
      (): AnyMySqlColumn => users.id,
      { onDelete: "set null" }
    ),
    power_id: bigint({ mode: "number", unsigned: true }).references(
      () => powers.id,
      { onDelete: "set null" }
    ),
    location: json(),
    latitude: decimal({ precision: 10, scale: 8 }),
    longitude: decimal({ precision: 11, scale: 8 }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "hotels_id" }),
    unique("hotels_code_unique").on(table.code),
    unique("hotels_email_unique").on(table.email),
  ]
);

export const images = mysqlTable(
  "images",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    imageable_id: int().notNull(),
    imageable_type: varchar({ length: 255 }).notNull(),
    url: varchar({ length: 255 }).notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "images_id" }),
    unique("images_url_unique").on(table.url),
  ]
);

export const invoices = mysqlTable(
  "invoices",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    reference: varchar({ length: 255 }).notNull(),
    description: varchar({ length: 255 }).notNull(),
    subscription: varchar({ length: 255 }).default("yearly").notNull(),
    amount: bigint({ mode: "number" }).notNull(),
    status: varchar({ length: 255 }).default("unpaid").notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_paid: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    from: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    to: date({ mode: "string" }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    deleted_at: timestamp({ mode: "string" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "invoices_id" }),
    unique("invoices_reference_unique").on(table.reference),
  ]
);

export const items = mysqlTable(
  "items",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    cost_price: bigint({ mode: "number" }),
    price: bigint({ mode: "number" }).notNull(),
    quantity: bigint({ mode: "number" }).notNull(),
    discount: bigint({ mode: "number" }),
    limit: int(),
    image: varchar({ length: 255 }),
    revenue_id: bigint({ mode: "number", unsigned: true }).references(
      () => revenues.id,
      { onDelete: "set null" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    general_item_id: bigint({ mode: "number", unsigned: true }).references(
      () => general_items.id,
      { onDelete: "set null" }
    ),
  },
  (table) => [primaryKey({ columns: [table.id], name: "items_id" })]
);

export const jobs = mysqlTable(
  "jobs",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    queue: varchar({ length: 255 }).notNull(),
    payload: longtext().notNull(),
    attempts: tinyint({ unsigned: true }).notNull(),
    reserved_at: int({ unsigned: true }),
    available_at: int({ unsigned: true }).notNull(),
    created_at: int({ unsigned: true }).notNull(),
  },
  (table) => [
    index("jobs_queue_index").on(table.queue),
    primaryKey({ columns: [table.id], name: "jobs_id" }),
  ]
);

export const languages = mysqlTable(
  "languages",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: char({ length: 2 }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    name_native: varchar({ length: 255 }).notNull(),
    dir: char({ length: 3 }).notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "languages_id" })]
);

export const media = mysqlTable(
  "media",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    model_type: varchar({ length: 255 }).notNull(),
    model_id: bigint({ mode: "number", unsigned: true }).notNull(),
    uuid: char({ length: 36 }),
    collection_name: varchar({ length: 255 }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    file_name: varchar({ length: 255 }).notNull(),
    mime_type: varchar({ length: 255 }),
    disk: varchar({ length: 255 }).notNull(),
    conversions_disk: varchar({ length: 255 }),
    size: bigint({ mode: "number", unsigned: true }).notNull(),
    manipulations: json().notNull(),
    custom_properties: json().notNull(),
    generated_conversions: json().notNull(),
    responsive_images: json().notNull(),
    order_column: int({ unsigned: true }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    index("media_model_type_model_id_index").on(
      table.model_type,
      table.model_id
    ),
    index("media_order_column_index").on(table.order_column),
    primaryKey({ columns: [table.id], name: "media_id" }),
    unique("media_uuid_unique").on(table.uuid),
  ]
);

export const migrations = mysqlTable(
  "migrations",
  {
    id: int({ unsigned: true }).autoincrement().notNull(),
    migration: varchar({ length: 255 }).notNull(),
    batch: int().notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "migrations_id" })]
);

export const model_has_permissions = mysqlTable(
  "model_has_permissions",
  {
    permission_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => permissions.id, { onDelete: "cascade" }),
    model_type: varchar({ length: 255 }).notNull(),
    model_id: bigint({ mode: "number", unsigned: true }).notNull(),
  },
  (table) => [
    index("model_has_permissions_model_id_model_type_index").on(
      table.model_id,
      table.model_type
    ),
    primaryKey({
      columns: [table.permission_id, table.model_id, table.model_type],
      name: "model_has_permissions_permission_id_model_id_model_type",
    }),
  ]
);

export const model_has_roles = mysqlTable(
  "model_has_roles",
  {
    role_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => roles.id, { onDelete: "cascade" }),
    model_type: varchar({ length: 255 }).notNull(),
    model_id: bigint({ mode: "number", unsigned: true }).notNull(),
  },
  (table) => [
    index("model_has_roles_model_id_model_type_index").on(
      table.model_id,
      table.model_type
    ),
    primaryKey({
      columns: [table.role_id, table.model_id, table.model_type],
      name: "model_has_roles_role_id_model_id_model_type",
    }),
  ]
);

export const notifications = mysqlTable(
  "notifications",
  {
    id: char({ length: 36 }).notNull(),
    type: varchar({ length: 255 }).notNull(),
    notifiable_type: varchar({ length: 255 }).notNull(),
    notifiable_id: bigint({ mode: "number", unsigned: true }).notNull(),
    data: text().notNull(),
    read_at: timestamp({ mode: "string" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    index("notifications_notifiable_type_notifiable_id_index").on(
      table.notifiable_type,
      table.notifiable_id
    ),
    primaryKey({ columns: [table.id], name: "notifications_id" }),
  ]
);

export const orders = mysqlTable(
  "orders",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: varchar({ length: 255 }),
    mode_of_payment: varchar({ length: 255 }).default("cash").notNull(),
    type: varchar({ length: 255 }).default("customer").notNull(),
    total_price: int(),
    discount: int(),
    quantity: int().notNull(),
    customer_id: bigint({ mode: "number", unsigned: true }).references(
      () => customers.id,
      { onDelete: "cascade" }
    ),
    item_id: bigint({ mode: "number", unsigned: true }).references(
      () => items.id,
      { onDelete: "cascade" }
    ),
    agent_id: bigint({ mode: "number", unsigned: true }).references(
      () => users.id,
      { onDelete: "set null" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    booking_id: bigint({ mode: "number", unsigned: true }).references(
      () => bookings.id,
      { onDelete: "cascade" }
    ),
    sale_id: bigint({ mode: "number", unsigned: true }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "orders_id" }),
    unique("orders_code_unique").on(table.code),
  ]
);

export const password_reset_tokens = mysqlTable(
  "password_reset_tokens",
  {
    email: varchar({ length: 255 }).notNull(),
    token: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.email], name: "password_reset_tokens_email" }),
  ]
);

export const permissions = mysqlTable(
  "permissions",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    guard_name: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "permissions_id" }),
    unique("permissions_name_guard_name_unique").on(
      table.name,
      table.guard_name
    ),
  ]
);

export const personal_access_tokens = mysqlTable(
  "personal_access_tokens",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    tokenable_type: varchar({ length: 255 }).notNull(),
    tokenable_id: bigint({ mode: "number", unsigned: true }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    token: varchar({ length: 64 }).notNull(),
    abilities: text(),
    last_used_at: timestamp({ mode: "string" }),
    expires_at: timestamp({ mode: "string" }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    index("personal_access_tokens_tokenable_type_tokenable_id_index").on(
      table.tokenable_type,
      table.tokenable_id
    ),
    primaryKey({ columns: [table.id], name: "personal_access_tokens_id" }),
    unique("personal_access_tokens_token_unique").on(table.token),
  ]
);

export const powers = mysqlTable(
  "powers",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    icon: varchar({ length: 255 }),
    number: varchar({ length: 255 }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "powers_id" }),
    unique("powers_icon_unique").on(table.icon),
    unique("powers_name_unique").on(table.name),
    unique("powers_number_unique").on(table.number),
  ]
);

export const profiles = mysqlTable(
  "profiles",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    account_number: varchar({ length: 255 }),
    account_name: varchar({ length: 255 }),
    bank_name: varchar({ length: 255 }),
    special_bonus: varchar({ length: 255 }),
    paye: int(),
    pension_fund_contribution: int(),
    other_deductions: int(),
    surcharges: int(),
    tip: int(),
    health_insurance: int(),
    transport_allowance: int(),
    housing_allowance: int(),
    special_duty_allowance: int(),
    overtime_allowance: int(),
    user_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => users.id, { onDelete: "cascade" }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "profiles_id" })]
);

export const questions = mysqlTable(
  "questions",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    text: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "questions_id" }),
    unique("questions_text_unique").on(table.text),
  ]
);

export const ratings = mysqlTable(
  "ratings",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    customer_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => customers.id, { onDelete: "cascade" }),
    hotel_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => hotels.id, { onDelete: "cascade" }),
    title: varchar({ length: 255 }).notNull(),
    body: text(),
    number: int().notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "ratings_id" })]
);

export const revenues = mysqlTable(
  "revenues",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    source: varchar({ length: 255 }).notNull(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "revenues_id" })]
);

export const role_has_permissions = mysqlTable(
  "role_has_permissions",
  {
    permission_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => permissions.id, { onDelete: "cascade" }),
    role_id: bigint({ mode: "number", unsigned: true })
      .notNull()
      .references(() => roles.id, { onDelete: "cascade" }),
  },
  (table) => [
    primaryKey({
      columns: [table.permission_id, table.role_id],
      name: "role_has_permissions_permission_id_role_id",
    }),
  ]
);

export const roles = mysqlTable(
  "roles",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    guard_name: varchar({ length: 255 }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "roles_id" }),
    unique("roles_name_guard_name_unique").on(table.name, table.guard_name),
  ]
);

export const rooms = mysqlTable(
  "rooms",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    number: varchar({ length: 255 }).notNull(),
    type_id: bigint({ mode: "number", unsigned: true }).references(
      () => types.id,
      { onDelete: "cascade" }
    ),
    floor_id: bigint({ mode: "number", unsigned: true }).references(
      () => floors.id,
      { onDelete: "set null" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    price: int(),
    capacity: int().notNull(),
    bed_type: varchar({ length: 255 }).notNull(),
    status: varchar({ length: 255 }).default("available"),
    character: varchar({ length: 255 }).default("no smoking"),
    description: text(),
    image: varchar({ length: 255 }),
    is_available: tinyint().default(1).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    comment: varchar({ length: 255 }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "rooms_id" })]
);

export const sales = mysqlTable(
  "sales",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: varchar({ length: 255 }),
    mode_of_payment: varchar({ length: 255 }).default("cash").notNull(),
    type: varchar({ length: 255 }).default("customer").notNull(),
    total: int(),
    discount: int().default(0),
    customer_id: bigint({ mode: "number", unsigned: true }).references(
      () => customers.id,
      { onDelete: "cascade" }
    ),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    agent_id: bigint({ mode: "number", unsigned: true }).references(
      () => users.id,
      { onDelete: "set null" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    booking_id: bigint({ mode: "number", unsigned: true }).references(
      () => bookings.id,
      { onDelete: "cascade" }
    ),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "sales_id" }),
    unique("sales_code_unique").on(table.code),
  ]
);

export const states = mysqlTable(
  "states",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: bigint({ mode: "number", unsigned: true }).notNull(),
    name: varchar({ length: 255 }).notNull(),
    country_code: varchar({ length: 3 }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "states_id" })]
);

export const statuses = mysqlTable(
  "statuses",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    status: varchar({ length: 255 }),
    booking_id: bigint({ mode: "number", unsigned: true }).references(
      () => bookings.id,
      { onDelete: "cascade" }
    ),
    room_id: bigint({ mode: "number", unsigned: true }).references(
      () => rooms.id,
      { onDelete: "cascade" }
    ),
    hall_id: bigint({ mode: "number", unsigned: true }).references(
      () => halls.id,
      { onDelete: "cascade" }
    ),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date: date({ mode: "string" }).notNull(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "statuses_id" })]
);

export const suppliers = mysqlTable(
  "suppliers",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }),
    phone: varchar({ length: 255 }),
    website: varchar({ length: 255 }),
    image: varchar({ length: 255 }),
    description: longtext(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    category_id: bigint({ mode: "number", unsigned: true }).references(
      () => categories.id,
      { onDelete: "cascade" }
    ),
    company_name: varchar({ length: 255 }),
    status: varchar({ length: 255 }).default("active"),
    is_trusted_and_tested: tinyint().default(0).notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "suppliers_id" })]
);

export const team_user = mysqlTable("team_user", {
  team_id: bigint({ mode: "number", unsigned: true })
    .notNull()
    .references(() => teams.id, { onDelete: "cascade" }),
  marketer_id: bigint({ mode: "number", unsigned: true })
    .notNull()
    .references(() => users.id, { onDelete: "cascade" }),
  created_at: timestamp({ mode: "string" }),
  updated_at: timestamp({ mode: "string" }),
});

export const teams = mysqlTable(
  "teams",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    code: varchar({ length: 255 }),
    description: text(),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "teams_id" }),
    unique("teams_code_unique").on(table.code),
  ]
);

export const timezones = mysqlTable(
  "timezones",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: bigint({ mode: "number", unsigned: true }).notNull(),
    name: varchar({ length: 255 }).notNull(),
  },
  (table) => [primaryKey({ columns: [table.id], name: "timezones_id" })]
);

export const types = mysqlTable(
  "types",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: varchar({ length: 255 }).notNull(),
    price1: bigint({ mode: "number" }),
    price2: bigint({ mode: "number" }),
    deposit1: bigint({ mode: "number" }),
    deposit2: bigint({ mode: "number" }),
    weekend_price: bigint({ mode: "number" }),
    weekend_start_day: varchar({ length: 255 }),
    weekend_end_day: varchar({ length: 255 }),
    promo_price: bigint({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_start_day: date({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_end_day: date({ mode: "string" }),
    remarks: text(),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "types_id" })]
);

export const users = mysqlTable(
  "users",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    firstname: varchar({ length: 255 }).notNull(),
    department_id: bigint({ mode: "number", unsigned: true }).references(
      (): AnyMySqlColumn => departments.id,
      { onDelete: "set null" }
    ),
    lastname: varchar({ length: 255 }).notNull(),
    username: varchar({ length: 255 }).notNull(),
    phone: varchar({ length: 255 }).notNull(),
    email: varchar({ length: 255 }),
    nok_name: varchar({ length: 255 }),
    nok_avatar: varchar({ length: 255 }),
    nok_phone: varchar({ length: 255 }),
    nok_email: varchar({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_birth: date({ mode: "string" }),
    can_login: tinyint().default(1).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_employment: date({ mode: "string" }).notNull(),
    avatar: varchar({ length: 255 }),
    current_role: varchar({ length: 255 }),
    designation: varchar({ length: 255 }).notNull(),
    gender: varchar({ length: 255 }),
    address: text(),
    account_number: varchar({ length: 255 }),
    bank_name: varchar({ length: 255 }),
    account_name: varchar({ length: 255 }),
    signature: varchar({ length: 255 }),
    gross_annual_salary: bigint({ mode: "number" }),
    payee: bigint({ mode: "number" }),
    surcharges: bigint({ mode: "number" }),
    special_bonus: bigint({ mode: "number" }),
    health_insurance: bigint({ mode: "number" }),
    transport_insurance: bigint({ mode: "number" }),
    housing_insurance: bigint({ mode: "number" }),
    overtime_insurance: bigint({ mode: "number" }),
    special_duty_insurance: bigint({ mode: "number" }),
    other_deductions: bigint({ mode: "number" }),
    city_id: bigint({ mode: "number" }),
    state_id: bigint({ mode: "number" }),
    country_id: bigint({ mode: "number" }),
    password: varchar({ length: 255 }).notNull(),
    two_factor_secret: text(),
    two_factor_recovery_codes: text(),
    two_factor_confirmed_at: timestamp({ mode: "string" }),
    is_new: tinyint().default(1).notNull(),
    email_verified_at: timestamp({ mode: "string" }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      (): AnyMySqlColumn => hotels.id,
      { onDelete: "cascade" }
    ),
    remember_token: varchar({ length: 100 }),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
    otp: varchar({ length: 255 }),
    device_name: varchar({ length: 255 }).default("web"),
    location: json(),
    latitude: decimal({ precision: 10, scale: 8 }),
    longitude: decimal({ precision: 11, scale: 8 }),
  },
  (table) => [
    primaryKey({ columns: [table.id], name: "users_id" }),
    unique("users_email_unique").on(table.email),
    unique("users_phone_unique").on(table.phone),
    unique("users_username_unique").on(table.username),
  ]
);

export const videos = mysqlTable(
  "videos",
  {
    id: bigint({ mode: "number", unsigned: true }).autoincrement().notNull(),
    url: varchar({ length: 255 }).notNull(),
    type: varchar({ length: 255 }),
    hotel_id: bigint({ mode: "number", unsigned: true }).references(
      () => hotels.id,
      { onDelete: "cascade" }
    ),
    created_at: timestamp({ mode: "string" }),
    updated_at: timestamp({ mode: "string" }),
  },
  (table) => [primaryKey({ columns: [table.id], name: "videos_id" })]
);
