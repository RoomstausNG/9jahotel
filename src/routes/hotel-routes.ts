import { Hono } from "hono";
import { container } from "../container";
import { HotelController } from "../controllers/HotelController"; // Import HotelController
import { LocationController } from "../controllers/LocationController"; // Import HotelController
// Use the wrapper for consistent error handling
import { zValidator } from "../validators/validator-wrapper";
import { NearbyHotelDTO, HotelCodeDTO } from "../dtos/LocationDTO"; // Keep if needed for nearby search, or remove if handled elsewhere
import { authMiddleware } from "../middlewares/auth-middleware"; // Import auth middleware
import {
  ListFavouritesDTO,
  ToggleFavouriteDTO,
} from "../dtos/FavouriteHotelDTO";
import {
  SearchHotelByCityDTO,
  CityDTO,
  SearchHotelTypeDTO,
} from "../dtos/SearchHotelDTO";
// Import the new DTOs for validation
import { RoomTypeParamDTO, AvailableRoomsQueryDTO } from "../dtos/RoomDTO";

const hotelRoutes = new Hono(); // Rename to hotelRoutes
const hotelController = container.get(HotelController); // Get HotelController instance
const locationController = container.get(LocationController); // Get HotelController instance

// Route to list hotels by city ID
hotelRoutes.get("/cities", (c) => locationController.getCities(c));
hotelRoutes.get(
  "/hotels/city/:cityId",
  zValidator("param", CityDTO),
  zValidator("query", SearchHotelByCityDTO),
  (c) => hotelController.listByCity(c)
);

// Route to list favorite hotels (Protected)
hotelRoutes.get(
  "/hotels/favourites",
  authMiddleware,
  zValidator("query", ListFavouritesDTO),
  (c) => hotelController.listFavourites(c)
);

// Route to toggle favorite status for a hotel (Protected)
hotelRoutes.post(
  "/hotels/toggle-favourite",
  authMiddleware,
  zValidator("json", ToggleFavouriteDTO),
  (c) => hotelController.toggleFavourite(c)
);

// Keep nearby hotels search if it belongs here, otherwise move/remove
// Example: If nearby search is still relevant under hotel routes
hotelRoutes.get(
  "/hotels/nearby",
  zValidator("query", NearbyHotelDTO),
  (c) => hotelController.searchNearbyHotels(c) // Assuming HotelController handles this now
);

hotelRoutes.get(
  "/hotels/:code",
  zValidator("param", HotelCodeDTO),
  (c) => hotelController.showHotelDetails(c) // Assuming HotelController handles this now
);

hotelRoutes.get(
  "/hotels/:code/categories",
  zValidator("param", HotelCodeDTO),
  zValidator("query", SearchHotelTypeDTO),
  (c) => hotelController.getHotelTypes(c)
);

// New route for available rooms by categories
hotelRoutes.get(
  "/hotels/categories/:typeId/rooms", // Define the new route path
  zValidator("param", RoomTypeParamDTO), // Validate typeId from path
  zValidator("query", AvailableRoomsQueryDTO), // Validate checkIn/checkOut from query
  (c) => hotelController.listAvailableRoomsByType(c) // Link to the new controller method
);

// New route for images by room type
hotelRoutes.get(
  "/hotels/categories/:typeId/images", // Define the new route path for images
  zValidator("param", RoomTypeParamDTO), // Validate typeId from path
  (c) => hotelController.getImagesByRoomType(c) // Link to the new controller method
);

export default hotelRoutes;
