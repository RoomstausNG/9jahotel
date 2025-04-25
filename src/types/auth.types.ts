// Authentication related types

// Customer registration data type
export interface CustomerRegistrationData {
  firstname: string;
  lastname: string;
  phone: string;
  email: string;
  password: string;
  referral_code?: string;
}

// Login response type
export interface LoginResponse {
  customer: any; // Using 'any' here as the customer type depends on the database schema
  token: string;
}

// JWT payload type
export interface JwtPayload {
  id: number;
  email: string;
  [key: string]: any; // For any additional claims
}