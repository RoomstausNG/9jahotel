export interface Amenity {
  name: string;
  icon: string;
}

export interface City {
  id: number;
  name: string;
}

export interface ReviewSummary {
  rating_5: number;
  rating_4: number;
  rating_3: number;
  rating_2: number;
  rating_1: number;
}

export interface Reviews {
  total: number;
  averageRating: number;
  summary: ReviewSummary;
}

export interface Hotel {
  id: number;
  name: string;
  code: string;
  status: "active" | "inactive";
  image: string;
  address: string;
  city: City;
  roomPrice: number;
  hallPrice?: number; // Optional based on sample
  originalPrice?: number; // From previous context
  discount?: number; // From previous context
  onlinePercentage?: number;
  nonRefundablePercentage?: number;
  latitude: number | null;
  longitude: number | null;
  distance?: number;
  reviews: Reviews;
  starRating: number;
  isFavourite: boolean;
  amenities: Amenity[];
}
