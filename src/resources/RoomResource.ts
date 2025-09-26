import { getCloudinaryImage } from "../utils/cloudinaryUtils"; // Assuming you might use Cloudinary

// Define the structure of the raw room data coming from the service
interface RawRoomData {
  id: number;
  roomNumber: string | null; // Assuming 'number' column in rooms table is string type
  image: string | null;
  background_image: string | null;
  logo: string | null;
  price: number | null;
  onlinePercentage: number | null;
  nonRefundablePercentage: number | null;
  status: string;
}

// Define the structure of the formatted room data for the API response
// interface FormattedRoom {
//   id: number;
//   number: string | null;
//   imageUrl: string | null;
//   price: number | null;

// }

export class RoomResource {
  /**
   * Formats a single room object.
   * @param room - The raw room data object.
   * @returns The formatted room object.
   */
  static toResource(room: RawRoomData) {
    return {
      id: room.id,
      number: room.roomNumber,
      imageUrl: room.image
        ? getCloudinaryImage(room.image)
        : room.background_image
          ? getCloudinaryImage(room.background_image)
          : room.logo
            ? getCloudinaryImage(room.logo)
            : `${env("APP_URL")}/img/logo.png`,
      price: room.price,
      onlinePercentage: room.onlinePercentage,
      nonRefundablePercentage: room.nonRefundablePercentage,
      status: room.status,
    };
  }

  /**
   * Formats an array of room objects.
   * @param rooms - An array of raw room data objects.
   * @returns An array of formatted room objects.
   */
  static toCollection(rooms: RawRoomData[]) {
    return rooms.map((room) => this.toResource(room));
  }
}
