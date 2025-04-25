import { cities } from "../database/migrations/schema";

interface City {
  id: number | string;
  name: string;
  state: string;
  hotel?: number;
}

export class CityResource {
  // Transform a single city with optional field selection
  static toResource(city: City, fields?: string[]) {
    // Default resource shape
    const resource = {
      id: city.id,
      name: `${city.name}, ${city.state} state  (${city.hotel} hotels)`,
    };

    // If specific fields are requested, filter to only those fields
    if (fields && fields.length > 0) {
      return Object.fromEntries(
        Object.entries(resource).filter(([key]) => fields.includes(key))
      );
    }

    return resource;
  }

  // Transform a collection with pagination
  static toCollection(
    cities: City[],
    total: number,
    page: number,
    perPage: number,
    fields?: string[]
  ) {
    // Map citys to resources
    const mappedCities = cities.map((city) => this.toResource(city, fields));

    // Base response without pagination
    const response: any = {
      cities: mappedCities,
    };

    // Only include pagination metadata if total exceeds perPage
    if (total > perPage) {
      const lastPage = Math.ceil(total / perPage);
      const hasMorePages = page < lastPage;
      const isFirstPage = page === 1;

      response.meta = {
        pagination: {
          total,
          count: cities.length,
          per_page: perPage,
          current_page: page,
          total_pages: lastPage,
          has_more_pages: hasMorePages,
          first_page: isFirstPage,
          last_page: page === lastPage,
          next_page: hasMorePages ? page + 1 : null,
          prev_page: page > 1 ? page - 1 : null,
        },
      };
    }

    return response;
  }

  // Method for detailed city profile (when more data is needed)
  static toDetailedResource(city: City) {
    return {
      ...this.toResource(city),
      //   countryId: city.country_id,
    };
  }
}
