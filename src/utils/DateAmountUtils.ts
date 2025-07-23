/**
 * Calculate the number of days between two dates
 * @param checkIn The check-in date
 * @param checkOut The check-out date
 * @returns The number of days between the dates (inclusive of check-in day)
 */
export const getDaysBetweenDates = (
  checkIn: Date | string,
  checkOut: Date | string
): number => {
  // Convert to Date objects if strings are provided
  const startDate = typeof checkIn === "string" ? new Date(checkIn) : checkIn;
  const endDate = typeof checkOut === "string" ? new Date(checkOut) : checkOut;
  // Calculate the time difference in milliseconds
  const timeDifference = endDate.getTime() - startDate.getTime();
  // Convert milliseconds to days and round up to include partial days
  const daysDifference = Math.ceil(timeDifference / (1000 * 60 * 60 * 24));
  // Return at least 1 day even for same-day bookings
  return Math.max(1, daysDifference);
};

/**
 * Calculate the discounted amount and remaining amount after applying a percentage
 * @param amount The original amount
 * @param days The number of days
 * @param percentage The percentage to apply (0-100)
 * @returns An object containing the original amount, discounted amount, and remaining amount
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
  // Calculate the total original amount
  const originalAmount = amount * days;
  // Ensure percentage is between 0 and 100
  const validPercentage = Math.min(100, Math.max(0, percentage));
  // Calculate the discount amount and round to whole number
  const discountedAmount = Math.floor((originalAmount * validPercentage) / 100);
  // Calculate the remaining amount after discount
  const remainingAmount = originalAmount - discountedAmount;
  return {
    originalAmount,
    discountedAmount,
    remainingAmount,
  };
};

/**
 * Calculate the hotel payable and commission amount with correct business logic
 * @param originalAmount - The original total amount before any discounts
 * @param firstDiscountAmount - The amount of the first discount (already calculated)
 * @param customerPaidAmount - The total amount actually paid by customer (after all discounts/coupons)
 * @param couponAmount - The coupon discount amount (optional, defaults to 0)
 * @returns An object with hotelPayable and commissionAmount
 */
export function ourCommission(
  originalAmount: number,
  firstDiscountAmount: number,
  customerPaidAmount: number,
  couponAmount: number = 0
) {
  const hotelPayable = originalAmount - firstDiscountAmount;

  const commissionAmount = customerPaidAmount - hotelPayable - couponAmount;

  return {
    originalAmount,
    hotelPayable,
    commissionAmount,
    customerPaidAmount,
    couponAmount,
  };
}
