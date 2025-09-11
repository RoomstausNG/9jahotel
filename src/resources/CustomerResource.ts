// resources/CustomerResource.ts
export class CustomerResource {
  // Transform a single customer object
  static toResource(customer: any): any {
    return {
      id: customer.id,
      firstName: customer.firstname,
      lastName: customer.lastname,
      referralCode: customer.referral_code,
      email: customer.email,
      phone: customer.phone,
      parentId: customer.parent_id,
      avatar: customer.avatar,
      address: customer.address,
      latitude: customer.latitude,
      longitude: customer.longitude,
      tokenVersion: customer.token_version,
      gender: customer.gender,
    };
  }

  // Transform a paginated collection of customers
  static toCollection(
    customers: any[],
    total: number,
    page: number,
    perPage: number
  ): any {
    return {
      data: customers.map((customer) => this.toResource(customer)),
      meta: {
        total,
        page,
        perPage,
        totalPages: Math.ceil(total / perPage),
      },
    };
  }
}
