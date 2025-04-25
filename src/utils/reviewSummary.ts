import { Review } from "../types/models";
export function buildReviewSummary(reviews: Review[]): Record<string, number> {
  return Object.fromEntries(
    [5, 4, 3, 2, 1].map((star) => [
      `rating_${star}`,
      reviews.filter((r) => r.star === star).length,
    ])
  );
}
