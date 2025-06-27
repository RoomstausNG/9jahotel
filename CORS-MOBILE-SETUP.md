# CORS Configuration for Mobile Apps & Tauri

This project has been configured to support CORS requests from mobile applications and Tauri desktop apps using Hono.js built-in CORS middleware.

## Configuration

The CORS configuration is located in `src/config/cors.ts` and includes:

### Allowed Origins

- Frontend URL (from `FRONTEND_URL` environment variable)
- Local development URLs (`localhost:3000`, `localhost:3001`, etc.)
- Mobile app schemes:
  - `capacitor://localhost` (for Capacitor apps)
  - `ionic://localhost` (for Ionic apps)
  - `tauri://localhost` (for Tauri desktop apps)
  - `https://tauri.localhost` (for Tauri apps with secure context)
  - `file://` (for hybrid mobile apps)
  - Custom origins from `MOBILE_APP_ORIGINS` environment variable

### Allowed Methods

- GET, POST, PUT, DELETE, PATCH, OPTIONS

### Allowed Headers

- Standard headers: `Content-Type`, `Authorization`, `Accept`, `Origin`
- Custom headers for mobile apps: `X-Device-Type`, `X-App-Version`
- Additional headers: `X-Requested-With`, `Cache-Control`, `X-File-Name`

## Environment Variables

Add these to your `.env` file:

```env
# CORS Configuration
FRONTEND_URL="http://localhost:3000"
MOBILE_APP_ORIGINS="capacitor://localhost,ionic://localhost,tauri://localhost,https://tauri.localhost,file://,http://localhost:8100"
NODE_ENV="development"
```

## Mobile App Integration

### For Tauri Desktop Apps

```javascript
// Tauri apps typically use tauri://localhost as origin
// Make sure your tauri.conf.json allows the API domain
const response = await fetch("http://your-api-domain.com/api/endpoint", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Authorization: "Bearer " + token,
    "X-Device-Type": "desktop",
    "X-App-Version": "1.0.0",
  },
  body: JSON.stringify(data),
});
```

**Important Tauri Configuration:**
Add to your `tauri.conf.json`:

```json
{
  "tauri": {
    "security": {
      "csp": "default-src 'self'; connect-src 'self' http://localhost:3003 http://your-api-domain.com;"
    },
    "allowlist": {
      "http": {
        "all": true,
        "request": true
      }
    }
  }
}
```

### For React Native Apps

```javascript
// API calls will work automatically with fetch
const response = await fetch("http://your-api-domain.com/api/endpoint", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    Authorization: "Bearer " + token,
    "X-Device-Type": "mobile",
    "X-App-Version": "1.0.0",
  },
  body: JSON.stringify(data),
});
```

### For Ionic/Capacitor Apps

```javascript
// The app will automatically use capacitor://localhost as origin
const response = await fetch("http://your-api-domain.com/api/endpoint", {
  method: "GET",
  headers: {
    "Content-Type": "application/json",
    Authorization: "Bearer " + token,
  },
});
```

### For Flutter Apps with WebView

```dart
// Ensure your WebView includes proper origin headers
// The CORS will handle file:// origins automatically
```

## Development vs Production

- **Development**: All origins are allowed for easier testing
- **Production**: Only specified origins in the configuration are allowed

## Testing CORS

1. Start your server: `bun run dev`
2. Open `cors-test.html` in a browser
3. Click "Test API Call" to verify CORS is working

## Common Mobile App CORS Issues

1. **Origin not set**: Mobile apps might not send an origin header
2. **Custom schemes**: Apps using `capacitor://` or `ionic://` schemes
3. **File protocol**: Hybrid apps using `file://` protocol
4. **Credentials**: Some mobile apps need `credentials: true`
5. **Tauri CSP**: Tauri apps need proper Content Security Policy configuration
6. **Tauri allowlist**: Tauri requires HTTP requests to be explicitly allowed

## Tauri-Specific CORS Configuration

### Required tauri.conf.json settings:

```json
{
  "tauri": {
    "security": {
      "csp": "default-src 'self'; connect-src 'self' http://localhost:3003 http://your-api-domain.com https://your-api-domain.com;"
    },
    "allowlist": {
      "http": {
        "all": true,
        "request": true,
        "scope": ["http://localhost:3003/**", "http://your-api-domain.com/**"]
      }
    }
  }
}
```

### Tauri Development vs Production:

- **Development**: Use `http://localhost:3003` in CSP and scope
- **Production**: Use your actual API domain in CSP and scope

The configuration handles all these cases automatically.

## Troubleshooting

If you're still experiencing CORS issues:

1. Check the browser/app console for specific CORS errors
2. Verify your environment variables are set correctly
3. For production, ensure your domain is in the allowed origins
4. For mobile apps, check if your app framework requires specific CORS settings

## Security Notes

- In production, avoid using `*` for origins
- Regularly review and update allowed origins
- Consider implementing API keys for additional security
- Monitor for unusual cross-origin requests
