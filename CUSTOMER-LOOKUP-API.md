# Customer Lookup API Documentation

## Overview
This document describes the customer lookup endpoints that allow checking if a customer exists by email or phone number. These endpoints are **not protected by authentication middleware** and can be called publicly.

## Endpoints

### 1. Check Customer Exists (Primary Endpoint)
**POST** `/api/customers/check-exists`

This is the main endpoint for checking if a customer exists by email or phone number.

#### Request Body
```json
{
  "identifier": "customer@example.com"
}
```

#### Parameters
- `identifier` (string, required): Email address or phone number to search for

#### Response - Customer Found (200 OK)
```json
{
  "success": true,
  "message": "Customer found successfully",
  "data": {
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "userName": "johndoe",
    "email": "customer@example.com",
    "phone": "+2348123456789",
    "avatar": "https://example.com/avatar.jpg",
    "designation": null,
    "departmentId": null,
    "createdAt": "2024-01-01T00:00:00.000Z",
    "updatedAt": "2024-01-01T00:00:00.000Z"
  }
}
```

#### Response - Customer Not Found (404 Not Found)
```json
{
  "success": false,
  "message": "Customer not found",
  "data": null
}
```

#### Response - Invalid Input (400 Bad Request)
```json
{
  "success": false,
  "message": "Identifier (email or phone) is required",
  "data": null
}
```

### 2. Find Customer by Email (Alternative Endpoint)
**GET** `/api/customers/find-by-email?email=customer@example.com`

#### Query Parameters
- `email` (string, required): Email address to search for

#### Response
Same as the primary endpoint above.

### 3. Find Customer by Phone (Alternative Endpoint)
**GET** `/api/customers/find-by-phone?phone=%2B2348123456789`

#### Query Parameters
- `phone` (string, required): Phone number to search for (URL encoded)

#### Response
Same as the primary endpoint above.

### 4. Get Customer by ID (Protected Endpoint)
**GET** `/api/customers/{id}`

#### Path Parameters
- `id` (integer, required): Customer ID

#### Response
Same customer data structure as above.

## Usage Examples

### JavaScript/TypeScript
```javascript
// Using fetch API
async function checkCustomerExists(identifier) {
  try {
    const response = await fetch('/api/customers/check-exists', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        identifier: identifier
      }),
    });

    const result = await response.json();
    
    if (response.ok) {
      console.log('Customer found:', result.data);
      return result.data;
    } else {
      console.log('Customer not found');
      return null;
    }
  } catch (error) {
    console.error('Error checking customer:', error);
    return null;
  }
}

// Usage
const customer = await checkCustomerExists('customer@example.com');
if (customer) {
  console.log('Customer ID:', customer.id);
  console.log('Customer Name:', customer.name);
}
```

### cURL Examples
```bash
# Check customer exists by email
curl -X POST http://localhost:3000/api/customers/check-exists \
  -H "Content-Type: application/json" \
  -d '{"identifier": "customer@example.com"}'

# Check customer exists by phone
curl -X POST http://localhost:3000/api/customers/check-exists \
  -H "Content-Type: application/json" \
  -d '{"identifier": "+2348123456789"}'

# Find by email (GET)
curl "http://localhost:3000/api/customers/find-by-email?email=customer@example.com"

# Find by phone (GET)
curl "http://localhost:3000/api/customers/find-by-phone?phone=%2B2348123456789"
```

## Integration with Booking System

The customer lookup endpoints are designed to work seamlessly with the booking system:

1. **Before creating a booking**, call the lookup endpoint to check if the customer exists
2. **If customer exists**, use their ID for the booking
3. **If customer doesn't exist**, create a new customer first, then use the new ID for the booking

### Example Integration Flow
```javascript
async function createBookingWithCustomerLookup(bookingData) {
  // Step 1: Check if customer exists
  const existingCustomer = await checkCustomerExists(bookingData.email);
  
  let customerId;
  if (existingCustomer) {
    // Use existing customer
    customerId = existingCustomer.id;
    console.log('Using existing customer:', existingCustomer.name);
  } else {
    // Create new customer
    const newCustomer = await createCustomer(bookingData);
    customerId = newCustomer.id;
    console.log('Created new customer:', newCustomer.name);
  }
  
  // Step 2: Create booking with customer ID
  const booking = await createBooking({
    ...bookingData,
    customerId: customerId
  });
  
  return booking;
}
```

## Error Handling

All endpoints return consistent error responses:

### Validation Errors (400 Bad Request)
```json
{
  "success": false,
  "message": "Identifier (email or phone) is required",
  "data": null
}
```

### Not Found Errors (404 Not Found)
```json
{
  "success": false,
  "message": "Customer not found",
  "data": null
}
```

### Server Errors (500 Internal Server Error)
```json
{
  "success": false,
  "message": "Internal server error while checking customer",
  "data": null
}
```

## Performance Considerations

- **Database Indexing**: Ensure email and phone fields are indexed for fast lookups
- **Caching**: Consider implementing Redis caching for frequently looked up customers
- **Rate Limiting**: Implement rate limiting if needed to prevent abuse
- **Input Sanitization**: All inputs are trimmed and validated

## Security Notes

- **No Authentication Required**: These endpoints are public and don't require authentication
- **Input Validation**: All inputs are validated and sanitized
- **No Sensitive Data**: Customer passwords and sensitive data are never returned
- **Rate Limiting**: Consider implementing rate limiting for production use

## Testing

Use the provided test script to verify the endpoints:

```bash
node test-customer-lookup.js
```

The test script will:
- Test customer lookup by email
- Test customer lookup by phone
- Test input validation
- Test error handling
- Verify all endpoint variants

## Status Codes

- **200 OK**: Customer found successfully
- **400 Bad Request**: Invalid input or missing parameters
- **404 Not Found**: Customer not found
- **500 Internal Server Error**: Server error occurred

## Rate Limiting Recommendations

For production use, consider implementing rate limiting:
- **Per IP**: 100 requests per minute
- **Per identifier**: 10 requests per minute for the same email/phone
- **Global**: 1000 requests per minute across all endpoints
