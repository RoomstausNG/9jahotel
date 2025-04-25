// helpers/pagination.ts
export async function getPaginationData(
  page: number,
  perPage: number,
  totalCount: number
) {
  const totalPages = Math.ceil(totalCount / perPage);

  // Return pagination metadata only if items exceed perPage threshold
  if (totalCount > perPage) {
    return {
      current_page: page,
      per_page: perPage,
      total: totalCount,
      total_pages: totalPages,
    };
  }

  // If no pagination is necessary, return null
  return null;
}

export async function getTotalCustomerCount() {
  // Fetch the total number of customers (example DB query)
  return 100; // Example total count
}
