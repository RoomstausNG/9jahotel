// BookingResource.ts
export class BookingResource {
  static toResource(booking: any) {
    if (!booking) return null;
    return {
      id: booking.id,
      reference: booking.reference,
      amount: booking.amount,
      commissionAmount: booking.commission_amount,
      hotelPayable: booking.hotel_payable,
      status: booking.status,
      customerId: booking.customer_id,
      hotelId: booking.hotel_id,
      checkedIn: booking.checked_in,
      checkedOut: booking.checked_out,
      type: booking.type,
      createdAt: booking.created_at,
      updatedAt: booking.updated_at,
      bookings: booking.bookings
        ? booking.bookings.map((b: any) => ({
            id: b.id,
            code: b.code,
            roomId: b.room_id,
            type: b.type,
            bookingInvoiceId: b.booking_invoice_id,
            hotelId: b.hotel_id,
            customerId: b.customer_id,
            bookableId: b.bookable_id,
            due: b.due,
            discount: b.discount,
            amountPaid: b.amount_paid,
            via: b.via,
            bookableType: b.bookable_type,
            checkIn: b.check_in,
            checkOut: b.check_out,
            status: b.status,
            createdAt: b.created_at,
            updatedAt: b.updated_at,
            room: b.room
              ? {
                  id: b.room.id,
                  name: b.room.name,
                  // ...add more room fields as needed
                }
              : undefined,
          }))
        : [],
    };
  }

  static toCollection(bookings: any[]) {
    return bookings.map(this.toResource);
  }
}