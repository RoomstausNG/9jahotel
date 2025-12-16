import { z } from "zod";

// TV Registration DTO
export const TVRegistrationDTO = z.object({
  hotelcode: z.string().min(1, "Hotel code is required"),
  roomnumber: z.string().min(1, "Room number is required"),
  macaddress: z.string().min(1, "MAC address is required"),
  ipaddress: z
    .string()
    .min(1, "IP address is required")
    .regex(/^(\d{1,3}\.){3}\d{1,3}$/, "Invalid IP address format"),
});

// Check-in Event DTO
export const CheckInDTO = z.object({
  hotelcode: z.string().min(1, "Hotel code is required"),
  roomnumber: z.string().min(1, "Room number is required"),
  message: z.string().optional(),
  fullname: z.string().min(1, "Full name is required"),
  macaddress: z.string().min(1, "MAC address is required"),
  tvid: z.string().min(1, "TV ID is required"),
  checkindatetime: z
    .string()
    .min(1, "Check-in date time is required")
    .refine((val) => !isNaN(Date.parse(val)), "Invalid ISO 8601 date format"),
  checkoutdatetime: z
    .string()
    .min(1, "Check-out date time is required")
    .refine((val) => !isNaN(Date.parse(val)), "Invalid ISO 8601 date format"),
});

// Reminder Event DTO
export const ReminderDTO = z.object({
  hotelcode: z.string().min(1, "Hotel code is required"),
  roomnumber: z.string().min(1, "Room number is required"),
  macaddress: z.string().min(1, "MAC address is required"),
  reminderType: z.enum(["checkout_2h", "checkout_1h"], {
    errorMap: () => ({
      message: "Reminder type must be 'checkout_2h' or 'checkout_1h'",
    }),
  }),
  checkoutdatetime: z
    .string()
    .min(1, "Check-out date time is required")
    .refine((val) => !isNaN(Date.parse(val)), "Invalid ISO 8601 date format"),
  message: z.string().optional(),
});

// Checkout Event DTO
export const CheckoutDTO = z.object({
  hotelcode: z.string().min(1, "Hotel code is required"),
  roomnumber: z.string().min(1, "Room number is required"),
  fullname: z.string().min(1, "Full name is required"),
  macaddress: z.string().min(1, "MAC address is required"),
  checkoutdatetime: z
    .string()
    .min(1, "Check-out date time is required")
    .refine((val) => !isNaN(Date.parse(val)), "Invalid ISO 8601 date format"),
  message: z.string().optional(),
});

// Broadcast All Event DTO
export const BroadcastAllDTO = z.object({
  hotelcode: z.string().min(1, "Hotel code is required"),
  message: z.string().min(1, "Message is required"),
  title: z.string().optional(),
});
