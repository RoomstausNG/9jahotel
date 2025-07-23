"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.rooms = exports.roles = exports.role_has_permissions = exports.revenues = exports.ratings = exports.questions = exports.profiles = exports.powers = exports.personal_access_tokens = exports.permissions = exports.password_reset_tokens = exports.orders = exports.notifications = exports.model_has_roles = exports.model_has_permissions = exports.migrations = exports.media = exports.languages = exports.jobs = exports.items = exports.invoices = exports.images = exports.hotels = exports.hotel_user = exports.hotel_galleries = exports.halls = exports.group_bookings = exports.group_booking_hotel = exports.general_items = exports.general_amenities = exports.floors = exports.finances = exports.favourites = exports.faqs = exports.failed_jobs = exports.expenditures = exports.departments = exports.customers = exports.customer_hotel = exports.currencies = exports.credit_payments = exports.countries = exports.cities = exports.categories = exports.businesses = exports.bookings = exports.booking_invoices = exports.amenity_hotel = exports.amenities = exports.activity_log = void 0;
exports.videos = exports.users = exports.types = exports.timezones = exports.teams = exports.team_user = exports.suppliers = exports.statuses = exports.states = exports.sales = void 0;
var mysql_core_1 = require("drizzle-orm/mysql-core");
exports.activity_log = (0, mysql_core_1.mysqlTable)("activity_log", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    log_name: (0, mysql_core_1.varchar)({ length: 255 }),
    description: (0, mysql_core_1.text)().notNull(),
    subject_type: (0, mysql_core_1.varchar)({ length: 255 }),
    event: (0, mysql_core_1.varchar)({ length: 255 }),
    subject_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }),
    causer_type: (0, mysql_core_1.varchar)({ length: 255 }),
    causer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }),
    properties: (0, mysql_core_1.json)(),
    batch_uuid: (0, mysql_core_1.char)({ length: 36 }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.index)("activity_log_log_name_index").on(table.log_name),
    (0, mysql_core_1.index)("causer").on(table.causer_type, table.causer_id),
    (0, mysql_core_1.index)("subject").on(table.subject_type, table.subject_id),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "activity_log_id" }),
]; });
exports.amenities = (0, mysql_core_1.mysqlTable)("amenities", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    icon: (0, mysql_core_1.text)(),
    general_amenity_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }),
}, function (table) { return [
    (0, mysql_core_1.foreignKey)({
        columns: [table.general_amenity_id],
        foreignColumns: [table.id],
        name: "amenities_general_amenity_id_foreign",
    }).onDelete("set null"),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "amenities_id" }),
]; });
exports.amenity_hotel = (0, mysql_core_1.mysqlTable)("amenity_hotel", {
    amenity_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.amenities.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
});
exports.booking_invoices = (0, mysql_core_1.mysqlTable)("booking_invoices", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    reference: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    amount: (0, mysql_core_1.int)().notNull(),
    commission_amount: (0, mysql_core_1.int)(),
    hotel_payable: (0, mysql_core_1.int)(),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("unpaid"),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.customers.id; }, { onDelete: "set null" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    payed_on: (0, mysql_core_1.date)({ mode: "string" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    deleted_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    is_settled: (0, mysql_core_1.tinyint)().default(0),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_settled: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_in: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_out: (0, mysql_core_1.date)({ mode: "string" }),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("online"),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "booking_invoices_id" }),
    (0, mysql_core_1.unique)("booking_invoices_reference_unique").on(table.reference),
]; });
exports.bookings = (0, mysql_core_1.mysqlTable)("bookings", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    bookable_id: (0, mysql_core_1.int)().notNull(),
    bookable_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    due: (0, mysql_core_1.bigint)({ mode: "number" }),
    discount: (0, mysql_core_1.bigint)({ mode: "number" }),
    amount_paid: (0, mysql_core_1.bigint)({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_in: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    checked_out: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("booked"),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("room"),
    agent_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.users.id; }, { onDelete: "set null" }),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    mode_of_payment: (0, mysql_core_1.varchar)({ length: 255 }),
    via: (0, mysql_core_1.varchar)({ length: 255 }).default("9jahotel"),
    booking_invoice_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.booking_invoices.id; }, { onDelete: "cascade" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "bookings_id" }),
    (0, mysql_core_1.unique)("bookings_code_unique").on(table.code),
]; });
exports.businesses = (0, mysql_core_1.mysqlTable)("businesses", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    phone: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    email: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    address: (0, mysql_core_1.varchar)({ length: 255 }),
    status: (0, mysql_core_1.varchar)({ length: 255 }),
    city_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.cities.id; }, { onDelete: "set null" }),
    balance: (0, mysql_core_1.bigint)({ mode: "number" }),
    limit: (0, mysql_core_1.bigint)({ mode: "number" }).default(500000),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "businesses_id" })]; });
exports.categories = (0, mysql_core_1.mysqlTable)("categories", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("supply"),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    deleted_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "categories_id" })]; });
exports.cities = (0, mysql_core_1.mysqlTable)("cities", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    state_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    country_code: (0, mysql_core_1.varchar)({ length: 3 }).notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "cities_id" })]; });
exports.countries = (0, mysql_core_1.mysqlTable)("countries", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    iso2: (0, mysql_core_1.varchar)({ length: 2 }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    status: (0, mysql_core_1.tinyint)().default(1).notNull(),
    phone_code: (0, mysql_core_1.varchar)({ length: 5 }).notNull(),
    iso3: (0, mysql_core_1.varchar)({ length: 3 }).notNull(),
    region: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    subregion: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "countries_id" })]; });
exports.credit_payments = (0, mysql_core_1.mysqlTable)("credit_payments", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    amount_paid: (0, mysql_core_1.int)().notNull(),
    amount_owed: (0, mysql_core_1.int)(),
    business_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.businesses.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "credit_payments_id" })]; });
exports.currencies = (0, mysql_core_1.mysqlTable)("currencies", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    precision: (0, mysql_core_1.tinyint)().default(2).notNull(),
    symbol: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    symbol_native: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    symbol_first: (0, mysql_core_1.tinyint)().default(1).notNull(),
    decimal_mark: (0, mysql_core_1.varchar)({ length: 1 }).default(".").notNull(),
    thousands_separator: (0, mysql_core_1.varchar)({ length: 1 }).default(",").notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "currencies_id" })]; });
exports.customer_hotel = (0, mysql_core_1.mysqlTable)("customer_hotel", {
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
});
exports.customers = (0, mysql_core_1.mysqlTable)("customers", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    parent_id: (0, mysql_core_1.varchar)({ length: 255 }),
    referral_code: (0, mysql_core_1.varchar)({ length: 255 }),
    firstname: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    lastname: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    phone: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    otp: (0, mysql_core_1.varchar)({ length: 255 }),
    email: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_name: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_phone: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_email: (0, mysql_core_1.varchar)({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_birth: (0, mysql_core_1.date)({ mode: "string" }),
    avatar: (0, mysql_core_1.varchar)({ length: 255 }),
    gender: (0, mysql_core_1.varchar)({ length: 255 }),
    address: (0, mysql_core_1.varchar)({ length: 255 }),
    city_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.cities.id; }, { onDelete: "set null" }),
    state_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.states.id; }, { onDelete: "set null" }),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.countries.id; }, { onDelete: "set null" }),
    nationality: (0, mysql_core_1.varchar)({ length: 255 }),
    business_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.businesses.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    password: (0, mysql_core_1.varchar)({ length: 255 }).default("$2y$12$f1kewYVMIb1n4YlWEKmKu.z6MO/89qJ/UtzpzHoJOvWMAUGqVzi6q"),
    device_name: (0, mysql_core_1.varchar)({ length: 255 }),
    remember_token: (0, mysql_core_1.varchar)({ length: 100 }),
    id_type: (0, mysql_core_1.varchar)({ length: 255 }),
    id_number: (0, mysql_core_1.varchar)({ length: 255 }),
    location: (0, mysql_core_1.json)(),
    latitude: (0, mysql_core_1.decimal)({ precision: 10, scale: 8 }),
    longitude: (0, mysql_core_1.decimal)({ precision: 11, scale: 8 }),
    token_version: (0, mysql_core_1.bigint)({ mode: "number" }).notNull(),
    socialProvider: (0, mysql_core_1.text)("social_provider"),
    socialId: (0, mysql_core_1.text)("social_id"),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "customers_id" })]; });
exports.departments = (0, mysql_core_1.mysqlTable)("departments", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    user_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.users.id; }, { onDelete: "set null" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "departments_id" })]; });
exports.expenditures = (0, mysql_core_1.mysqlTable)("expenditures", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    source: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "expenditures_id" })]; });
exports.failed_jobs = (0, mysql_core_1.mysqlTable)("failed_jobs", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    uuid: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    connection: (0, mysql_core_1.text)().notNull(),
    queue: (0, mysql_core_1.text)().notNull(),
    payload: (0, mysql_core_1.longtext)().notNull(),
    exception: (0, mysql_core_1.longtext)().notNull(),
    failed_at: (0, mysql_core_1.timestamp)({ mode: "string" }).defaultNow().notNull(),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "failed_jobs_id" }),
    (0, mysql_core_1.unique)("failed_jobs_uuid_unique").on(table.uuid),
]; });
exports.faqs = (0, mysql_core_1.mysqlTable)("faqs", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    answer: (0, mysql_core_1.longtext)(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    question_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.questions.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "faqs_id" })]; });
exports.favourites = (0, mysql_core_1.mysqlTable)("favourites", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "favourites_id" })]; });
exports.finances = (0, mysql_core_1.mysqlTable)("finances", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }),
    amount: (0, mysql_core_1.bigint)({ mode: "number" }).notNull(),
    description: (0, mysql_core_1.text)(),
    financeable_id: (0, mysql_core_1.int)().notNull(),
    financeable_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    type: (0, mysql_core_1.varchar)({ length: 255 }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "finances_id" }),
    (0, mysql_core_1.unique)("finances_code_unique").on(table.code),
]; });
exports.floors = (0, mysql_core_1.mysqlTable)("floors", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "floors_id" }),
    (0, mysql_core_1.unique)("floors_name_unique").on(table.name),
]; });
exports.general_amenities = (0, mysql_core_1.mysqlTable)("general_amenities", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    icon: (0, mysql_core_1.text)(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "general_amenities_id" }),
    (0, mysql_core_1.unique)("general_amenities_name_unique").on(table.name),
]; });
exports.general_items = (0, mysql_core_1.mysqlTable)("general_items", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    image_url: (0, mysql_core_1.varchar)({ length: 255 }),
    price: (0, mysql_core_1.bigint)({ mode: "number" }),
    description: (0, mysql_core_1.text)(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "general_items_id" }),
    (0, mysql_core_1.unique)("general_items_code_unique").on(table.code),
]; });
exports.group_booking_hotel = (0, mysql_core_1.mysqlTable)("group_booking_hotel", {
    group_booking_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.group_bookings.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
});
exports.group_bookings = (0, mysql_core_1.mysqlTable)("group_bookings", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    city_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.cities.id; }, { onDelete: "cascade" }),
    phone: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    email: (0, mysql_core_1.varchar)({ length: 255 }),
    organisation: (0, mysql_core_1.varchar)({ length: 255 }),
    expected_guest: (0, mysql_core_1.int)().notNull(),
    room_needed: (0, mysql_core_1.int)(),
    room_type: (0, mysql_core_1.varchar)({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    room_from: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    room_to: (0, mysql_core_1.date)({ mode: "string" }),
    room_budget: (0, mysql_core_1.int)(),
    need_hall: (0, mysql_core_1.tinyint)().default(0).notNull(),
    hall_capacity: (0, mysql_core_1.int)(),
    hall_budget: (0, mysql_core_1.int)(),
    need_meal: (0, mysql_core_1.tinyint)().default(0).notNull(),
    meal_budget: (0, mysql_core_1.int)(),
    meal_attendees: (0, mysql_core_1.int)(),
    meal_options: (0, mysql_core_1.json)(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    hall_from: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    hall_to: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    meal_from: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    meal_to: (0, mysql_core_1.date)({ mode: "string" }),
    hotels: (0, mysql_core_1.json)(),
    allow_external_bids: (0, mysql_core_1.tinyint)().notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "group_bookings_id" })]; });
exports.halls = (0, mysql_core_1.mysqlTable)("halls", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    price: (0, mysql_core_1.int)(),
    capacity: (0, mysql_core_1.int)().notNull(),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("available"),
    description: (0, mysql_core_1.text)(),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    is_available: (0, mysql_core_1.tinyint)().default(1).notNull(),
    type_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.types.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    deposit: (0, mysql_core_1.bigint)({ mode: "number" }),
    promo_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_start_day: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_end_day: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    reference: (0, mysql_core_1.date)({ mode: "string" }),
    number: (0, mysql_core_1.varchar)({ length: 255 }),
    remarks: (0, mysql_core_1.varchar)({ length: 255 }).default("sellable"),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "halls_id" })]; });
exports.hotel_galleries = (0, mysql_core_1.mysqlTable)("hotel_galleries", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    from: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    to: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "hotel_galleries_id" })]; });
exports.hotel_user = (0, mysql_core_1.mysqlTable)("hotel_user", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    user_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.users.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "hotel_user_id" })]; });
exports.hotels = (0, mysql_core_1.mysqlTable)("hotels", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hall_lowest_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    room_lowest_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    code: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    background_image: (0, mysql_core_1.varchar)({ length: 255 }),
    address: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    primary_currency: (0, mysql_core_1.varchar)({ length: 255 }),
    secondary_currency: (0, mysql_core_1.varchar)({ length: 255 }),
    city_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.cities.id; }, { onDelete: "set null" }),
    zip_code: (0, mysql_core_1.varchar)({ length: 255 }),
    service_charge: (0, mysql_core_1.int)(),
    vat: (0, mysql_core_1.tinyint)().default(0),
    surcharge: (0, mysql_core_1.int)(),
    logo: (0, mysql_core_1.varchar)({ length: 255 }),
    primary_color: (0, mysql_core_1.varchar)({ length: 255 }),
    secondary_color: (0, mysql_core_1.varchar)({ length: 255 }),
    checked_out: (0, mysql_core_1.time)(),
    telephone: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    email: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("active"),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    renew: (0, mysql_core_1.date)({ mode: "string" }).default("2024-06-16"),
    percentage_charge: (0, mysql_core_1.int)().default(10),
    online_price: (0, mysql_core_1.int)().default(10),
    non_refundable_price: (0, mysql_core_1.int)().default(15),
    deleted_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("both"),
    team_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.teams.id; }, { onDelete: "set null" }),
    star_rating: (0, mysql_core_1.int)(),
    state_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.states.id; }, { onDelete: "set null" }),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.states.id; }, { onDelete: "set null" }),
    description: (0, mysql_core_1.longtext)(),
    created_by: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.users.id; }, { onDelete: "set null" }),
    power_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.powers.id; }, { onDelete: "set null" }),
    location: (0, mysql_core_1.json)(),
    latitude: (0, mysql_core_1.decimal)({ precision: 10, scale: 8 }),
    longitude: (0, mysql_core_1.decimal)({ precision: 11, scale: 8 }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "hotels_id" }),
    (0, mysql_core_1.unique)("hotels_code_unique").on(table.code),
    (0, mysql_core_1.unique)("hotels_email_unique").on(table.email),
]; });
exports.images = (0, mysql_core_1.mysqlTable)("images", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    imageable_id: (0, mysql_core_1.int)().notNull(),
    imageable_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    url: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "images_id" }),
    (0, mysql_core_1.unique)("images_url_unique").on(table.url),
]; });
exports.invoices = (0, mysql_core_1.mysqlTable)("invoices", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    reference: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    description: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    subscription: (0, mysql_core_1.varchar)({ length: 255 }).default("yearly").notNull(),
    amount: (0, mysql_core_1.bigint)({ mode: "number" }).notNull(),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("unpaid").notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_paid: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    from: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    to: (0, mysql_core_1.date)({ mode: "string" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    deleted_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "invoices_id" }),
    (0, mysql_core_1.unique)("invoices_reference_unique").on(table.reference),
]; });
exports.items = (0, mysql_core_1.mysqlTable)("items", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    cost_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    price: (0, mysql_core_1.bigint)({ mode: "number" }).notNull(),
    quantity: (0, mysql_core_1.bigint)({ mode: "number" }).notNull(),
    discount: (0, mysql_core_1.bigint)({ mode: "number" }),
    limit: (0, mysql_core_1.int)(),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    revenue_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.revenues.id; }, { onDelete: "set null" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    general_item_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.general_items.id; }, { onDelete: "set null" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "items_id" })]; });
exports.jobs = (0, mysql_core_1.mysqlTable)("jobs", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    queue: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    payload: (0, mysql_core_1.longtext)().notNull(),
    attempts: (0, mysql_core_1.tinyint)({ unsigned: true }).notNull(),
    reserved_at: (0, mysql_core_1.int)({ unsigned: true }),
    available_at: (0, mysql_core_1.int)({ unsigned: true }).notNull(),
    created_at: (0, mysql_core_1.int)({ unsigned: true }).notNull(),
}, function (table) { return [
    (0, mysql_core_1.index)("jobs_queue_index").on(table.queue),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "jobs_id" }),
]; });
exports.languages = (0, mysql_core_1.mysqlTable)("languages", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: (0, mysql_core_1.char)({ length: 2 }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    name_native: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    dir: (0, mysql_core_1.char)({ length: 3 }).notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "languages_id" })]; });
exports.media = (0, mysql_core_1.mysqlTable)("media", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    model_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    model_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    uuid: (0, mysql_core_1.char)({ length: 36 }),
    collection_name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    file_name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    mime_type: (0, mysql_core_1.varchar)({ length: 255 }),
    disk: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    conversions_disk: (0, mysql_core_1.varchar)({ length: 255 }),
    size: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    manipulations: (0, mysql_core_1.json)().notNull(),
    custom_properties: (0, mysql_core_1.json)().notNull(),
    generated_conversions: (0, mysql_core_1.json)().notNull(),
    responsive_images: (0, mysql_core_1.json)().notNull(),
    order_column: (0, mysql_core_1.int)({ unsigned: true }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.index)("media_model_type_model_id_index").on(table.model_type, table.model_id),
    (0, mysql_core_1.index)("media_order_column_index").on(table.order_column),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "media_id" }),
    (0, mysql_core_1.unique)("media_uuid_unique").on(table.uuid),
]; });
exports.migrations = (0, mysql_core_1.mysqlTable)("migrations", {
    id: (0, mysql_core_1.int)({ unsigned: true }).autoincrement().notNull(),
    migration: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    batch: (0, mysql_core_1.int)().notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "migrations_id" })]; });
exports.model_has_permissions = (0, mysql_core_1.mysqlTable)("model_has_permissions", {
    permission_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.permissions.id; }, { onDelete: "cascade" }),
    model_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    model_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
}, function (table) { return [
    (0, mysql_core_1.index)("model_has_permissions_model_id_model_type_index").on(table.model_id, table.model_type),
    (0, mysql_core_1.primaryKey)({
        columns: [table.permission_id, table.model_id, table.model_type],
        name: "model_has_permissions_permission_id_model_id_model_type",
    }),
]; });
exports.model_has_roles = (0, mysql_core_1.mysqlTable)("model_has_roles", {
    role_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.roles.id; }, { onDelete: "cascade" }),
    model_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    model_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
}, function (table) { return [
    (0, mysql_core_1.index)("model_has_roles_model_id_model_type_index").on(table.model_id, table.model_type),
    (0, mysql_core_1.primaryKey)({
        columns: [table.role_id, table.model_id, table.model_type],
        name: "model_has_roles_role_id_model_id_model_type",
    }),
]; });
exports.notifications = (0, mysql_core_1.mysqlTable)("notifications", {
    id: (0, mysql_core_1.char)({ length: 36 }).notNull(),
    type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    notifiable_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    notifiable_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    data: (0, mysql_core_1.text)().notNull(),
    read_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.index)("notifications_notifiable_type_notifiable_id_index").on(table.notifiable_type, table.notifiable_id),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "notifications_id" }),
]; });
exports.orders = (0, mysql_core_1.mysqlTable)("orders", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }),
    mode_of_payment: (0, mysql_core_1.varchar)({ length: 255 }).default("cash").notNull(),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("customer").notNull(),
    total_price: (0, mysql_core_1.int)(),
    discount: (0, mysql_core_1.int)(),
    quantity: (0, mysql_core_1.int)().notNull(),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    item_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.items.id; }, { onDelete: "cascade" }),
    agent_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.users.id; }, { onDelete: "set null" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    booking_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.bookings.id; }, { onDelete: "cascade" }),
    sale_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "orders_id" }),
    (0, mysql_core_1.unique)("orders_code_unique").on(table.code),
]; });
exports.password_reset_tokens = (0, mysql_core_1.mysqlTable)("password_reset_tokens", {
    email: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    token: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.email], name: "password_reset_tokens_email" }),
]; });
exports.permissions = (0, mysql_core_1.mysqlTable)("permissions", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    guard_name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "permissions_id" }),
    (0, mysql_core_1.unique)("permissions_name_guard_name_unique").on(table.name, table.guard_name),
]; });
exports.personal_access_tokens = (0, mysql_core_1.mysqlTable)("personal_access_tokens", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    tokenable_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    tokenable_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    token: (0, mysql_core_1.varchar)({ length: 64 }).notNull(),
    abilities: (0, mysql_core_1.text)(),
    last_used_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    expires_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.index)("personal_access_tokens_tokenable_type_tokenable_id_index").on(table.tokenable_type, table.tokenable_id),
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "personal_access_tokens_id" }),
    (0, mysql_core_1.unique)("personal_access_tokens_token_unique").on(table.token),
]; });
exports.powers = (0, mysql_core_1.mysqlTable)("powers", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    icon: (0, mysql_core_1.varchar)({ length: 255 }),
    number: (0, mysql_core_1.varchar)({ length: 255 }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "powers_id" }),
    (0, mysql_core_1.unique)("powers_icon_unique").on(table.icon),
    (0, mysql_core_1.unique)("powers_name_unique").on(table.name),
    (0, mysql_core_1.unique)("powers_number_unique").on(table.number),
]; });
exports.profiles = (0, mysql_core_1.mysqlTable)("profiles", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    account_number: (0, mysql_core_1.varchar)({ length: 255 }),
    account_name: (0, mysql_core_1.varchar)({ length: 255 }),
    bank_name: (0, mysql_core_1.varchar)({ length: 255 }),
    special_bonus: (0, mysql_core_1.varchar)({ length: 255 }),
    paye: (0, mysql_core_1.int)(),
    pension_fund_contribution: (0, mysql_core_1.int)(),
    other_deductions: (0, mysql_core_1.int)(),
    surcharges: (0, mysql_core_1.int)(),
    tip: (0, mysql_core_1.int)(),
    health_insurance: (0, mysql_core_1.int)(),
    transport_allowance: (0, mysql_core_1.int)(),
    housing_allowance: (0, mysql_core_1.int)(),
    special_duty_allowance: (0, mysql_core_1.int)(),
    overtime_allowance: (0, mysql_core_1.int)(),
    user_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.users.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "profiles_id" })]; });
exports.questions = (0, mysql_core_1.mysqlTable)("questions", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    text: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "questions_id" }),
    (0, mysql_core_1.unique)("questions_text_unique").on(table.text),
]; });
exports.ratings = (0, mysql_core_1.mysqlTable)("ratings", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    title: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    body: (0, mysql_core_1.text)(),
    number: (0, mysql_core_1.int)().notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "ratings_id" })]; });
exports.revenues = (0, mysql_core_1.mysqlTable)("revenues", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    source: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "revenues_id" })]; });
exports.role_has_permissions = (0, mysql_core_1.mysqlTable)("role_has_permissions", {
    permission_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.permissions.id; }, { onDelete: "cascade" }),
    role_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.roles.id; }, { onDelete: "cascade" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({
        columns: [table.permission_id, table.role_id],
        name: "role_has_permissions_permission_id_role_id",
    }),
]; });
exports.roles = (0, mysql_core_1.mysqlTable)("roles", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    guard_name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "roles_id" }),
    (0, mysql_core_1.unique)("roles_name_guard_name_unique").on(table.name, table.guard_name),
]; });
exports.rooms = (0, mysql_core_1.mysqlTable)("rooms", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    number: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    type_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.types.id; }, { onDelete: "cascade" }),
    floor_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.floors.id; }, { onDelete: "set null" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    price: (0, mysql_core_1.int)(),
    capacity: (0, mysql_core_1.int)().notNull(),
    bed_type: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("available"),
    character: (0, mysql_core_1.varchar)({ length: 255 }).default("no smoking"),
    description: (0, mysql_core_1.text)(),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    is_available: (0, mysql_core_1.tinyint)().default(1).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    comment: (0, mysql_core_1.varchar)({ length: 255 }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "rooms_id" })]; });
exports.sales = (0, mysql_core_1.mysqlTable)("sales", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }),
    mode_of_payment: (0, mysql_core_1.varchar)({ length: 255 }).default("cash").notNull(),
    type: (0, mysql_core_1.varchar)({ length: 255 }).default("customer").notNull(),
    total: (0, mysql_core_1.int)(),
    discount: (0, mysql_core_1.int)().default(0),
    customer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.customers.id; }, { onDelete: "cascade" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    agent_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.users.id; }, { onDelete: "set null" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    booking_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.bookings.id; }, { onDelete: "cascade" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "sales_id" }),
    (0, mysql_core_1.unique)("sales_code_unique").on(table.code),
]; });
exports.states = (0, mysql_core_1.mysqlTable)("states", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    country_code: (0, mysql_core_1.varchar)({ length: 3 }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "states_id" })]; });
exports.statuses = (0, mysql_core_1.mysqlTable)("statuses", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    status: (0, mysql_core_1.varchar)({ length: 255 }),
    booking_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.bookings.id; }, { onDelete: "cascade" }),
    room_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.rooms.id; }, { onDelete: "cascade" }),
    hall_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.halls.id; }, { onDelete: "cascade" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "statuses_id" })]; });
exports.suppliers = (0, mysql_core_1.mysqlTable)("suppliers", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    email: (0, mysql_core_1.varchar)({ length: 255 }),
    phone: (0, mysql_core_1.varchar)({ length: 255 }),
    website: (0, mysql_core_1.varchar)({ length: 255 }),
    image: (0, mysql_core_1.varchar)({ length: 255 }),
    description: (0, mysql_core_1.longtext)(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    category_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.categories.id; }, { onDelete: "cascade" }),
    company_name: (0, mysql_core_1.varchar)({ length: 255 }),
    status: (0, mysql_core_1.varchar)({ length: 255 }).default("active"),
    is_trusted_and_tested: (0, mysql_core_1.tinyint)().default(0).notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "suppliers_id" })]; });
exports.team_user = (0, mysql_core_1.mysqlTable)("team_user", {
    team_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.teams.id; }, { onDelete: "cascade" }),
    marketer_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true })
        .notNull()
        .references(function () { return exports.users.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
});
exports.teams = (0, mysql_core_1.mysqlTable)("teams", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    code: (0, mysql_core_1.varchar)({ length: 255 }),
    description: (0, mysql_core_1.text)(),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "teams_id" }),
    (0, mysql_core_1.unique)("teams_code_unique").on(table.code),
]; });
exports.timezones = (0, mysql_core_1.mysqlTable)("timezones", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    country_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "timezones_id" })]; });
exports.types = (0, mysql_core_1.mysqlTable)("types", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    name: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    price1: (0, mysql_core_1.bigint)({ mode: "number" }),
    price2: (0, mysql_core_1.bigint)({ mode: "number" }),
    deposit1: (0, mysql_core_1.bigint)({ mode: "number" }),
    deposit2: (0, mysql_core_1.bigint)({ mode: "number" }),
    weekend_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    weekend_start_day: (0, mysql_core_1.varchar)({ length: 255 }),
    weekend_end_day: (0, mysql_core_1.varchar)({ length: 255 }),
    promo_price: (0, mysql_core_1.bigint)({ mode: "number" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_start_day: (0, mysql_core_1.date)({ mode: "string" }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    promo_end_day: (0, mysql_core_1.date)({ mode: "string" }),
    remarks: (0, mysql_core_1.text)(),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "types_id" })]; });
exports.users = (0, mysql_core_1.mysqlTable)("users", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    firstname: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    department_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.departments.id; }, { onDelete: "set null" }),
    lastname: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    username: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    phone: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    email: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_name: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_avatar: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_phone: (0, mysql_core_1.varchar)({ length: 255 }),
    nok_email: (0, mysql_core_1.varchar)({ length: 255 }),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_birth: (0, mysql_core_1.date)({ mode: "string" }),
    can_login: (0, mysql_core_1.tinyint)().default(1).notNull(),
    // you can use { mode: 'date' }, if you want to have Date as type for this column
    date_of_employment: (0, mysql_core_1.date)({ mode: "string" }).notNull(),
    avatar: (0, mysql_core_1.varchar)({ length: 255 }),
    current_role: (0, mysql_core_1.varchar)({ length: 255 }),
    designation: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    gender: (0, mysql_core_1.varchar)({ length: 255 }),
    address: (0, mysql_core_1.text)(),
    account_number: (0, mysql_core_1.varchar)({ length: 255 }),
    bank_name: (0, mysql_core_1.varchar)({ length: 255 }),
    account_name: (0, mysql_core_1.varchar)({ length: 255 }),
    signature: (0, mysql_core_1.varchar)({ length: 255 }),
    gross_annual_salary: (0, mysql_core_1.bigint)({ mode: "number" }),
    payee: (0, mysql_core_1.bigint)({ mode: "number" }),
    surcharges: (0, mysql_core_1.bigint)({ mode: "number" }),
    special_bonus: (0, mysql_core_1.bigint)({ mode: "number" }),
    health_insurance: (0, mysql_core_1.bigint)({ mode: "number" }),
    transport_insurance: (0, mysql_core_1.bigint)({ mode: "number" }),
    housing_insurance: (0, mysql_core_1.bigint)({ mode: "number" }),
    overtime_insurance: (0, mysql_core_1.bigint)({ mode: "number" }),
    special_duty_insurance: (0, mysql_core_1.bigint)({ mode: "number" }),
    other_deductions: (0, mysql_core_1.bigint)({ mode: "number" }),
    city_id: (0, mysql_core_1.bigint)({ mode: "number" }),
    state_id: (0, mysql_core_1.bigint)({ mode: "number" }),
    country_id: (0, mysql_core_1.bigint)({ mode: "number" }),
    password: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    two_factor_secret: (0, mysql_core_1.text)(),
    two_factor_recovery_codes: (0, mysql_core_1.text)(),
    two_factor_confirmed_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    is_new: (0, mysql_core_1.tinyint)().default(1).notNull(),
    email_verified_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    remember_token: (0, mysql_core_1.varchar)({ length: 100 }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    otp: (0, mysql_core_1.varchar)({ length: 255 }),
    device_name: (0, mysql_core_1.varchar)({ length: 255 }).default("web"),
    location: (0, mysql_core_1.json)(),
    latitude: (0, mysql_core_1.decimal)({ precision: 10, scale: 8 }),
    longitude: (0, mysql_core_1.decimal)({ precision: 11, scale: 8 }),
}, function (table) { return [
    (0, mysql_core_1.primaryKey)({ columns: [table.id], name: "users_id" }),
    (0, mysql_core_1.unique)("users_email_unique").on(table.email),
    (0, mysql_core_1.unique)("users_phone_unique").on(table.phone),
    (0, mysql_core_1.unique)("users_username_unique").on(table.username),
]; });
exports.videos = (0, mysql_core_1.mysqlTable)("videos", {
    id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).autoincrement().notNull(),
    url: (0, mysql_core_1.varchar)({ length: 255 }).notNull(),
    type: (0, mysql_core_1.varchar)({ length: 255 }),
    hotel_id: (0, mysql_core_1.bigint)({ mode: "number", unsigned: true }).references(function () { return exports.hotels.id; }, { onDelete: "cascade" }),
    created_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
    updated_at: (0, mysql_core_1.timestamp)({ mode: "string" }),
}, function (table) { return [(0, mysql_core_1.primaryKey)({ columns: [table.id], name: "videos_id" })]; });
