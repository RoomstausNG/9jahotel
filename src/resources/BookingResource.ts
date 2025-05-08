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

  static toCollection(bookings: any[]) {
    return bookings.map(this.toResource);
  }
}
