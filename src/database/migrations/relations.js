"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.videosRelations = exports.team_userRelations = exports.suppliersRelations = exports.statusesRelations = exports.salesRelations = exports.roomsRelations = exports.role_has_permissionsRelations = exports.ratingsRelations = exports.profilesRelations = exports.ordersRelations = exports.rolesRelations = exports.model_has_rolesRelations = exports.permissionsRelations = exports.model_has_permissionsRelations = exports.revenuesRelations = exports.general_itemsRelations = exports.itemsRelations = exports.invoicesRelations = exports.imagesRelations = exports.teamsRelations = exports.powersRelations = exports.hotel_userRelations = exports.hotel_galleriesRelations = exports.typesRelations = exports.hallsRelations = exports.group_bookingsRelations = exports.group_booking_hotelRelations = exports.floorsRelations = exports.financesRelations = exports.favouritesRelations = exports.questionsRelations = exports.faqsRelations = exports.expendituresRelations = exports.departmentsRelations = exports.statesRelations = exports.countriesRelations = exports.customer_hotelRelations = exports.credit_paymentsRelations = exports.categoriesRelations = exports.citiesRelations = exports.businessesRelations = exports.usersRelations = exports.bookingsRelations = exports.customersRelations = exports.booking_invoicesRelations = exports.hotelsRelations = exports.amenity_hotelRelations = exports.amenitiesRelations = void 0;
var relations_1 = require("drizzle-orm/relations");
var schema_1 = require("./schema");
exports.amenitiesRelations = (0, relations_1.relations)(schema_1.amenities, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        amenity: one(schema_1.amenities, {
            fields: [schema_1.amenities.general_amenity_id],
            references: [schema_1.amenities.id],
            relationName: "amenities_general_amenity_id_amenities_id"
        }),
        amenities: many(schema_1.amenities, {
            relationName: "amenities_general_amenity_id_amenities_id"
        }),
        amenity_hotels: many(schema_1.amenity_hotel),
    });
});
exports.amenity_hotelRelations = (0, relations_1.relations)(schema_1.amenity_hotel, function (_a) {
    var one = _a.one;
    return ({
        amenity: one(schema_1.amenities, {
            fields: [schema_1.amenity_hotel.amenity_id],
            references: [schema_1.amenities.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.amenity_hotel.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.hotelsRelations = (0, relations_1.relations)(schema_1.hotels, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        amenity_hotels: many(schema_1.amenity_hotel),
        booking_invoices: many(schema_1.booking_invoices),
        bookings: many(schema_1.bookings),
        businesses: many(schema_1.businesses),
        categories: many(schema_1.categories),
        credit_payments: many(schema_1.credit_payments),
        customer_hotels: many(schema_1.customer_hotel),
        customers: many(schema_1.customers),
        departments: many(schema_1.departments),
        expenditures: many(schema_1.expenditures),
        faqs: many(schema_1.faqs),
        favourites: many(schema_1.favourites),
        finances: many(schema_1.finances),
        floors: many(schema_1.floors),
        group_booking_hotels: many(schema_1.group_booking_hotel),
        halls: many(schema_1.halls),
        hotel_galleries: many(schema_1.hotel_galleries),
        hotel_users: many(schema_1.hotel_user),
        city: one(schema_1.cities, {
            fields: [schema_1.hotels.city_id],
            references: [schema_1.cities.id]
        }),
        state_country_id: one(schema_1.states, {
            fields: [schema_1.hotels.country_id],
            references: [schema_1.states.id],
            relationName: "hotels_country_id_states_id"
        }),
        user: one(schema_1.users, {
            fields: [schema_1.hotels.created_by],
            references: [schema_1.users.id],
            relationName: "hotels_created_by_users_id"
        }),
        power: one(schema_1.powers, {
            fields: [schema_1.hotels.power_id],
            references: [schema_1.powers.id]
        }),
        state_state_id: one(schema_1.states, {
            fields: [schema_1.hotels.state_id],
            references: [schema_1.states.id],
            relationName: "hotels_state_id_states_id"
        }),
        team: one(schema_1.teams, {
            fields: [schema_1.hotels.team_id],
            references: [schema_1.teams.id]
        }),
        images: many(schema_1.images),
        invoices: many(schema_1.invoices),
        items: many(schema_1.items),
        orders: many(schema_1.orders),
        profiles: many(schema_1.profiles),
        ratings: many(schema_1.ratings),
        revenues: many(schema_1.revenues),
        rooms: many(schema_1.rooms),
        sales: many(schema_1.sales),
        suppliers: many(schema_1.suppliers),
        types: many(schema_1.types),
        users: many(schema_1.users, {
            relationName: "users_hotel_id_hotels_id"
        }),
        videos: many(schema_1.videos),
    });
});
exports.booking_invoicesRelations = (0, relations_1.relations)(schema_1.booking_invoices, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        customer: one(schema_1.customers, {
            fields: [schema_1.booking_invoices.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.booking_invoices.hotel_id],
            references: [schema_1.hotels.id]
        }),
        bookings: many(schema_1.bookings),
    });
});
exports.customersRelations = (0, relations_1.relations)(schema_1.customers, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        booking_invoices: many(schema_1.booking_invoices),
        bookings: many(schema_1.bookings),
        customer_hotels: many(schema_1.customer_hotel),
        business: one(schema_1.businesses, {
            fields: [schema_1.customers.business_id],
            references: [schema_1.businesses.id]
        }),
        city: one(schema_1.cities, {
            fields: [schema_1.customers.city_id],
            references: [schema_1.cities.id]
        }),
        country: one(schema_1.countries, {
            fields: [schema_1.customers.country_id],
            references: [schema_1.countries.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.customers.hotel_id],
            references: [schema_1.hotels.id]
        }),
        state: one(schema_1.states, {
            fields: [schema_1.customers.state_id],
            references: [schema_1.states.id]
        }),
        favourites: many(schema_1.favourites),
        orders: many(schema_1.orders),
        ratings: many(schema_1.ratings),
        sales: many(schema_1.sales),
    });
});
exports.bookingsRelations = (0, relations_1.relations)(schema_1.bookings, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        user: one(schema_1.users, {
            fields: [schema_1.bookings.agent_id],
            references: [schema_1.users.id]
        }),
        booking_invoice: one(schema_1.booking_invoices, {
            fields: [schema_1.bookings.booking_invoice_id],
            references: [schema_1.booking_invoices.id]
        }),
        customer: one(schema_1.customers, {
            fields: [schema_1.bookings.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.bookings.hotel_id],
            references: [schema_1.hotels.id]
        }),
        orders: many(schema_1.orders),
        sales: many(schema_1.sales),
        statuses: many(schema_1.statuses),
    });
});
exports.usersRelations = (0, relations_1.relations)(schema_1.users, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        bookings: many(schema_1.bookings),
        departments: many(schema_1.departments, {
            relationName: "departments_user_id_users_id"
        }),
        hotel_users: many(schema_1.hotel_user),
        hotels: many(schema_1.hotels, {
            relationName: "hotels_created_by_users_id"
        }),
        orders: many(schema_1.orders),
        profiles: many(schema_1.profiles),
        sales: many(schema_1.sales),
        team_users: many(schema_1.team_user),
        department: one(schema_1.departments, {
            fields: [schema_1.users.department_id],
            references: [schema_1.departments.id],
            relationName: "users_department_id_departments_id"
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.users.hotel_id],
            references: [schema_1.hotels.id],
            relationName: "users_hotel_id_hotels_id"
        }),
    });
});
exports.businessesRelations = (0, relations_1.relations)(schema_1.businesses, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        city: one(schema_1.cities, {
            fields: [schema_1.businesses.city_id],
            references: [schema_1.cities.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.businesses.hotel_id],
            references: [schema_1.hotels.id]
        }),
        credit_payments: many(schema_1.credit_payments),
        customers: many(schema_1.customers),
    });
});
exports.citiesRelations = (0, relations_1.relations)(schema_1.cities, function (_a) {
    var many = _a.many;
    return ({
        businesses: many(schema_1.businesses),
        customers: many(schema_1.customers),
        group_bookings: many(schema_1.group_bookings),
        hotels: many(schema_1.hotels),
    });
});
exports.categoriesRelations = (0, relations_1.relations)(schema_1.categories, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.categories.hotel_id],
            references: [schema_1.hotels.id]
        }),
        suppliers: many(schema_1.suppliers),
    });
});
exports.credit_paymentsRelations = (0, relations_1.relations)(schema_1.credit_payments, function (_a) {
    var one = _a.one;
    return ({
        business: one(schema_1.businesses, {
            fields: [schema_1.credit_payments.business_id],
            references: [schema_1.businesses.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.credit_payments.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.customer_hotelRelations = (0, relations_1.relations)(schema_1.customer_hotel, function (_a) {
    var one = _a.one;
    return ({
        customer: one(schema_1.customers, {
            fields: [schema_1.customer_hotel.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.customer_hotel.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.countriesRelations = (0, relations_1.relations)(schema_1.countries, function (_a) {
    var many = _a.many;
    return ({
        customers: many(schema_1.customers),
    });
});
exports.statesRelations = (0, relations_1.relations)(schema_1.states, function (_a) {
    var many = _a.many;
    return ({
        customers: many(schema_1.customers),
        hotels_country_id: many(schema_1.hotels, {
            relationName: "hotels_country_id_states_id"
        }),
        hotels_state_id: many(schema_1.hotels, {
            relationName: "hotels_state_id_states_id"
        }),
    });
});
exports.departmentsRelations = (0, relations_1.relations)(schema_1.departments, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.departments.hotel_id],
            references: [schema_1.hotels.id]
        }),
        user: one(schema_1.users, {
            fields: [schema_1.departments.user_id],
            references: [schema_1.users.id],
            relationName: "departments_user_id_users_id"
        }),
        users: many(schema_1.users, {
            relationName: "users_department_id_departments_id"
        }),
    });
});
exports.expendituresRelations = (0, relations_1.relations)(schema_1.expenditures, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.expenditures.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.faqsRelations = (0, relations_1.relations)(schema_1.faqs, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.faqs.hotel_id],
            references: [schema_1.hotels.id]
        }),
        question: one(schema_1.questions, {
            fields: [schema_1.faqs.question_id],
            references: [schema_1.questions.id]
        }),
    });
});
exports.questionsRelations = (0, relations_1.relations)(schema_1.questions, function (_a) {
    var many = _a.many;
    return ({
        faqs: many(schema_1.faqs),
    });
});
exports.favouritesRelations = (0, relations_1.relations)(schema_1.favourites, function (_a) {
    var one = _a.one;
    return ({
        customer: one(schema_1.customers, {
            fields: [schema_1.favourites.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.favourites.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.financesRelations = (0, relations_1.relations)(schema_1.finances, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.finances.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.floorsRelations = (0, relations_1.relations)(schema_1.floors, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.floors.hotel_id],
            references: [schema_1.hotels.id]
        }),
        rooms: many(schema_1.rooms),
    });
});
exports.group_booking_hotelRelations = (0, relations_1.relations)(schema_1.group_booking_hotel, function (_a) {
    var one = _a.one;
    return ({
        group_booking: one(schema_1.group_bookings, {
            fields: [schema_1.group_booking_hotel.group_booking_id],
            references: [schema_1.group_bookings.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.group_booking_hotel.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.group_bookingsRelations = (0, relations_1.relations)(schema_1.group_bookings, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        group_booking_hotels: many(schema_1.group_booking_hotel),
        city: one(schema_1.cities, {
            fields: [schema_1.group_bookings.city_id],
            references: [schema_1.cities.id]
        }),
    });
});
exports.hallsRelations = (0, relations_1.relations)(schema_1.halls, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.halls.hotel_id],
            references: [schema_1.hotels.id]
        }),
        type: one(schema_1.types, {
            fields: [schema_1.halls.type_id],
            references: [schema_1.types.id]
        }),
        statuses: many(schema_1.statuses),
    });
});
exports.typesRelations = (0, relations_1.relations)(schema_1.types, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        halls: many(schema_1.halls),
        rooms: many(schema_1.rooms),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.types.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.hotel_galleriesRelations = (0, relations_1.relations)(schema_1.hotel_galleries, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.hotel_galleries.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.hotel_userRelations = (0, relations_1.relations)(schema_1.hotel_user, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.hotel_user.hotel_id],
            references: [schema_1.hotels.id]
        }),
        user: one(schema_1.users, {
            fields: [schema_1.hotel_user.user_id],
            references: [schema_1.users.id]
        }),
    });
});
exports.powersRelations = (0, relations_1.relations)(schema_1.powers, function (_a) {
    var many = _a.many;
    return ({
        hotels: many(schema_1.hotels),
    });
});
exports.teamsRelations = (0, relations_1.relations)(schema_1.teams, function (_a) {
    var many = _a.many;
    return ({
        hotels: many(schema_1.hotels),
        team_users: many(schema_1.team_user),
    });
});
exports.imagesRelations = (0, relations_1.relations)(schema_1.images, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.images.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.invoicesRelations = (0, relations_1.relations)(schema_1.invoices, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.invoices.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.itemsRelations = (0, relations_1.relations)(schema_1.items, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        general_item: one(schema_1.general_items, {
            fields: [schema_1.items.general_item_id],
            references: [schema_1.general_items.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.items.hotel_id],
            references: [schema_1.hotels.id]
        }),
        revenue: one(schema_1.revenues, {
            fields: [schema_1.items.revenue_id],
            references: [schema_1.revenues.id]
        }),
        orders: many(schema_1.orders),
    });
});
exports.general_itemsRelations = (0, relations_1.relations)(schema_1.general_items, function (_a) {
    var many = _a.many;
    return ({
        items: many(schema_1.items),
    });
});
exports.revenuesRelations = (0, relations_1.relations)(schema_1.revenues, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        items: many(schema_1.items),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.revenues.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.model_has_permissionsRelations = (0, relations_1.relations)(schema_1.model_has_permissions, function (_a) {
    var one = _a.one;
    return ({
        permission: one(schema_1.permissions, {
            fields: [schema_1.model_has_permissions.permission_id],
            references: [schema_1.permissions.id]
        }),
    });
});
exports.permissionsRelations = (0, relations_1.relations)(schema_1.permissions, function (_a) {
    var many = _a.many;
    return ({
        model_has_permissions: many(schema_1.model_has_permissions),
        role_has_permissions: many(schema_1.role_has_permissions),
    });
});
exports.model_has_rolesRelations = (0, relations_1.relations)(schema_1.model_has_roles, function (_a) {
    var one = _a.one;
    return ({
        role: one(schema_1.roles, {
            fields: [schema_1.model_has_roles.role_id],
            references: [schema_1.roles.id]
        }),
    });
});
exports.rolesRelations = (0, relations_1.relations)(schema_1.roles, function (_a) {
    var many = _a.many;
    return ({
        model_has_roles: many(schema_1.model_has_roles),
        role_has_permissions: many(schema_1.role_has_permissions),
    });
});
exports.ordersRelations = (0, relations_1.relations)(schema_1.orders, function (_a) {
    var one = _a.one;
    return ({
        user: one(schema_1.users, {
            fields: [schema_1.orders.agent_id],
            references: [schema_1.users.id]
        }),
        booking: one(schema_1.bookings, {
            fields: [schema_1.orders.booking_id],
            references: [schema_1.bookings.id]
        }),
        customer: one(schema_1.customers, {
            fields: [schema_1.orders.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.orders.hotel_id],
            references: [schema_1.hotels.id]
        }),
        item: one(schema_1.items, {
            fields: [schema_1.orders.item_id],
            references: [schema_1.items.id]
        }),
    });
});
exports.profilesRelations = (0, relations_1.relations)(schema_1.profiles, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.profiles.hotel_id],
            references: [schema_1.hotels.id]
        }),
        user: one(schema_1.users, {
            fields: [schema_1.profiles.user_id],
            references: [schema_1.users.id]
        }),
    });
});
exports.ratingsRelations = (0, relations_1.relations)(schema_1.ratings, function (_a) {
    var one = _a.one;
    return ({
        customer: one(schema_1.customers, {
            fields: [schema_1.ratings.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.ratings.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.role_has_permissionsRelations = (0, relations_1.relations)(schema_1.role_has_permissions, function (_a) {
    var one = _a.one;
    return ({
        permission: one(schema_1.permissions, {
            fields: [schema_1.role_has_permissions.permission_id],
            references: [schema_1.permissions.id]
        }),
        role: one(schema_1.roles, {
            fields: [schema_1.role_has_permissions.role_id],
            references: [schema_1.roles.id]
        }),
    });
});
exports.roomsRelations = (0, relations_1.relations)(schema_1.rooms, function (_a) {
    var one = _a.one, many = _a.many;
    return ({
        floor: one(schema_1.floors, {
            fields: [schema_1.rooms.floor_id],
            references: [schema_1.floors.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.rooms.hotel_id],
            references: [schema_1.hotels.id]
        }),
        type: one(schema_1.types, {
            fields: [schema_1.rooms.type_id],
            references: [schema_1.types.id]
        }),
        statuses: many(schema_1.statuses),
    });
});
exports.salesRelations = (0, relations_1.relations)(schema_1.sales, function (_a) {
    var one = _a.one;
    return ({
        user: one(schema_1.users, {
            fields: [schema_1.sales.agent_id],
            references: [schema_1.users.id]
        }),
        booking: one(schema_1.bookings, {
            fields: [schema_1.sales.booking_id],
            references: [schema_1.bookings.id]
        }),
        customer: one(schema_1.customers, {
            fields: [schema_1.sales.customer_id],
            references: [schema_1.customers.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.sales.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.statusesRelations = (0, relations_1.relations)(schema_1.statuses, function (_a) {
    var one = _a.one;
    return ({
        booking: one(schema_1.bookings, {
            fields: [schema_1.statuses.booking_id],
            references: [schema_1.bookings.id]
        }),
        hall: one(schema_1.halls, {
            fields: [schema_1.statuses.hall_id],
            references: [schema_1.halls.id]
        }),
        room: one(schema_1.rooms, {
            fields: [schema_1.statuses.room_id],
            references: [schema_1.rooms.id]
        }),
    });
});
exports.suppliersRelations = (0, relations_1.relations)(schema_1.suppliers, function (_a) {
    var one = _a.one;
    return ({
        category: one(schema_1.categories, {
            fields: [schema_1.suppliers.category_id],
            references: [schema_1.categories.id]
        }),
        hotel: one(schema_1.hotels, {
            fields: [schema_1.suppliers.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
exports.team_userRelations = (0, relations_1.relations)(schema_1.team_user, function (_a) {
    var one = _a.one;
    return ({
        user: one(schema_1.users, {
            fields: [schema_1.team_user.marketer_id],
            references: [schema_1.users.id]
        }),
        team: one(schema_1.teams, {
            fields: [schema_1.team_user.team_id],
            references: [schema_1.teams.id]
        }),
    });
});
exports.videosRelations = (0, relations_1.relations)(schema_1.videos, function (_a) {
    var one = _a.one;
    return ({
        hotel: one(schema_1.hotels, {
            fields: [schema_1.videos.hotel_id],
            references: [schema_1.hotels.id]
        }),
    });
});
