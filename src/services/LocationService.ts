import { injectable } from "inversify";
import { cities, states, hotels, ratings } from "../database/migrations/schema";
import { eq, lt, or, like, sql, countDistinct, and } from "drizzle-orm";
import { db, dbPool } from "../config/database";
import { CityResource } from "../resources/CityResource";
import { HotelResource } from "../resources/HotelResource";
import { redis } from "../config/redis"; // Import Redis client

@injectable()
export class LocationService {
  private readonly CACHE_EXPIRATION_SECONDS = 3600; // Cache for 1 hour

  // No constructor injection for database
  async getAllCities(page: number, perPage: number, search: string) {
    const cacheKey = `cities:page:${page}:limit:${perPage}:search:${
      search || ""
    }`;

    try {
      // Try to get from cache first
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

    // Prepare the search filter and country filter
    const countryFilter = eq(cities.country_id, 160);
    const searchFilter = search
      ? and(
          countryFilter,
          or(like(cities.name, `%${search}%`), like(states.name, `%${search}%`))
        )
      : countryFilter;

    // Fetch cities with optional search filter and country filter
    const cityList = await dbPool
      .select({
        id: cities.id,
        name: cities.name,
        state: states.name,
        hotel: sql<number>`COUNT(${hotels.id})`.as("hotel_count"),
      })
      .from(cities)
      .innerJoin(states, eq(cities.state_id, states.id))
      .innerJoin(hotels, eq(cities.id, hotels.city_id))
      .where(searchFilter)
      .groupBy(cities.id, states.name)
      .limit(perPage)
      .offset(offset);

    // Fetch total count based on the search filter and country filter
    const [{ count: totalcities }] = await db
      .select({ count: countDistinct(cities.id).as("count") })
      .from(cities)
      .innerJoin(states, eq(cities.state_id, states.id))
      .innerJoin(hotels, eq(cities.id, hotels.city_id))
      .where(searchFilter);

    const result = CityResource.toCollection(
      cityList,
      totalcities,
      page,
      perPage
    );

    try {
      // Cache the result
      await redis.set(
        cacheKey,
        JSON.stringify(result),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return result;
  }

  async searchHotelsNearby(
    lat: number,
    long: number,
    maxDistanceKm: number,
    page = 1,
    perPage = 10,
    search = "",
    customerId?: number
  ) {
    const cacheKey = `hotels:nearby:lat:${lat}:long:${long}:maxDist:${maxDistanceKm}:page:${page}:limit:${perPage}:search:${
      search || ""
    }:user:${customerId || "guest"}`;

    try {
      // Try to get from cache first
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

    const distanceExpr = sql<number>`
    6371 * acos(
      cos(radians(${lat})) *
      cos(radians(hotels.latitude)) *
      cos(radians(hotels.longitude) - radians(${long})) +
      sin(radians(${lat})) *
      sin(radians(hotels.latitude))
    )
  `.as("distance");

    const searchFilter = search ? like(hotels.name, `%${search}%`) : undefined;

    const hotelList = await db
      .select({
        id: hotels.id,
        name: hotels.name,
        image: hotels.background_image,
        address: hotels.address,
        city: hotels.city_id,
        roomPrice: hotels.room_lowest_price,
        hallPrice: hotels.hall_lowest_price,
        status: hotels.status,
        distance: distanceExpr,
        reviewCount: sql<number>`COUNT(ratings.id)`.as("reviewCount"),
        averageRating: sql<number>`AVG(ratings.number)`.as("averageRating"),
        starRating: hotels.star_rating,
        isFavourite:
          sql<boolean>`CASE WHEN EXISTS(SELECT 1 FROM favourites WHERE hotel_id = ${hotels.id} AND user_id = ${customerId}) THEN true ELSE false END`.as(
            "isFavourite"
          ),
      })
      .from(hotels)
      .leftJoin(ratings, eq(hotels.id, ratings.hotel_id))
      .where(
        and(
          eq(hotels.status, "active"),
          ...(searchFilter ? [searchFilter] : [])
        )
      )
      .groupBy(hotels.id)
      .having(lt(sql`distance`, maxDistanceKm))
      .orderBy(sql`distance`)
      .limit(perPage)
      .offset(offset);

    const [{ count: total }] = await db
      .select({ count: sql<number>`COUNT(*)` })
      .from(hotels)
      .where(
        and(
          eq(hotels.status, "active"),
          ...(searchFilter ? [searchFilter] : [])
        )
      );

    const result = HotelResource.toCollection(hotelList, total, page, perPage);

    try {
      // Cache the result
      await redis.set(
        cacheKey,
        JSON.stringify(result),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return result;
  }

  // Add caching for getCityById method
  async getCityById(cityId: number) {
    const cacheKey = `city:${cityId}`;

    try {
      // Try to get from cache first
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting cache:", error);
    }

    // Fetch city data (only where country_id = 160)
    const city = await db
      .select({
        id: cities.id,
        name: cities.name,
        state: states.name,
      })
      .from(cities)
      .innerJoin(states, eq(cities.state_id, states.id))
      .where(and(eq(cities.id, cityId), eq(cities.country_id, 160)))
      .limit(1);

    if (!city || city.length === 0) {
      throw new Error("City not found");
    }

    // Get hotel count for this city
    const [{ count: hotelCount }] = await db
      .select({ count: countDistinct(hotels.id).as("count") })
      .from(hotels)
      .where(eq(hotels.city_id, cityId));

    const result = {
      ...city[0],
      hotelCount,
    };

    try {
      // Cache the result
      await redis.set(
        cacheKey,
        JSON.stringify(result),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return result;
  }

  // Add caching for getPopularCities method
  async getPopularCities(limit: number = 10) {
    const cacheKey = `cities:popular:limit:${limit}`;

    try {
      // Try to get from cache first
      const cachedData = await redis.get(cacheKey);
      if (cachedData) {
        console.log(`Cache hit for key: ${cacheKey}`);
        return JSON.parse(cachedData);
      }
      console.log(`Cache miss for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error getting cache:", error);
    }

    // Fetch popular cities based on hotel count (only where country_id = 160)
    const popularCities = await db
      .select({
        id: cities.id,
        name: cities.name,
        state: states.name,
        hotelCount: sql<number>`COUNT(${hotels.id})`.as("hotel_count"),
      })
      .from(cities)
      .innerJoin(states, eq(cities.state_id, states.id))
      .innerJoin(hotels, eq(cities.id, hotels.city_id))
      .where(eq(cities.country_id, 160))
      .groupBy(cities.id, states.name)
      .orderBy(sql`hotel_count DESC`)
      .limit(limit);

    try {
      // Cache the result
      await redis.set(
        cacheKey,
        JSON.stringify(popularCities),
        "EX",
        this.CACHE_EXPIRATION_SECONDS
      );
      console.log(`Cached data for key: ${cacheKey}`);
    } catch (error) {
      console.error("Redis error setting cache:", error);
    }

    return popularCities;
  }

  // Add method to invalidate city cache when data changes
  async invalidateCityCache(cityId?: number): Promise<void> {
    if (cityId) {
      // Invalidate specific city cache
      const cityKey = `city:${cityId}`;
      await redis.del(cityKey);
      console.log(`Invalidated cache for city ${cityId}`);
    }

    // Invalidate all cities list and popular cities caches
    const citiesPattern = `cities:*`;
    const keys = await redis.keys(citiesPattern);
    if (keys.length > 0) {
      await redis.del(keys);
      console.log(`Invalidated cities caches: ${keys.join(", ")}`);
    }
  }
}
