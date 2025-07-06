// BookingResource.ts
import { getCloudinaryImage, getFullImageUrl } from "../utils/cloudinaryUtils";

export class BookingResource {
  static toResource(booking: any) {
    if (!booking) return null;
    return {
      id: booking.id,
      reference: booking.reference,
      amount: booking.amount,
      commissionAmount: booking.commission_amount,
      hotelPayable: booking.hotel_payable,
      checkedIn: booking.checked_in,
      checkedOut: booking.checked_out,
      type: booking.type,
      createdAt: booking.created_at,
      updatedAt: booking.updated_at,
      status: booking.status,
      customer: booking.customer
        ? {
            id: booking.customer.id,
            firstname: booking.customer.firstname,
            lastname: booking.customer.lastname,
            email: booking.customer.email,
            phone: booking.customer.phone,
            avatar: booking.customer.avatar,
          }
        : null,
      hotel: booking.hotel
        ? {
            id: booking.hotel.id,
            name: booking.hotel.name,
            code: booking.hotel.code,
            email: booking.hotel.email,
            address: booking.hotel.address,
            phone: booking.hotel.phone,
            backgroundImage: booking.hotel.background_image
              ? getCloudinaryImage(booking.hotel.background_image)
              : null,
          }
        : null,
      bookings: booking.bookings
        ? booking.bookings.map((b: any) => ({
            id: b.id,
            code: b.code,
            roomNumber: b.room ? b.room.number : undefined,
            hallName: b.hall ? b.hall.name : undefined,
            category: b.room
              ? b.room.category
              : b.hall
              ? b.hall.category
              : undefined,
          }))
        : [],
    };
  }

  /**
   * Returns a paginated collection of bookings, matching the HotelResource style.
   * @param bookings Array of booking objects
   * @param total Total number of bookings
   * @param page Current page number
   * @param perPage Number of bookings per page
   */
  static toCollection(
    bookings: any[],
    total: number,
    page: number,
    perPage: number
  ) {
    const mappedBookings = bookings.map(this.toResource);

    const lastPage = Math.ceil(total / perPage);
    const hasMorePages = page < lastPage;
    const isFirstPage = page === 1;

    return {
      bookings: mappedBookings,
      meta: {
        pagination: {
          total,
          count: mappedBookings.length,
          per_page: perPage,
          current_page: page,
          total_pages: lastPage,
          has_more_pages: hasMorePages,
          first_page: isFirstPage,
          last_page: page === lastPage,
          next_page: hasMorePages ? page + 1 : null,
          prev_page: page > 1 ? page - 1 : null,
        },
      },
    };
  }
}
