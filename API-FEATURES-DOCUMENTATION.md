# Hotel Search API - New Features Documentation

## Updated Features

### 1. **Sorting Options**
- `sortBy`: Sort hotels by specific fields
  - `room_lowest_price`: Sort by room price
  - `star_rating`: Sort by hotel star rating  
  - `distance`: Sort by distance from user location
  - `name`: Sort by hotel name (default)

- `sortOrder`: Order direction
  - `asc`: Ascending order (default)
  - `desc`: Descending order

### 2. **Price Filtering**
- `minPrice`: Minimum room price filter (optional)
- `maxPrice`: Maximum room price filter (optional)
- Based on `room_lowest_price` column

### 3. **Custom Pagination**
- `perpage`: Number of results per page (1-100, default: 24)
- Replaces fixed pagination limits

### 4. **Distance Sorting**
- Requires `latitude` and `longitude` parameters
- Calculates distance from user location
- Returns hotels sorted by proximity

## API Examples

### Basic City Search with Sorting
```
GET /api/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=room_lowest_price&sortOrder=asc
```

### Price Range Filtering
```
GET /api/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&minPrice=5000&maxPrice=20000
```

### Distance-based Sorting
```
GET /api/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=distance&latitude=6.5244&longitude=3.3792
```

### Custom Pagination
```
GET /api/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&page=1&perpage=50
```

### Combined Parameters
```
GET /api/hotels/city/1?checkIn=2025-07-10&checkOut=2025-07-15&sortBy=star_rating&sortOrder=desc&minPrice=10000&maxPrice=50000&perpage=20
```

## Response Format

The response includes the distance field when user coordinates are provided:

```json
{
  "success": true,
  "message": "Hotels for city fetched successfully",
  "data": {
    "hotels": [
      {
        "id": 1,
        "name": "Luxury Hotel",
        "roomPrice": 15000,
        "starRating": 5,
        "distance": 2.5,
        "image": "hotel-image.jpg",
        "address": "123 Hotel Street",
        "isFavourite": false
      }
    ],
    "meta": {
      "pagination": {
        "total": 100,
        "count": 20,
        "per_page": 20,
        "current_page": 1,
        "total_pages": 5,
        "has_more_pages": true
      }
    }
  }
}
```

## Validation Rules

1. **Date Validation**:
   - `checkIn` must not be in the past
   - `checkOut` must be after `checkIn`

2. **Price Validation**:
   - `minPrice` and `maxPrice` must be ≥ 0
   - `minPrice` cannot be greater than `maxPrice`

3. **Pagination Validation**:
   - `page` must be ≥ 1
   - `perpage` must be between 1 and 100

4. **Distance Sorting**:
   - Requires both `latitude` and `longitude`
   - Coordinates must be within valid ranges

5. **Sorting Validation**:
   - `sortBy` must be one of the allowed values
   - `sortOrder` must be 'asc' or 'desc'

## Nearby Hotels API

The nearby hotels endpoint (`/api/hotels/nearby`) also supports all these features:

```
GET /api/hotels/nearby?lat=6.5244&long=3.3792&checkIn=2025-07-10&checkOut=2025-07-15&sortBy=room_lowest_price&minPrice=5000&maxPrice=25000&perpage=30
```

## Caching

The system automatically caches results based on all parameters to improve performance. Cache keys include:
- City ID
- Pagination settings
- Search terms
- Sorting options
- Price filters

Cache is automatically invalidated when hotel data changes.

## Error Handling

The API returns detailed validation errors:

```json
{
  "success": false,
  "message": "Validation error",
  "data": {
    "minPrice": ["Minimum price cannot be greater than maximum price"],
    "sortBy": ["Latitude and longitude are required when sorting by distance"]
  }
}
```
