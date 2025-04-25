import { z } from "zod";
import { existHotel } from "../validators/exists-hotel";

export const ListFavouritesDTO = z.object({
  page: z
    .string()
    .optional()
    .transform((val) => (val ? Number(val) : 1))
    .refine((val) => !isNaN(val) && val > 0, {
      message: "Page must be a positive number",
    }),
  perPage: z
    .string()
    .optional()
    .transform((val) => (val ? Number(val) : 24))
    .refine((val) => !isNaN(val) && val > 0, {
      message: "perPage must be a positive number",
    }),
});

export const ToggleFavouriteDTO = z.object({
  hotelId: existHotel,
});

export type ListFavouritesDTOType = z.infer<typeof ListFavouritesDTO>;
export type ToggleFavouriteDTOType = z.infer<typeof ToggleFavouriteDTO>;
