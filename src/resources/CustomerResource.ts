// resources/CustomerResource.ts
export class CustomerResource {
  // Transform a single customer object
  static toResource(customer: any): any {
    return {
      id: customer.id,
      firstname: customer.firstname,
      lastname: customer.lastname,
      name: `${customer.firstname} ${customer.lastname}`,
      email: customer.email,
      phone: customer.phone,
      gender: customer.gender,
      date_of_birth: customer.date_of_birth,
      avatar: customer.avatar,
      address: customer.address,
      city_id: customer.city_id,
      state_id: customer.state_id,
      country_id: customer.country_id,
      nationality: customer.nationality,
      nok_name: customer.nok_name,
      nok_phone: customer.nok_phone,
      nok_email: customer.nok_email,
      id_type: customer.id_type,
      id_number: customer.id_number,
      referral_code: customer.referral_code,
      location: customer.location,
      latitude: customer.latitude,
      longitude: customer.longitude,
      created_at: customer.created_at,
      updated_at: customer.updated_at,
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
