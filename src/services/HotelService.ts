import { injectable } from "inversify";
import {
  hotels,
  favourites,
  cities,
  ratings,
  images,
  customers,
  amenities,
  amenity_hotel,
  types,
  rooms, // Ensure rooms is imported
  bookings,
} from "../database/migrations/schema";
import { eq, and, sql, not, notInArray, isNotNull } from "drizzle-orm";
import { db } from "../config/database";
import { ApiResponse } from "../utils/ApiResponse";
import { HotelResource } from "../resources/HotelResource";
import { redis } from "../config/redis";
import { Context } from "hono";
import { buildReviewSummary } from "../utils/reviewSummary";
import { RoomResource } from "../resources/RoomResource";
import { getCloudinaryImage, getFullImageUrl } from "../utils/cloudinaryUtils";

@injectable()
export class HotelService {
  private readonly CACHE_EXPIRATION_SECONDS = 3600; // Cache for 1 hour

  private getListHotelsCacheKey(
    cityId: number,
    page: number,
    perPage: number,
    searchKey: string = ""
  ) {
    return `hotels:city:${cityId}:page:${page}:limit:${perPage}:search:${searchKey}`;
  }

  private async invalidateCityHotelCache(cityId: number): Promise<void> {
    const pattern = `hotels:city:${cityId}:*`;
    const keys = await redis.keys(pattern);
    if (keys.length > 0) {
      await redis.del(keys);
      console.log(`Invalidated cache for city ${cityId}: ${keys.join(", ")}`);
    }
  }

  async listHotelsByCity(
    cityId: number,
    checkIn: string,
    checkOut: string,
    page: number,
    perPage: number,
    search?: string
  ) {
    const searchKey = search?.toLowerCase().trim() || "";
    const cacheKey = this.getListHotelsCacheKey(
      cityId,
      page,
      perPage,
      searchKey
    );

    try {
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting cache:", error);
    }

    const offset = (page - 1) * perPage;

    let conditions = [eq(hotels.city_id, cityId), eq(hotels.status, "active")];

    conditions.push(sql`
    EXISTS (
      SELECT 1 FROM rooms
      LEFT JOIN bookings
        ON rooms.id = bookings.bookable_id
       AND bookings.bookable_type = 'App\\Models\\Room'
      WHERE rooms.hotel_id = ${hotels.id}
        AND rooms.status NOT IN ('occupied', 'repair')
        AND NOT EXISTS (
          SELECT 1 FROM bookings
          WHERE bookings.bookable_id = rooms.id
            AND bookings.bookable_type = 'App\\Models\\Room'
            AND bookings.status IN ('paid', 'unpaid', 'checkedin', 'checkedout')
            AND bookings.checked_in < ${checkOut}
            AND bookings.checked_out > ${checkIn}
        )
    )
  `);

    if (searchKey) {
      conditions.push(
        sql`LOWER(${hotels.name}) LIKE LOWER(${`%${searchKey}%`})`
      );
    }

    let query = db
      .select({
        id: hotels.id,
        name: hotels.name,
        image: hotels.background_image,
        code: hotels.code,
        address: hotels.address,
        latitude: hotels.latitude,
        longitude: hotels.longitude,
        city: {
          id: cities.id,
          name: cities.name,
        },
        roomPrice: hotels.room_lowest_price,
        hallPrice: hotels.hall_lowest_price,
        onlinePercentage: hotels.online_price,
        nonRefundablePercentage: hotels.non_refundable_price || 0,
        status: hotels.status,
        reviewCount: sql<number>`COUNT(ratings.id)`.as("reviewCount"),
        averageRating: sql<number>`AVG(ratings.number)`.as("averageRating"),
        starRating: hotels.star_rating,
        isFavourite: sql<boolean>`false`.as("isFavourite"),
      })
      .from(hotels)
      .innerJoin(cities, eq(hotels.city_id, cities.id))
      .leftJoin(ratings, eq(hotels.id, ratings.hotel_id))
      .where(and(...conditions))
      .groupBy(
        hotels.id,
        hotels.name,
        hotels.background_image,
        hotels.code,
        hotels.address,
        hotels.latitude,
        hotels.longitude,
        hotels.room_lowest_price,
        hotels.hall_lowest_price,
        hotels.status,
        hotels.star_rating,
        cities.id,
        cities.name
      )
      .orderBy(hotels.name)
      .limit(perPage)
      .offset(offset);

    const results = await query;

    if (results.length === 0) {
      return {
        hotels: [],
        meta: {
          pagination: {
            total: 0,
            count: 0,
            per_page: perPage,
            current_page: page,
            total_pages: 0,
            has_more_pages: false,
            first_page: true,
            last_page: true,
            next_page: null,
            prev_page: null,
          },
        },
      };
    }

    const countConditions = [...conditions];
    let countQuery = db
      .select({ count: sql<number>`COUNT(*)` })
      .from(hotels)
      .where(and(...countConditions));

    const total = await countQuery;

    const responseData = HotelResource.toCollection(
      results,
      total[0].count,
      page,
      perPage
    );

    try {
      await redis.set(
        cacheKey,
        JSON.stringify(responseData),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return responseData;
  }

  // get single hotels
  async getHotelDetails(hotelId: string, customerId?: number) {
    const cacheKey = `hotelDetails:${hotelId}:${customerId || "guest"}`;

    try {
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`No cahced data: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting cache:", error);
    }

    // Query for core hotel details only
    const hotelQuery = db
      .select({
        id: hotels.id,
        name: hotels.name,
        code: hotels.code,
        onlinePercentage: hotels.online_price,
        nonRefundablePercentage: hotels.non_refundable_price,
        status: hotels.status,
        image: hotels.background_image, // Main background image
        description: hotels.description,
        address: hotels.address,
        latitude: hotels.latitude,
        longitude: hotels.longitude,
        city: {
          id: cities.id,
          name: cities.name,
        },
        roomPrice: hotels.room_lowest_price,
        hallPrice: hotels.hall_lowest_price,
        starRating: hotels.star_rating,
        // Removed distance, reviewCount, averageRating, images aggregation from here
      })
      .from(hotels)
      .innerJoin(cities, eq(hotels.city_id, cities.id))
      // Removed joins for ratings, customers, images from this main query
      .where(eq(hotels.code, hotelId)) // Assuming you want to find by code based on hotelId being string
      .limit(1);

    // Execute query
    const hotelResult = await hotelQuery; // Drizzle returns array

    if (!hotelResult || hotelResult.length === 0) {
      // Consider returning ApiResponse.failure("Hotel not found", 404)
      return {
        hotel: null,
      };
    }

    const hotelDetails = hotelResult[0];

    // --- Fetch Images Separately ---
    let imageUrls: string[] = [];
    try {
      const imagesQuery = db
        .select({ url: images.url })
        .from(images)
        .where(
          and(
            eq(images.imageable_type, "App\\Models\\Hotel"), // Ensure this type string is correct
            eq(images.imageable_id, hotelDetails.id)
          )
        );
      const fetchedImages = await imagesQuery;
      imageUrls = fetchedImages
        .map((img) => img.url)
        .filter((url): url is string => url !== null); // Get non-null URLs
    } catch (error) {
      console.error("Error fetching hotel images:", error);
      // Proceed without images or handle error
    }
    // --- End Fetch Images ---

    // Query for amenities separately
    const amenitiesQuery = db
      .select({
        // Select specific columns needed
        name: amenities.name,
        icon: amenities.icon,
      })
      .from(amenity_hotel)
      .innerJoin(amenities, eq(amenities.id, amenity_hotel.amenity_id))
      .where(eq(amenity_hotel.hotel_id, hotelDetails.id)); // Filter by hotel ID

    let formattedAmenities: { name: string | null; icon: string | null }[] = [];
    try {
      const fetchedAmenitiesRaw = await amenitiesQuery;
      // Map directly if select is specific, otherwise map from raw result
      formattedAmenities = fetchedAmenitiesRaw.map((item) => ({
        name: item.name,
        icon: item.icon,
      }));
    } catch (error) {
      console.error("Error fetching amenities:", error);
    }

    // Query for ratings and reviews separately
    const ratingsQuery = db
      .select({
        id: ratings.id,
        customer: sql`
        JSON_OBJECT('id', customers.id, 'name', CONCAT(customers.firstName, ' ', customers.lastName))
      `, // Ensure firstName/lastName exist
        star: ratings.number,
        date: ratings.created_at,
        review: ratings.body, // Ensure body exists
      })
      .from(ratings)
      .leftJoin(customers, eq(ratings.customer_id, customers.id))
      .where(eq(ratings.hotel_id, hotelDetails.id));

    let reviews: any[] = [];
    try {
      reviews = await ratingsQuery;
    } catch (error) {
      console.error("Error fetching reviews:", error);
    }

    // If a customerId is provided, check if the hotel is a favourite
    let isFavourite = false;
    if (customerId) {
      try {
        const favouriteQuery = db
          .select({ count: sql<number>`COUNT(*)` }) // Just need to know if count > 0
          .from(favourites)
          .where(
            and(
              eq(favourites.hotel_id, hotelDetails.id),
              eq(favourites.customer_id, customerId)
            )
          );

        const favouriteResult = await favouriteQuery;
        isFavourite = (favouriteResult[0]?.count ?? 0) > 0;
      } catch (error) {
        console.error("Error checking favourite status:", error);
      }
    }

    // Add review count and average rating back to hotelDetails object before passing to resource
    // These are now calculated from the separate 'reviews' query result
    (hotelDetails as any).reviewCount = reviews.length;
    (hotelDetails as any).averageRating =
      reviews.length > 0
        ? reviews.reduce((sum, r) => sum + (r.star || 0), 0) / reviews.length
        : 0;
    (hotelDetails as any).reviews = reviews; // Attach the detailed list
    (hotelDetails as any).imageUrls = imageUrls; // Attach the fetched image URLs

    const reviewsSummary = buildReviewSummary(reviews);

    // Combine all the data - Pass amenities and image URLs if needed by resource
    const response = HotelResource.toDetail(
      hotelDetails, // Now includes reviewCount, averageRating, reviews list, imageUrls
      reviewsSummary,
      isFavourite,
      formattedAmenities // Pass fetched amenities
    );

    try {
      await redis.set(
        cacheKey,
        JSON.stringify(response),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return response;
  }

  // Modify signature to accept checkIn and checkOut dates
  async getHotelTypesByCode(
    hotelCode: string,
    checkIn: string,
    checkOut: string
  ) {
    const hotelResult = await db
      .select({ id: hotels.id })
      .from(hotels)
      .where(eq(hotels.code, hotelCode))
      .limit(1);

    if (!hotelResult || hotelResult.length === 0) {
      throw new Error("Hotel not found");
    }

    const hotelId = hotelResult[0].id;

    // Remove the separate bookedRoomsSubquery

    // Fetch types and count available rooms using logic similar to listHotelsByCity
    const typesWithAvailabilityRaw = await db
      .select({
        id: types.id,
        name: types.name,
        price: types.price1,
        onlinePercentage: hotels.online_price,
        nonRefundablePercentage: hotels.non_refundable_price,
        images: sql`JSON_ARRAYAGG(rooms.image)`.as("images"),
        // images: sql`JSON_ARRAYAGG(DISTINCT rooms.image)`.as("images"),
        // Use a subquery within COUNT to check availability per room
        availableRoom: sql<number>`(
          SELECT COUNT(r.id)
          FROM ${rooms} as r
          WHERE r.type_id = ${types.id} -- Link room to the outer type
            AND r.hotel_id = ${hotelId} -- Filter by the specific hotel
            AND r.status NOT IN ('occupied', 'repair', 'damage') -- Check room status directly
            AND NOT EXISTS ( -- Check for overlapping bookings for this specific room (r)
              SELECT 1 FROM ${bookings} as b
              WHERE b.bookable_id = r.id
                AND b.bookable_type = 'App\\Models\\Room' -- Adjust if needed
                AND b.status IN ('paid', 'unpaid', 'checkedin', 'checkedout') -- Relevant booking statuses
                AND b.checked_in < ${checkOut} -- Overlap condition
                AND b.checked_out > ${checkIn} -- Overlap condition
            )
        )`.as("availableCount"),
      })
      .from(types)
      .innerJoin(hotels, eq(types.hotel_id, hotels.id))
      .leftJoin(rooms, and(eq(types.id, rooms.type_id), isNotNull(rooms.image)))
      .where(eq(types.hotel_id, hotelId))
      .groupBy(
        types.id,
        types.name,
        types.price1, // Include price in group by as it's selected
        hotels.online_price,
        hotels.non_refundable_price
      )
      .orderBy(types.name);

    const typesWithAvailability = typesWithAvailabilityRaw.map((type) => {
      let imageList: string[] = [];

      if (Array.isArray(type.images)) {
        imageList = type.images;
      } else if (typeof type.images === "string") {
        try {
          imageList = JSON.parse(type.images);
        } catch (err) {
          console.error("Image parse error:", err);
        }
      }

      return {
        ...type,
        images: imageList
          .filter((img) => img)
          .map((url) => getFullImageUrl(url)),
      };
    });

    return typesWithAvailability;
  }

  async getAvailableRoomsByType(
    typeId: number,
    checkIn: string,
    checkOut: string
  ) {
    // 1. Find the hotel_id associated with the typeId
    const typeInfo = await db
      .select({ hotelId: types.hotel_id })
      .from(types)
      .where(eq(types.id, typeId))
      .limit(1);

    if (!typeInfo || typeInfo.length === 0) {
      throw new Error("Room type not found"); // Or return null/empty array as needed
    }
    const hotelId = typeInfo[0].hotelId;

    // 2. Find available rooms for this type and hotel
    const availableRoomsRaw = await db
      .select({
        id: rooms.id,
        roomNumber: rooms.number,
        image: rooms.image,
        price: types.price1,
        onlinePercentage: hotels.online_price,
        nonRefundablePercentage: hotels.non_refundable_price,
      })
      .from(rooms)
      .innerJoin(types, eq(rooms.type_id, types.id))
      .innerJoin(hotels, eq(rooms.hotel_id, hotels.id))
      .where(
        and(
          eq(rooms.type_id, typeId),
          notInArray(rooms.status, ["occupied", "repair", "damage"]), // Added 'damage' back based on previous context
          sql`NOT EXISTS (
            SELECT 1 FROM ${bookings} as b
            WHERE b.bookable_id = ${rooms.id}
              AND b.bookable_type = 'App\\Models\\Room'
              AND b.status IN ('paid', 'unpaid', 'checkedin', 'checkedout')
              AND b.checked_in < ${checkOut}
              AND b.checked_out > ${checkIn}
          )`
        )
      )
      .orderBy(rooms.number);

    // Format the results using the RoomResource
    const formattedRooms = RoomResource.toCollection(availableRoomsRaw);

    return formattedRooms; // Return the formatted data
  }
  async searchHotelsNearby(
    customerId: number | undefined,
    latitude: number | undefined,
    longitude: number | undefined,
    page: number,
    perPage: number,
    search: string | undefined,
    checkIn?: string,
    checkOut?: string
  ) {
    if (!latitude || !longitude) return;

    const radius = 50; // km
    const earthRadius = 6371; // km
    const latRad = latitude * (Math.PI / 180);
    const lonRad = longitude * (Math.PI / 180);
    const offset = (page - 1) * perPage;

    const distanceSql = sql<number>`(
    ${earthRadius} * acos(
      cos(${latRad}) * cos(radians(${hotels.latitude})) *
      cos(radians(${hotels.longitude}) - ${lonRad}) +
      sin(${latRad}) * sin(radians(${hotels.latitude}))
    )
  )`;

    // Shared filters
    const baseDistanceFilter = sql`${distanceSql} <= ${radius}`;
    const searchFilter = search
      ? sql`LOWER(${hotels.name}) LIKE LOWER('%' || ${search} || '%')`
      : undefined;
    const availabilityFilter =
      checkIn && checkOut
        ? sql`EXISTS (
          SELECT 1 FROM rooms
          LEFT JOIN bookings ON rooms.id = bookings.bookable_id AND bookings.bookable_type = 'App\\Models\\Room'
          WHERE rooms.hotel_id = ${hotels.id}
          AND rooms.status NOT IN ('occupied', 'repair')
          AND NOT EXISTS (
            SELECT 1 FROM bookings
            WHERE bookings.bookable_id = rooms.id AND bookings.bookable_type = 'App\\Models\\Room'
            AND bookings.status IN ('paid', 'unpaid', 'checkedin', 'checkout')
            AND NOT (
              bookings.checked_out <= ${checkIn} OR
              bookings.checked_in >= ${checkOut}
            )
          )
        )`
        : undefined;

    // Result query
    let query = db
      .select({
        id: hotels.id,
        name: hotels.name,
        address: hotels.address,
        status: hotels.status,
        starRating: hotels.star_rating,
        image: hotels.background_image,
        distance: distanceSql.as("distance"),
        isFavourite: customerId
          ? sql<boolean>`EXISTS(
            SELECT 1 FROM favourites 
            WHERE favourites.customer_id = ${customerId} 
            AND favourites.hotel_id = ${hotels.id}
          )`.as("isFavourite")
          : sql<boolean>`false`.as("isFavourite"),
      })
      .from(hotels)
      .where(and(baseDistanceFilter, availabilityFilter, searchFilter))
      .orderBy(sql`distance`)
      .limit(perPage)
      .offset(offset);

    const results = await query;

    // Count query
    const countQuery = db
      .select({ count: sql<number>`COUNT(*)` })
      .from(hotels)
      .where(and(baseDistanceFilter, availabilityFilter, searchFilter));

    const total = await countQuery;

    return HotelResource.toCollection(results, total[0].count, page, perPage);
  }

  // --- New function to list favourite hotels ---
  private getListFavouritesCacheKey(
    customerId: number,
    page: number,
    perPage: number
  ): string {
    return `favourites:customer:${customerId}:page:${page}:perPage:${perPage}`;
  }

  private async invalidateFavouritesCache(customerId: number): Promise<void> {
    const pattern = `favourites:customer:${customerId}:*`;
    const keys = await redis.keys(pattern);
    if (keys.length > 0) {
      await redis.del(keys);
      console.log(
        `Invalidated favourites cache for customer ${customerId}: ${keys.join(
          ", "
        )}`
      );
    }
  }

  async listFavouriteHotels(
    customerId: number,
    page: number,
    perPage: number,
    c: Context
  ) {
    const cacheKey = this.getListFavouritesCacheKey(customerId, page, perPage);

    try {
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for favourites key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for favourites key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting favourites cache:", error);
      // Proceed to fetch from DB if Redis fails
    }

    const offset = (page - 1) * perPage;

    const user = c.get("user");

    // Query favourite hotels for the customer
    const results = await db
      .select({
        id: hotels.id,
        name: hotels.name,
        code: hotels.code,
        address: hotels.address,
        roomPrice: hotels.room_lowest_price,
        hallPrice: hotels.hall_lowest_price,
        onlinePercentage: hotels.online_price,
        nonRefundablePercentage: hotels.non_refundable_price,
        status: hotels.status,
        percentageCharge: hotels.percentage_charge,
        starRating: hotels.star_rating,
        distance: sql<number>`(
          6371 * acos(
            cos(radians(${user.latitude})) * cos(radians(${hotels.latitude})) *
            cos(radians(${hotels.longitude}) - radians(${user.longitude})) +
            sin(radians(${user.latitude})) * sin(radians(${hotels.latitude}))
          )
        )`.as("distance"),
        reviewCount: sql<number>`COUNT(ratings.id)`.as("reviewCount"),
        averageRating: sql<number>`AVG(ratings.number)`.as("averageRating"),
        isFavourite: sql<boolean>`true`.as("isFavourite"),
      })
      .from(favourites)
      .innerJoin(hotels, eq(favourites.hotel_id, hotels.id))
      .leftJoin(ratings, eq(hotels.id, ratings.hotel_id))
      .where(eq(favourites.customer_id, customerId))
      .groupBy(hotels.id)
      .orderBy(hotels.name)
      .limit(perPage)
      .offset(offset);

    // Get total count of favourite hotels for the customer
    const total = await db
      .select({ count: sql<number>`COUNT(*)` })
      .from(favourites)
      .where(eq(favourites.customer_id, customerId));

    const responseData = HotelResource.toCollection(
      results,
      total[0].count,
      page,
      perPage
    );

    try {
      // Cache the response data
      await redis.set(
        cacheKey,
        JSON.stringify(responseData),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached favourites data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting favourites cache:", error);
    }

    return responseData;
  }
  // --- End of new function ---

  async toggleHotelFavourite(customerId: number, hotelId: number) {
    // Fetch the city_id for cache invalidation
    const hotelInfo = await db
      .select({ cityId: hotels.city_id })
      .from(hotels)
      .where(eq(hotels.id, hotelId))
      .limit(1);
    const cityId = hotelInfo.length > 0 ? hotelInfo[0].cityId : null;

    // Check if favourite already exists using correct column name
    const existing = await db
      .select()
      .from(favourites)
      .where(
        and(
          eq(favourites.customer_id, customerId),
          eq(favourites.hotel_id, hotelId) // Corrected column name
        )
      )
      .limit(1);

    let message: string;
    if (existing.length > 0) {
      // Remove from favourites
      await db.delete(favourites).where(
        and(
          eq(favourites.customer_id, customerId),
          eq(favourites.hotel_id, hotelId) // Corrected column name
        )
      );
      message = "Hotel removed from favourites.";
    } else {
      // Add to favourites
      await db
        .insert(favourites)
        .values({ customer_id: customerId, hotel_id: hotelId }); // Corrected column name
      message = "Hotel added to favourites.";
    }

    // Invalidate relevant caches
    const invalidationPromises = [];
    if (cityId !== null) {
      invalidationPromises.push(this.invalidateCityHotelCache(cityId));
    } else {
      console.warn(
        `Could not find cityId for hotel ${hotelId} to invalidate city cache.`
      );
    }
    // Always invalidate the user's favourites cache
    invalidationPromises.push(this.invalidateFavouritesCache(customerId));

    try {
      await Promise.all(invalidationPromises);
    } catch (error) {
      console.error("Redis error during cache invalidation:", error);
      // Continue even if cache invalidation fails
    }

    return ApiResponse.success(message);
  }
}
