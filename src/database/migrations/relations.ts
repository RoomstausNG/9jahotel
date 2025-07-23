import { relations } from "drizzle-orm/relations";
import { amenities, amenity_hotel, hotels, coupons, booking_invoices, customers, users, bookings, cities, businesses, categories, credit_payments, customer_hotel, countries, states, departments, expenditures, exports, imports, failed_import_rows, faqs, questions, favourites, finances, floors, group_bookings, group_booking_hotel, halls, types, hotel_galleries, hotel_user, powers, teams, images, invoices, general_items, items, revenues, permissions, model_has_permissions, roles, model_has_roles, orders, pages, payment_methods, profiles, ratings, refunds, role_has_permissions, rooms, sales, statuses, suppliers, team_user, transactions, videos } from "./schema";

export const amenitiesRelations = relations(amenities, ({one, many}) => ({
	amenity: one(amenities, {
		fields: [amenities.general_amenity_id],
		references: [amenities.id],
		relationName: "amenities_general_amenity_id_amenities_id"
	}),
	amenities: many(amenities, {
		relationName: "amenities_general_amenity_id_amenities_id"
	}),
	amenity_hotels: many(amenity_hotel),
}));

export const amenity_hotelRelations = relations(amenity_hotel, ({one}) => ({
	amenity: one(amenities, {
		fields: [amenity_hotel.amenity_id],
		references: [amenities.id]
	}),
	hotel: one(hotels, {
		fields: [amenity_hotel.hotel_id],
		references: [hotels.id]
	}),
}));

export const hotelsRelations = relations(hotels, ({one, many}) => ({
	amenity_hotels: many(amenity_hotel),
	booking_invoices: many(booking_invoices),
	bookings: many(bookings),
	businesses: many(businesses),
	categories: many(categories),
	coupons: many(coupons),
	credit_payments: many(credit_payments),
	customer_hotels: many(customer_hotel),
	customers: many(customers),
	departments: many(departments),
	expenditures: many(expenditures),
	faqs: many(faqs),
	favourites: many(favourites),
	finances: many(finances),
	floors: many(floors),
	group_booking_hotels: many(group_booking_hotel),
	halls: many(halls),
	hotel_galleries: many(hotel_galleries),
	hotel_users: many(hotel_user),
	city: one(cities, {
		fields: [hotels.city_id],
		references: [cities.id]
	}),
	state_country_id: one(states, {
		fields: [hotels.country_id],
		references: [states.id],
		relationName: "hotels_country_id_states_id"
	}),
	user: one(users, {
		fields: [hotels.created_by],
		references: [users.id],
		relationName: "hotels_created_by_users_id"
	}),
	power: one(powers, {
		fields: [hotels.power_id],
		references: [powers.id]
	}),
	state_state_id: one(states, {
		fields: [hotels.state_id],
		references: [states.id],
		relationName: "hotels_state_id_states_id"
	}),
	team: one(teams, {
		fields: [hotels.team_id],
		references: [teams.id]
	}),
	images: many(images),
	invoices: many(invoices),
	items: many(items),
	orders: many(orders),
	pages: many(pages),
	payment_methods: many(payment_methods),
	profiles: many(profiles),
	ratings: many(ratings),
	revenues: many(revenues),
	rooms: many(rooms),
	sales: many(sales),
	suppliers: many(suppliers),
	transactions: many(transactions),
	types: many(types),
	users: many(users, {
		relationName: "users_hotel_id_hotels_id"
	}),
	videos: many(videos),
}));

export const booking_invoicesRelations = relations(booking_invoices, ({one, many}) => ({
	coupon: one(coupons, {
		fields: [booking_invoices.coupon_id],
		references: [coupons.id]
	}),
	customer: one(customers, {
		fields: [booking_invoices.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [booking_invoices.hotel_id],
		references: [hotels.id]
	}),
	bookings: many(bookings),
	refunds: many(refunds),
	transactions: many(transactions),
}));

export const couponsRelations = relations(coupons, ({one, many}) => ({
	booking_invoices: many(booking_invoices),
	hotel: one(hotels, {
		fields: [coupons.hotel_id],
		references: [hotels.id]
	}),
	user: one(users, {
		fields: [coupons.owner_id],
		references: [users.id]
	}),
}));

export const customersRelations = relations(customers, ({one, many}) => ({
	booking_invoices: many(booking_invoices),
	bookings: many(bookings),
	customer_hotels: many(customer_hotel),
	business: one(businesses, {
		fields: [customers.business_id],
		references: [businesses.id]
	}),
	city: one(cities, {
		fields: [customers.city_id],
		references: [cities.id]
	}),
	country: one(countries, {
		fields: [customers.country_id],
		references: [countries.id]
	}),
	hotel: one(hotels, {
		fields: [customers.hotel_id],
		references: [hotels.id]
	}),
	state: one(states, {
		fields: [customers.state_id],
		references: [states.id]
	}),
	favourites: many(favourites),
	orders: many(orders),
	ratings: many(ratings),
	sales: many(sales),
}));

export const bookingsRelations = relations(bookings, ({one, many}) => ({
	user: one(users, {
		fields: [bookings.agent_id],
		references: [users.id]
	}),
	booking_invoice: one(booking_invoices, {
		fields: [bookings.booking_invoice_id],
		references: [booking_invoices.id]
	}),
	customer: one(customers, {
		fields: [bookings.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [bookings.hotel_id],
		references: [hotels.id]
	}),
	orders: many(orders),
	sales: many(sales),
	statuses: many(statuses),
}));

export const usersRelations = relations(users, ({one, many}) => ({
	bookings: many(bookings),
	coupons: many(coupons),
	departments: many(departments, {
		relationName: "departments_user_id_users_id"
	}),
	exports: many(exports),
	hotel_users: many(hotel_user),
	hotels: many(hotels, {
		relationName: "hotels_created_by_users_id"
	}),
	imports: many(imports),
	orders: many(orders),
	profiles: many(profiles),
	sales: many(sales),
	team_users: many(team_user),
	department: one(departments, {
		fields: [users.department_id],
		references: [departments.id],
		relationName: "users_department_id_departments_id"
	}),
	hotel: one(hotels, {
		fields: [users.hotel_id],
		references: [hotels.id],
		relationName: "users_hotel_id_hotels_id"
	}),
}));

export const businessesRelations = relations(businesses, ({one, many}) => ({
	city: one(cities, {
		fields: [businesses.city_id],
		references: [cities.id]
	}),
	hotel: one(hotels, {
		fields: [businesses.hotel_id],
		references: [hotels.id]
	}),
	credit_payments: many(credit_payments),
	customers: many(customers),
}));

export const citiesRelations = relations(cities, ({many}) => ({
	businesses: many(businesses),
	customers: many(customers),
	group_bookings: many(group_bookings),
	hotels: many(hotels),
}));

export const categoriesRelations = relations(categories, ({one, many}) => ({
	hotel: one(hotels, {
		fields: [categories.hotel_id],
		references: [hotels.id]
	}),
	suppliers: many(suppliers),
}));

export const credit_paymentsRelations = relations(credit_payments, ({one}) => ({
	business: one(businesses, {
		fields: [credit_payments.business_id],
		references: [businesses.id]
	}),
	hotel: one(hotels, {
		fields: [credit_payments.hotel_id],
		references: [hotels.id]
	}),
}));

export const customer_hotelRelations = relations(customer_hotel, ({one}) => ({
	customer: one(customers, {
		fields: [customer_hotel.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [customer_hotel.hotel_id],
		references: [hotels.id]
	}),
}));

export const countriesRelations = relations(countries, ({many}) => ({
	customers: many(customers),
}));

export const statesRelations = relations(states, ({many}) => ({
	customers: many(customers),
	hotels_country_id: many(hotels, {
		relationName: "hotels_country_id_states_id"
	}),
	hotels_state_id: many(hotels, {
		relationName: "hotels_state_id_states_id"
	}),
}));

export const departmentsRelations = relations(departments, ({one, many}) => ({
	hotel: one(hotels, {
		fields: [departments.hotel_id],
		references: [hotels.id]
	}),
	user: one(users, {
		fields: [departments.user_id],
		references: [users.id],
		relationName: "departments_user_id_users_id"
	}),
	users: many(users, {
		relationName: "users_department_id_departments_id"
	}),
}));

export const expendituresRelations = relations(expenditures, ({one}) => ({
	hotel: one(hotels, {
		fields: [expenditures.hotel_id],
		references: [hotels.id]
	}),
}));

export const exportsRelations = relations(exports, ({one}) => ({
	user: one(users, {
		fields: [exports.user_id],
		references: [users.id]
	}),
}));

export const failed_import_rowsRelations = relations(failed_import_rows, ({one}) => ({
	import: one(imports, {
		fields: [failed_import_rows.import_id],
		references: [imports.id]
	}),
}));

export const importsRelations = relations(imports, ({one, many}) => ({
	failed_import_rows: many(failed_import_rows),
	user: one(users, {
		fields: [imports.user_id],
		references: [users.id]
	}),
}));

export const faqsRelations = relations(faqs, ({one}) => ({
	hotel: one(hotels, {
		fields: [faqs.hotel_id],
		references: [hotels.id]
	}),
	question: one(questions, {
		fields: [faqs.question_id],
		references: [questions.id]
	}),
}));

export const questionsRelations = relations(questions, ({many}) => ({
	faqs: many(faqs),
}));

export const favouritesRelations = relations(favourites, ({one}) => ({
	customer: one(customers, {
		fields: [favourites.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [favourites.hotel_id],
		references: [hotels.id]
	}),
}));

export const financesRelations = relations(finances, ({one}) => ({
	hotel: one(hotels, {
		fields: [finances.hotel_id],
		references: [hotels.id]
	}),
}));

export const floorsRelations = relations(floors, ({one, many}) => ({
	hotel: one(hotels, {
		fields: [floors.hotel_id],
		references: [hotels.id]
	}),
	rooms: many(rooms),
}));

export const group_booking_hotelRelations = relations(group_booking_hotel, ({one}) => ({
	group_booking: one(group_bookings, {
		fields: [group_booking_hotel.group_booking_id],
		references: [group_bookings.id]
	}),
	hotel: one(hotels, {
		fields: [group_booking_hotel.hotel_id],
		references: [hotels.id]
	}),
}));

export const group_bookingsRelations = relations(group_bookings, ({one, many}) => ({
	group_booking_hotels: many(group_booking_hotel),
	city: one(cities, {
		fields: [group_bookings.city_id],
		references: [cities.id]
	}),
}));

export const hallsRelations = relations(halls, ({one, many}) => ({
	hotel: one(hotels, {
		fields: [halls.hotel_id],
		references: [hotels.id]
	}),
	type: one(types, {
		fields: [halls.type_id],
		references: [types.id]
	}),
	statuses: many(statuses),
}));

export const typesRelations = relations(types, ({one, many}) => ({
	halls: many(halls),
	rooms: many(rooms),
	hotel: one(hotels, {
		fields: [types.hotel_id],
		references: [hotels.id]
	}),
}));

export const hotel_galleriesRelations = relations(hotel_galleries, ({one}) => ({
	hotel: one(hotels, {
		fields: [hotel_galleries.hotel_id],
		references: [hotels.id]
	}),
}));

export const hotel_userRelations = relations(hotel_user, ({one}) => ({
	hotel: one(hotels, {
		fields: [hotel_user.hotel_id],
		references: [hotels.id]
	}),
	user: one(users, {
		fields: [hotel_user.user_id],
		references: [users.id]
	}),
}));

export const powersRelations = relations(powers, ({many}) => ({
	hotels: many(hotels),
}));

export const teamsRelations = relations(teams, ({many}) => ({
	hotels: many(hotels),
	team_users: many(team_user),
}));

export const imagesRelations = relations(images, ({one}) => ({
	hotel: one(hotels, {
		fields: [images.hotel_id],
		references: [hotels.id]
	}),
}));

export const invoicesRelations = relations(invoices, ({one}) => ({
	hotel: one(hotels, {
		fields: [invoices.hotel_id],
		references: [hotels.id]
	}),
}));

export const itemsRelations = relations(items, ({one, many}) => ({
	general_item: one(general_items, {
		fields: [items.general_item_id],
		references: [general_items.id]
	}),
	hotel: one(hotels, {
		fields: [items.hotel_id],
		references: [hotels.id]
	}),
	revenue: one(revenues, {
		fields: [items.revenue_id],
		references: [revenues.id]
	}),
	orders: many(orders),
}));

export const general_itemsRelations = relations(general_items, ({many}) => ({
	items: many(items),
}));

export const revenuesRelations = relations(revenues, ({one, many}) => ({
	items: many(items),
	hotel: one(hotels, {
		fields: [revenues.hotel_id],
		references: [hotels.id]
	}),
}));

export const model_has_permissionsRelations = relations(model_has_permissions, ({one}) => ({
	permission: one(permissions, {
		fields: [model_has_permissions.permission_id],
		references: [permissions.id]
	}),
}));

export const permissionsRelations = relations(permissions, ({many}) => ({
	model_has_permissions: many(model_has_permissions),
	role_has_permissions: many(role_has_permissions),
}));

export const model_has_rolesRelations = relations(model_has_roles, ({one}) => ({
	role: one(roles, {
		fields: [model_has_roles.role_id],
		references: [roles.id]
	}),
}));

export const rolesRelations = relations(roles, ({many}) => ({
	model_has_roles: many(model_has_roles),
	role_has_permissions: many(role_has_permissions),
}));

export const ordersRelations = relations(orders, ({one}) => ({
	user: one(users, {
		fields: [orders.agent_id],
		references: [users.id]
	}),
	booking: one(bookings, {
		fields: [orders.booking_id],
		references: [bookings.id]
	}),
	customer: one(customers, {
		fields: [orders.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [orders.hotel_id],
		references: [hotels.id]
	}),
	item: one(items, {
		fields: [orders.item_id],
		references: [items.id]
	}),
}));

export const pagesRelations = relations(pages, ({one}) => ({
	hotel: one(hotels, {
		fields: [pages.hotel_id],
		references: [hotels.id]
	}),
}));

export const payment_methodsRelations = relations(payment_methods, ({one}) => ({
	hotel: one(hotels, {
		fields: [payment_methods.hotel_id],
		references: [hotels.id]
	}),
}));

export const profilesRelations = relations(profiles, ({one}) => ({
	hotel: one(hotels, {
		fields: [profiles.hotel_id],
		references: [hotels.id]
	}),
	user: one(users, {
		fields: [profiles.user_id],
		references: [users.id]
	}),
}));

export const ratingsRelations = relations(ratings, ({one}) => ({
	customer: one(customers, {
		fields: [ratings.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [ratings.hotel_id],
		references: [hotels.id]
	}),
}));

export const refundsRelations = relations(refunds, ({one}) => ({
	booking_invoice: one(booking_invoices, {
		fields: [refunds.booking_invoice_id],
		references: [booking_invoices.id]
	}),
}));

export const role_has_permissionsRelations = relations(role_has_permissions, ({one}) => ({
	permission: one(permissions, {
		fields: [role_has_permissions.permission_id],
		references: [permissions.id]
	}),
	role: one(roles, {
		fields: [role_has_permissions.role_id],
		references: [roles.id]
	}),
}));

export const roomsRelations = relations(rooms, ({one, many}) => ({
	floor: one(floors, {
		fields: [rooms.floor_id],
		references: [floors.id]
	}),
	hotel: one(hotels, {
		fields: [rooms.hotel_id],
		references: [hotels.id]
	}),
	type: one(types, {
		fields: [rooms.type_id],
		references: [types.id]
	}),
	statuses: many(statuses),
}));

export const salesRelations = relations(sales, ({one}) => ({
	user: one(users, {
		fields: [sales.agent_id],
		references: [users.id]
	}),
	booking: one(bookings, {
		fields: [sales.booking_id],
		references: [bookings.id]
	}),
	customer: one(customers, {
		fields: [sales.customer_id],
		references: [customers.id]
	}),
	hotel: one(hotels, {
		fields: [sales.hotel_id],
		references: [hotels.id]
	}),
}));

export const statusesRelations = relations(statuses, ({one}) => ({
	booking: one(bookings, {
		fields: [statuses.booking_id],
		references: [bookings.id]
	}),
	hall: one(halls, {
		fields: [statuses.hall_id],
		references: [halls.id]
	}),
	room: one(rooms, {
		fields: [statuses.room_id],
		references: [rooms.id]
	}),
}));

export const suppliersRelations = relations(suppliers, ({one}) => ({
	category: one(categories, {
		fields: [suppliers.category_id],
		references: [categories.id]
	}),
	hotel: one(hotels, {
		fields: [suppliers.hotel_id],
		references: [hotels.id]
	}),
}));

export const team_userRelations = relations(team_user, ({one}) => ({
	user: one(users, {
		fields: [team_user.marketer_id],
		references: [users.id]
	}),
	team: one(teams, {
		fields: [team_user.team_id],
		references: [teams.id]
	}),
}));

export const transactionsRelations = relations(transactions, ({one}) => ({
	booking_invoice: one(booking_invoices, {
		fields: [transactions.booking_invoice_id],
		references: [booking_invoices.id]
	}),
	hotel: one(hotels, {
		fields: [transactions.hotel_id],
		references: [hotels.id]
	}),
}));

export const videosRelations = relations(videos, ({one}) => ({
	hotel: one(hotels, {
		fields: [videos.hotel_id],
		references: [hotels.id]
	}),
}));