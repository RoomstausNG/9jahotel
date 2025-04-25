import { getCloudinaryImage, getFullImageUrl } from "../utils/cloudinaryUtils";

import { Hotel } from "../types/models";
import { buildReviewSummary } from "../utils/reviewSummary";

export class HotelResource {
  // Transform a single hotel with optional field selection
  static toResource(hotel: Hotel, fields?: string[]) {
    // Default resource shape
    const resource = {
      id: hotel.id,
      name: hotel.name,
      code: hotel.code,
      status: hotel.status,
      image: getFullImageUrl(hotel.image),
      description: hotel.description,
      address: hotel.city
        ? `${hotel.address}, ${hotel.city.name}`
        : hotel.address,
      city: {
        id: hotel.city?.id,
        name: hotel.city?.name,
      },
      roomPrice: hotel.roomPrice,
      hallPrice: hotel.hallPrice,
      onlinePercentage: hotel.onlinePercentage,
      nonRefundablePercentage: hotel.nonRefundablePercentage,
      latitude: hotel.latitude,
      longitude: hotel.longitude,
      distance: parseFloat(hotel.distance?.toFixed(2) ?? "0"),
      reviews: {
        total: hotel.reviewCount || 0,
        averageRating: parseFloat(hotel.averageRating?.toFixed(1) ?? "0.0"),
        summary: buildReviewSummary(hotel.reviews || []),
        list: hotel.reviews,
      },
      starRating: hotel.starRating,
      isFavourite: Boolean(hotel.isFavourite),
    };

    // If specific fields are requested, filter to only those fields
    if (fields && fields.length > 0) {
      return Object.fromEntries(
        Object.entries(resource).filter(([key]) => fields.includes(key))
      );
    }

    return resource;
  }

  // Transform a collection with pagination
  static toCollection(
    hotels: Hotel[] | any,
    total: number,
    page: number,
    perPage: number,
    fields?: string[]
  ) {
    const mappedhotels = hotels.map((hotel: any) =>
      this.toResource(hotel, fields)
    );

    const response: any = {
      hotels: mappedhotels,
    };

    // Only include pagination if there is at least one hotel
    if (mappedhotels.length > 0) {
      const lastPage = Math.ceil(total / perPage);
      const hasMorePages = page < lastPage;
      const isFirstPage = page === 1;

      response.meta = {
        pagination: {
          total,
          count: mappedhotels.length,
          per_page: perPage,
          current_page: page,
          total_pages: lastPage,
          has_more_pages: hasMorePages,
          first_page: isFirstPage,
          last_page: page === lastPage,
          next_page: hasMorePages ? page + 1 : null,
          prev_page: page > 1 ? page - 1 : null,
        },
      };
    }

    return response;
  }

  // Method for detailed hotel profile (when more data is needed)
  static toDetail(
    hotelDetails: any,
    reviewsSummary: any,
    isFavourite: boolean,
    amenities: any[] // Add the amenities parameter here
  ) {
    // Map the images to Cloudinary URLs if available
    const imageUrls =
      hotelDetails.imageUrls && hotelDetails.imageUrls.length > 0
        ? hotelDetails.imageUrls.map((image: string) => getFullImageUrl(image))
        : []; // Default to an empty array if no images

    return {
      id: hotelDetails.id,
      name: hotelDetails.name,
      code: hotelDetails.code,
      status: hotelDetails.status,
      image: hotelDetails.image ? getCloudinaryImage(hotelDetails.image) : null, // Cloudinary URL for main image
      description: hotelDetails.description,
      address: hotelDetails.city
        ? `${hotelDetails.address}, ${hotelDetails.city.name}`
        : hotelDetails.address,
      city: {
        id: hotelDetails.city?.id,
        name: hotelDetails.city?.name,
      },
      roomPrice: hotelDetails.roomPrice,
      hallPrice: hotelDetails.hallPrice,
      onlinePercentage: hotelDetails.onlinePercentage,
      nonRefundablePercentage: hotelDetails.nonRefundablePercentage,
      latitude: hotelDetails.latitude,
      longitude: hotelDetails.longitude,
      distance: parseFloat(hotelDetails.distance?.toFixed(2) ?? "0"),
      reviews: {
        total: hotelDetails.reviewCount || 0,
        averageRating: parseFloat(
          hotelDetails.averageRating?.toFixed(1) ?? "0.0"
        ),
        summary: reviewsSummary,
        list: hotelDetails.reviews,
      },
      starRating: hotelDetails.starRating,
      images:
        hotelDetails.imageUrls?.map((url: string) => getFullImageUrl(url)) || // Use getFullImageUrl consistently
        [],
      amenities: amenities, // Include amenities in the response object
      isFavourite, // Based on customerId check in service
    };
  }
}
