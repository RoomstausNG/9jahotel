/**
 * Calculate the number of days between two dates
 */
export const getDaysBetweenDates = (
  checkIn: Date | string,
  checkOut: Date | string
): number => {
  const startDate = typeof checkIn === "string" ? new Date(checkIn) : checkIn;
  const endDate = typeof checkOut === "string" ? new Date(checkOut) : checkOut;
  const timeDifference = endDate.getTime() - startDate.getTime();
  const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
  return Math.max(1, daysDifference);
};

/**
 * Calculate the discounted amount and remaining amount after applying a percentage
 */
export const calculateDiscountedAmount = (
  amount: number,
  days: number,
  percentage: number
): {
  originalAmount: number;
  discountedAmount: number;
  remainingAmount: number;
} => {
  const originalAmount = amount * days;
  const validPercentage = Math.min(100, Math.max(0, percentage));
  const discountedAmount = Math.floor((originalAmount * validPercentage) / 100);
  const remainingAmount = originalAmount - discountedAmount;
  return {
    originalAmount,
    discountedAmount,
    remainingAmount,
  };
};

/**
 * Format a date string to a human-readable format
 * @param dateString - ISO date string
 * @returns Formatted date string like "December 18, 2025 at 9:10 AM"
 */
export const formatDateToHumanReadable = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleString("en-US", {
    timeZone: "Africa/Lagos",
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  });
};

/**
 * Normalize a checkout date to always be at 12:00 PM (noon) in WAT timezone
 * @param dateString - ISO date string
 * @returns ISO date string with time set to 11:00:00 UTC (which becomes 12:00:00 WAT)
 */
export const normalizeCheckoutTimeToNoon = (dateString: string): string => {
  const date = new Date(dateString);
  date.setHours(12, 0, 0, 0); // Set to 11:00:00 UTC (which is 12:00:00 WAT)
  return date.toISOString();
};

/**
 * FIXED: Calculate hotel payable and commission with correct business logic
 * Hotel payable is FIXED, platform commission varies with coupons
 */
export function ourCommission(
  originalAmount: number,
  customerDiscountAmount: number,
  platformCommissionPercent: number,
  customerPaidAmount: number,
  couponAmount: number = 0
) {
  // Step 1: Amount after customer discount
  const amountAfterCustomerDiscount = originalAmount - customerDiscountAmount;

  const platformCommissionAmount = Math.floor(
    (amountAfterCustomerDiscount * platformCommissionPercent) / 100
  );

  const hotelPayable = amountAfterCustomerDiscount - platformCommissionAmount;

  const commissionAmount = platformCommissionAmount - couponAmount;

  return {
    originalAmount,
    customerDiscountAmount,
    amountAfterCustomerDiscount,
    platformCommissionAmount,
    hotelPayable, // FIXED amount hotel always receives (unaffected by coupons)
    commissionAmount, // Platform net after absorbing coupon cost
    customerPaidAmount,
    couponAmount,
  };
}
