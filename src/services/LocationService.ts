import { injectable } from "inversify";
import { cities, states, hotels, ratings } from "../database/migrations/schema";
import { eq, lt, or, like, sql, countDistinct, and } from "drizzle-orm";
import { db } from "../config/database";
import { ApiResponse } from "../utils/ApiResponse";
import { CityResource } from "../resources/CityResource";
import { HotelResource } from "../resources/HotelResource";

@injectable()
export class LocationService {
  // No constructor injection for database
  async getAllCities(page: number, perPage: number, search: string) {
    const offset = (page - 1) * perPage;

    // Prepare the search filter
    const searchFilter = search
      ? or(like(cities.name, `%${search}%`), like(states.name, `%${search}%`))
      : undefined;

    // Fetch cities with optional search filter
    const cityList = await db
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

    // Fetch total count based on the search filter
    const [{ count: totalcities }] = await db
      .select({ count: countDistinct(cities.id).as("count") })
      .from(cities)
      .innerJoin(states, eq(cities.state_id, states.id))
      .innerJoin(hotels, eq(cities.id, hotels.city_id))
      .where(searchFilter);

    return CityResource.toCollection(cityList, totalcities, page, perPage);
  }

  async searchHotelsNearby(
    lat: number,
    long: number,
    maxDistanceKm: number,
    page = 1,
    perPage = 10,
    search = ""
  ) {
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

    return HotelResource.toCollection(hotelList, total, page, perPage);
  }
}
