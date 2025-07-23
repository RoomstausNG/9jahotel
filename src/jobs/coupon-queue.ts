// src/jobs/coupon-queue.ts
// Job queue for coupon usage increment

import { db } from "../config/database";
import { coupons } from "../database/migrations/schema";
import { sql } from "drizzle-orm";

/**
 * Queue a job to increment coupon usage
 * @param couponId - The coupon ID to increment usage for
 */
export async function queueCouponUsageIncrementJob(couponId: number) {
  try {
    await db
      .update(coupons)
      .set({ usage_count: sql`usage_count + 1` })
      .where(sql`id = ${couponId}`);
    console.log(`Coupon usage incremented for couponId ${couponId}`);
  } catch (err) {
    console.error("Failed to increment coupon usage:", err);
    throw err;
  }
}
