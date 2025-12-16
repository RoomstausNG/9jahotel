<file_path>
9jahotel/EVENTS-README.md
</file_path>

<edit_description>
Create comprehensive README documenting all TV event endpoints and Socket.IO events
</edit_description>

# Hotel TV Event System Documentation

This document describes the TV event system for hotel management, including both HTTP REST endpoints and Socket.IO real-time events for broadcasting messages to smart TVs.

## Overview

The system manages smart TVs in hotel rooms, allowing for real-time broadcasting of check-in welcomes, checkout confirmations, reminders, and general announcements. All endpoints support both HTTP POST requests and Socket.IO events with identical parameters.

## Database Operations

Only minimal database operations are performed:
- TV registration creates/updates smart_tvs records
- TV events (check-in, check-out, reminder) only update the `last_seen_at` timestamp
- No guest information is stored in the TV table - all broadcast data comes from POST request parameters

## API Endpoints

All endpoints start with `/api/events/`.

## Quick Reference

| Action | HTTP Endpoint | Socket Event |
|--------|--------------|--------------|
| Register TV | `POST /tv-registration` | `tv-notification` (type: connected OR registration) |
| Check-in | `POST /check-in` | `tv-notification` (type: check-in) |
| Reminder | `POST /reminder` | `tv-notification` (type: reminder) |
| Checkout | `POST /checkout` | `tv-notification` (type: checkout) |
| Broadcast | `POST /broadcast-all` | `tv-notification` (type: broadcast) |

### Frontend Listener
Listen for `tv-notification` on:
- Room: `smart-tv:<mac_address>` (for specific events)
- Room: `hotel:<hotel_code>` (for broadcasts)

## API Endpoints

### 1. TV Registration
**Endpoint**: `POST /api/events/tv-registration`
**Authentication**: Unprotected

Registers a TV with the system and establishes room connections.

**Request Body**:
```json
{
  "hotelcode": "ZCHS241",
  "roomnumber": "105",
  "macaddress": "AD:6B:BG:TE:KE:F", // Accepts any format: AD6BBGTEKEF, ad-6b-bg-te-ke-f, etc.
  "ipaddress": "173.190.134.125"
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "TV registered successfully",
  "data": {
    "tvId": 123,
    "sanitizedMac": "AD6BBGTEKEF",
    "tvRoom": "smart-tv:AD6BBGTEKEF",
    "hotelRoom": "hotel:ZCHS241"
  }
}
```

### 2. Check-in Event
**Endpoint**: `POST /api/events/check-in`
**Authentication**: Unprotected

Processes guest check-in and broadcasts welcome message.

**Request Body**:
```json
{
  "hotelcode": "ZCHS241",
  "roomnumber": "105",
  "message": "Welcome to Paradise Hotel!",
  "fullname": "John Doe",
  "macaddress": "AD6BBGTEKEF", // Any format accepted and sanitized
  "tvid": "TV001",
  "checkindatetime": "2024-01-15T14:30:00Z",
  "checkoutdatetime": "2024-01-17T11:00:00Z"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Check-in processed successfully",
  "data": {
    "tvId": 123,
    "message": "Welcome, John Doe! Your check-in at 2024-01-15T14:30:00Z is confirmed. Enjoy your stay until 2024-01-17T11:00:00Z."
  }
}
```

### 3. Reminder Event
**Endpoint**: `POST /api/events/reminder`
**Authentication**: Unprotected

Sends checkout reminders to specific TVs.

**Request Body**:
```json
{
  "hotelcode": "ZCHS241",
  "macaddress": "ad-6b-bg-te-ke-f", // Flexible format support
  "reminderType": "checkout_2h",
  "checkoutdatetime": "2024-01-17T11:00:00Z"
}
```

**Reminder Types**:
- `checkout_2h`: 2 hours until checkout
- `checkout_1h`: 1 hour until checkout

**Response**:
```json
{
  "success": true,
  "message": "Reminder sent successfully",
  "data": {
    "tvId": 123,
    "message": "Reminder: You have 2 hours until checkout at 2024-01-17T11:00:00Z."
  }
}
```

### 4. Checkout Event
**Endpoint**: `POST /api/events/checkout`
**Authentication**: Unprotected

Processes guest checkout and broadcasts thank you message.

**Request Body**:
```json
{
  "hotelcode": "ZCHS241",
  "fullname": "John Doe",
  "macaddress": "AD6BBGTEKEF", // Any valid MAC format accepted
  "checkoutdatetime": "2024-01-17T11:00:00Z"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Checkout processed successfully",
  "data": {
    "tvId": 123,
    "message": "Thank you for your stay, John Doe! Checkout completed at 2024-01-17T11:00:00Z."
  }
}
```

### 5. Broadcast All
**Endpoint**: `POST /api/events/broadcast-all`
**Authentication**: Unprotected

Broadcasts a custom message to all TVs in a hotel.

**Request Body**:
```json
{
  "hotelcode": "ZCHS241",
  "message": "Hotel-wide announcement: Pool maintenance today from 2-4 PM"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Broadcast sent successfully",
  "data": {
    "hotelCode": "ZCHS241",
    "message": "Hotel-wide announcement: Pool maintenance today from 2-4 PM",
    "tvCount": 25
  }
}
```

## Socket.IO Events

All HTTP endpoints have corresponding Socket.IO events with identical parameters. Frontend applications can use either HTTP requests or Socket.IO events.

### Client-Side Events (to send)
```javascript
// TV Registration
socket.emit('tv-registration', {
  hotelcode: 'ZCHS241',
  roomnumber: '105',
  macaddress: 'AD:6B:BG:TE:KE:F',
  ipaddress: '173.190.134.125'
});

// Check-in
socket.emit('check-in', {
  // same payload as HTTP
});

// Reminder
socket.emit('reminder', {
  // same payload as HTTP
});

// Checkout
socket.emit('checkout', {
  // same payload as HTTP
});

// Broadcast All
socket.emit('broadcast-all', {
  // same payload as HTTP
});
```

### Response Events (to listen)
The server responds with success/error events for each operation:

```javascript
// Success responses
socket.on('tv-registration-success', (data) => { /* handle success */ });
socket.on('check-in-success', (data) => { /* handle success */ });
socket.on('reminder-success', (data) => { /* handle success */ });
socket.on('checkout-success', (data) => { /* handle success */ });
socket.on('broadcast-all-success', (data) => { /* handle success */ });

// Error responses
socket.on('tv-registration-error', (error) => { /* handle error */ });
socket.on('check-in-error', (error) => { /* handle error */ });
socket.on('reminder-error', (error) => { /* handle error */ });
socket.on('checkout-error', (error) => { /* handle error */ });
socket.on('broadcast-all-error', (error) => { /* handle error */ });
```

### Unified TV Event Listener
All events (check-in, checkout, reminder, broadcast) are now emitted as a single `tv-notification` event. The payload contains a `type` field to distinguish the event.

```javascript
// Listen on TV-specific room (smart-tv:sanitizedMacAddress) OR hotel room
socket.on('tv-notification', (payload) => {
  const { type, data } = payload;
  
  switch (type) {
    case 'registration':
      // data: { hotelcode, roomnumber, macaddress, ipaddress, tvid, status: "active", timestamp }
      console.log(`TV registered: ${data.tvid}`);
      break;

    case 'connected':
      // data: { tvid, status: "active", message }
      console.log(`TV connected: ${data.message}`);
      break;
      
    case 'check-in':
      // data: { hotelcode, roomnumber, message, fullname, checkintime, checkouttime, ... }
      displayWelcomeMessage(data.message);
      break;
      
    case 'reminder':
      // data: { message, checkouttime, ... }
      displayReminder(data.message);
      break;
      
    case 'checkout':
      // data: { message, checkouttime, ... }
      displayCheckoutMessage(data.message);
      break;
      
    case 'broadcast':
      // data: { message, hotelcode, ... }
      displayAnnouncement(data.message);
      break;
  }
});
```

## Validation Rules

All endpoints use Zod schemas for input validation:

- `hotelcode`: Required string
- `roomnumber`: Required string (where applicable)
- `macaddress`: Required, valid MAC address format (XX:XX:XX:XX:XX:XX)
- `ipaddress`: Required, valid IPv4 format (where applicable)
- `fullname`: Required string (where applicable)
- `checkindatetime`/`checkoutdatetime`: Required, valid ISO 8601 datetime strings
- `reminderType`: Must be "checkout_2h" or "checkout_1h"
- `message`: Optional string

## Error Handling

All endpoints return standardized error responses:
```json
{
  "success": false,
  "message": "Error description",
  "errors": { /* validation errors */ }
}
```

## MAC Address Validation

MAC addresses are validated flexibly - any format is accepted (e.g., `AD:6B:BG:TE:KE:F`, `AD6BBGTEKEF`, `ad-6b-bg-te-ke-f`). The system automatically sanitizes them by:
- Removing all non-alphanumeric characters
- Converting to uppercase
- Storing the clean format in the database
- Using the sanitized format in room names and event emissions

### **Unified Event Handling**
```javascript
// Listen on TV-specific room AND hotel room
socket.on('tv-notification', (payload) => {
  const { type, data } = payload;
  console.log(`Received ${type} event:`, data);
  // Handle UI updates based on type
});
```

## Best Practices

1. **TV Registration**: Always register TVs first to establish room connections
2. **Room Management**: TVs automatically join both specific and hotel-wide rooms
3. **Data Source**: All guest information and broadcast data comes from POST request parameters, not database queries
4. **Database Operations**: Only TV registration and last_seen_at updates are performed - no guest data storage
5. **Offline Handling**: System supports queuing messages if TVs are offline
6. **Authentication**: All event endpoints are unprotected for system integration
7. **MAC Address Flexibility**: Any MAC address format is accepted and automatically sanitized
8. **Real-time Updates**: Use Socket.IO events for real-time features, HTTP for one-time operations

## System Flow Examples

### Guest Check-in Process
1. Guest completes booking → PMS sends check-in event
2. System validates hotel/room, updates TV status
3. Welcome message broadcasted to specific TV room
4. TV displays personalized welcome message

### Reminder System
1. Scheduled job identifies upcoming checkouts
2. System sends reminder events 2 hours and 1 hour before checkout
3. Specific TV receives and displays reminder

### Hotel Announcements
1. Hotel staff creates announcement via admin panel
2. System broadcasts to all TVs in hotel via hotel room
3. All connected TVs display the announcement