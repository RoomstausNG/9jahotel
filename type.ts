// Common response types
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: any[];
}

// Pagination types
export interface PaginationOptions {
  page?: number;
  limit?: number;
  sort?: string;
  order?: "asc" | "desc";
}

// Sorting options
export interface SortOptions {
  field: string;
  order: "asc" | "desc";
}

// Filter options
export interface FilterOptions {
  [key: string]: any;
}
