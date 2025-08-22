import { defineStore } from "pinia";

interface BookingData {
  checkIn: string;
  checkOut: string;
  guest: {
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
  };
  roomIds: number[];
  type: "PayAtHotel" | "NonRefundable" | "PayNow";
  total: number;
  categoryName?: string; // Add this for room category
}

interface BookingResponse {
  reference: string;
  status: string;
  amount: number;
  commission_amount: number;
  hotel_payable: number;
  customer_id: number;
  hotel_id: number;
  checked_in: string;
  checked_out: string;
  type: string;
  created_at: string;
  updated_at: string;
  hotel?: {
    id: number;
    name: string;
    address: string;
    phone: string;
    email: string;
    backgroundImage: string;
  };
  customer?: {
    id: number;
    firstname: string;
    lastname: string;
    email: string;
    phone: string;
  };
  bookings?: Array<{
    id: number;
    code: string;
    room_id: number;
    due: number;
    status: string;
    // ... other booking fields
  }>;
}

export const useBookingStore = defineStore(
  "booking",
  {
    state: () => ({
      currentBooking: null as BookingData | null,
      bookingResponse: null as BookingResponse | null, // Add this to store API response
    }),

    actions: {
      setBooking(bookingData: BookingData) {
        this.currentBooking = bookingData;
        // Save to localStorage as backup
        if (typeof window !== "undefined") {
          localStorage.setItem("currentBooking", JSON.stringify(bookingData));
        }
      },

      // NEW: Method to store the booking response after successful creation
      setBookingResponse(response: BookingResponse) {
        this.bookingResponse = response;
        // Save to localStorage as backup
        if (typeof window !== "undefined") {
          localStorage.setItem("bookingResponse", JSON.stringify(response));
        }
      },

      clearBooking() {
        this.currentBooking = null;
        this.bookingResponse = null; // Clear both
        if (typeof window !== "undefined") {
          localStorage.removeItem("currentBooking");
          localStorage.removeItem("bookingResponse");
          localStorage.removeItem("selectedRooms");
          localStorage.removeItem("hotelSearchParams");
        }
      },

      loadFromStorage() {
        if (typeof window !== "undefined") {
          // Load current booking data
          const storedBooking = localStorage.getItem("currentBooking");
          if (storedBooking) {
            try {
              this.currentBooking = JSON.parse(storedBooking);
            } catch (error) {
              console.error("Error loading booking from storage:", error);
            }
          }

          // Load booking response
          const storedResponse = localStorage.getItem("bookingResponse");
          if (storedResponse) {
            try {
              // Handle both direct response and wrapped response
              const parsed = JSON.parse(storedResponse);
              this.bookingResponse = parsed.data ? parsed.data : parsed;
            } catch (error) {
              console.error(
                "Error loading booking response from storage:",
                error
              );
            }
          }
        }
      },

      // NEW: Method to get booking details for confirmation page
      getBookingDetails() {
        // Return the API response if available, otherwise return null
        return this.bookingResponse;
      },
    },

    getters: {
      hasBooking: (state) => !!state.currentBooking,
      getBooking: (state) => state.currentBooking,
      hasBookingResponse: (state) => !!state.bookingResponse, // NEW
      getBookingResponse: (state) => state.bookingResponse, // NEW
    },
  },
  {
    persist: {
      paths: ["currentBooking", "bookingResponse"], // Persist both
    },
  }
);
