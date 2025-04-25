export interface Hotel {
  id: number | string;
  name: string;
  image: string;
  images?: string[];
  code?: string;
  latitude?: number | null;
  longitude?: number | null;
  address: string;
  city?: {
    id: string;
    name: string;
  };
  description?: string;
  amenities?: Amenity[];
  onlinePercentage?: number;
  nonRefundablePercentage?: number;
  roomPrice: string | number;
  hallPrice: string | number;
  status: string;
  reviews?: Review[];
  reviewCount?: number;
  distance?: number;
  averageRating?: number;
  starRating: number | null;
  isFavourite?: boolean;
}

export interface Amenity {
  name: string;
  icon: string;
}

export interface Review {
  id: number;
  customer: {
    id: number;
    name: number;
  };
  avatar?: string;
  star: number;
  date: string;
  review: string;
}
