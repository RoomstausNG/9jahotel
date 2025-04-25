// resources/CustomerResource.ts
import { customers } from "../models/customers";

export class CustomerResource {
  // Transform a single customer object
  static toResource(customer: any): any {
    return {
      id: customer.id,
      name: customer.name,
      email: customer.email,
      created_at: customer.created_at,
      updated_at: customer.updated_at,
    };
  }

  // Transform a paginated collection of customers
  static toCollection(customers: any[], pagination: any): any {
    return {
      data: customers.map((customer) => this.toResource(customer)),
      meta: pagination, // Use 'meta' for pagination info (Laravel convention)
    };
  }
}
