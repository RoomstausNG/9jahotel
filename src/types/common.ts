import { ValidationTargets } from "hono";

// Validation related types
export type ValidationTarget = keyof ValidationTargets;

// Any other common types used across the application
export type HttpMethod = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';