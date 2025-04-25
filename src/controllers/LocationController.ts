import { LocationService } from "./../services/LocationService";
import { inject, injectable } from "inversify";
import { Context } from "hono";
import { ApiResponse } from "../utils/ApiResponse";

@injectable()
export class LocationController {
  constructor(
    @inject(LocationService) private locationService: LocationService
  ) {}

  async getCities(c: Context) {
    try {
      const page = Number(c.req.query("page") || 1);
      const perPage = Number(c.req.query("perPage") || 24);

      // all search terms
      const searchParams = c.req.query("search") || "";

      const cities = await this.locationService.getAllCities(
        page,
        perPage,
        searchParams
      );
      return ApiResponse.success(cities, "City fetch successfully");
    } catch (error: any) {
      return ApiResponse.failure(error.message, 500);
    }
  }

  async searchNearbyHotels(c: Context) {
    const lat = parseFloat(c.req.query("lat") || "0");
    const long = parseFloat(c.req.query("long") || "0");
    const distance = parseFloat(c.req.query("distance") || "10"); // Default to 10km
    const page = parseInt(c.req.query("page") || "1");
    const perPage = parseInt(c.req.query("perpage") || "10");
    const search = c.req.query("search") || "";

    const hotels = await this.locationService.searchHotelsNearby(
      lat,
      long,
      distance,
      page,
      perPage,
      search
    );

    return ApiResponse.success(hotels, "Hotels fetched successfully");
  }

  async show(c: Context) {
    try {
      const idParam = c.req.param("id");
      const id = Number(idParam);

      // Check if id is a valid number
      if (isNaN(id) || id <= 0) {
        return ApiResponse.failure(
          "Invalid user ID, the id must be a number",
          400
        );
      }

      const user = await this.locationService.getCityById(id);
      return ApiResponse.success(user);
    } catch (error: any) {
      if (error instanceof Response) {
        return error;
      }
      return ApiResponse.failure(
        "Internal Server Error while fetching user",
        500
      );
    }
  }

  //   async store(c: Context) {
  //     try {
  //       const body = await c.req.json();
  //       const user = await this.citieservice.createUser(body);
  //       return ApiResponse.success(user, "User created successfully", 201);
  //     } catch (error: any) {
  //       return ApiResponse.failure(error.message, 400);
  //     }
  //   }
}
