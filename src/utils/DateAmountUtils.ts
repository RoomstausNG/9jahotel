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
