# LGIHE Backend API Documentation

## Base URL

```
Development: http://localhost:8001/api/v1
Production: https://api.lgihe.org/api/v1
```

## Authentication

The API uses Laravel Sanctum for authentication. Protected endpoints require a bearer token.

### Getting a Token

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@lgihe.org",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "System Administrator",
    "email": "admin@lgihe.org",
    "roles": ["Super Admin"],
    "permissions": [...]
  },
  "token": "1|abc123..."
}
```

### Using the Token

Include the token in the Authorization header:

```bash
Authorization: Bearer {token}
```

## Public Endpoints

### News

#### List All News
```bash
GET /api/v1/news
```

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 12)

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Welcome to LGIHE",
      "slug": "welcome-to-lgihe",
      "excerpt": "Brief description...",
      "content": "<p>Full content...</p>",
      "featured_image": "path/to/image.jpg",
      "category": "announcement",
      "status": "published",
      "published_at": "2026-04-21T08:00:00.000000Z",
      "created_at": "2026-04-21T08:00:00.000000Z",
      "creator": {
        "id": 1,
        "name": "Admin User"
      }
    }
  ],
  "total": 10,
  "per_page": 12,
  "current_page": 1,
  "last_page": 1
}
```

#### Get Single News Article
```bash
GET /api/v1/news/{slug}
```

**Response:**
```json
{
  "id": 1,
  "title": "Welcome to LGIHE",
  "slug": "welcome-to-lgihe",
  "excerpt": "Brief description...",
  "content": "<p>Full content...</p>",
  "featured_image": "path/to/image.jpg",
  "category": "announcement",
  "status": "published",
  "published_at": "2026-04-21T08:00:00.000000Z",
  "created_at": "2026-04-21T08:00:00.000000Z",
  "creator": {
    "id": 1,
    "name": "Admin User"
  }
}
```

### Events

#### List All Events
```bash
GET /api/v1/events
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page (default: 12)
- `upcoming` (optional): Filter upcoming events only

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Orientation Day",
      "slug": "orientation-day",
      "description": "Welcome new students",
      "content": "<p>Full details...</p>",
      "location": "Maseru",
      "venue": "Main Campus",
      "start_date": "2026-05-01T09:00:00.000000Z",
      "end_date": "2026-05-01T17:00:00.000000Z",
      "featured_image": "path/to/image.jpg",
      "category": "orientation",
      "status": "published"
    }
  ],
  "total": 5
}
```

#### Get Single Event
```bash
GET /api/v1/events/{id}
```

### Job Listings

#### List All Jobs
```bash
GET /api/v1/jobs
```

**Query Parameters:**
- `page` (optional): Page number
- `per_page` (optional): Items per page (default: 12)
- `active` (optional): Filter active jobs only

**Response:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Lecturer - Computer Science",
      "slug": "lecturer-computer-science",
      "description": "<p>Job description...</p>",
      "requirements": "<p>Requirements...</p>",
      "responsibilities": "<p>Responsibilities...</p>",
      "location": "Maseru",
      "employment_type": "full-time",
      "salary_range": "M15,000 - M25,000",
      "application_deadline": "2026-05-31",
      "status": "active",
      "published_at": "2026-04-21T08:00:00.000000Z"
    }
  ],
  "total": 3
}
```

#### Get Single Job
```bash
GET /api/v1/jobs/{id}
```

### Tenders

#### List All Tenders
```bash
GET /api/v1/tenders
```

#### Get Single Tender
```bash
GET /api/v1/tenders/{id}
```

### Research

#### List All Research Publications
```bash
GET /api/v1/research
```

#### Get Single Research Publication
```bash
GET /api/v1/research/{id}
```

## Form Submissions

### Contact Form

#### Submit Contact Inquiry
```bash
POST /api/v1/contact
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+266 1234 5678",
  "subject": "Inquiry about programmes",
  "message": "I would like to know more about..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Your inquiry has been submitted successfully. We will get back to you soon.",
  "inquiry": {
    "id": 123,
    "reference": "INQ-2026-000123"
  }
}
```

### Application Form

#### Submit Application
```bash
POST /api/v1/applications
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+266 1234 5678",
  "date_of_birth": "2000-01-01",
  "gender": "male",
  "nationality": "Lesotho",
  "id_number": "12345678",
  "address": "123 Main St",
  "city": "Maseru",
  "district": "Maseru",
  "country": "Lesotho",
  "programme_choice_1": "Computer Science",
  "programme_choice_2": "Information Technology",
  "intake_year": "2026",
  "study_mode": "full-time",
  "kin_name": "Jane Doe",
  "kin_relationship": "Mother",
  "kin_phone": "+266 8765 4321",
  "kin_email": "jane@example.com",
  "additional_info": "Additional information..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Your application has been submitted successfully.",
  "application": {
    "id": 456,
    "reference_no": "LGI-2026-000456"
  }
}
```

## Analytics Endpoints

### Track Event
```bash
POST /api/v1/analytics/event
Content-Type: application/json

{
  "event_name": "page_view",
  "event_data": {
    "page": "/about",
    "referrer": "https://google.com"
  },
  "user_agent": "Mozilla/5.0...",
  "ip_address": "192.168.1.1"
}
```

### Log Error
```bash
POST /api/v1/analytics/error
Content-Type: application/json

{
  "error_message": "Failed to load resource",
  "error_stack": "Error stack trace...",
  "page_url": "/contact",
  "user_agent": "Mozilla/5.0..."
}
```

### Track Page Load
```bash
POST /api/v1/analytics/pageload
Content-Type: application/json

{
  "page_url": "/about",
  "load_time": 1234,
  "user_agent": "Mozilla/5.0..."
}
```

## Protected Endpoints (Require Authentication)

### Authentication

#### Logout
```bash
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

#### Get Current User
```bash
GET /api/v1/auth/me
Authorization: Bearer {token}
```

**Response:**
```json
{
  "id": 1,
  "name": "System Administrator",
  "email": "admin@lgihe.org",
  "roles": ["Super Admin"],
  "permissions": ["manage-content", "manage-users", ...]
}
```

## Admin Endpoints (Require Authentication)

All admin endpoints require authentication and appropriate permissions.

### News Management

#### List All News (Including Drafts)
```bash
GET /api/v1/admin/news
Authorization: Bearer {token}
```

#### Create News
```bash
POST /api/v1/admin/news
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "New Article",
  "slug": "new-article",
  "excerpt": "Brief description",
  "content": "<p>Full content</p>",
  "category": "announcement",
  "status": "draft",
  "published_at": "2026-04-21T08:00:00Z"
}
```

#### Update News
```bash
PUT /api/v1/admin/news/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Updated Title",
  "status": "published"
}
```

#### Delete News
```bash
DELETE /api/v1/admin/news/{id}
Authorization: Bearer {token}
```

### Application Management

#### List All Applications
```bash
GET /api/v1/admin/applications
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status (pending, under_review, accepted, rejected)
- `intake_year` (optional): Filter by intake year
- `page` (optional): Page number

#### Get Single Application
```bash
GET /api/v1/admin/applications/{id}
Authorization: Bearer {token}
```

#### Update Application Status
```bash
PUT /api/v1/admin/applications/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "accepted",
  "notes": "Congratulations! You have been accepted."
}
```

#### Add Note to Application
```bash
POST /api/v1/admin/applications/{id}/notes
Authorization: Bearer {token}
Content-Type: application/json

{
  "note": "Contacted applicant via phone."
}
```

### Contact Inquiry Management

#### List All Inquiries
```bash
GET /api/v1/admin/inquiries
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status (new, in_progress, resolved)
- `assigned_to` (optional): Filter by assigned user ID

#### Get Single Inquiry
```bash
GET /api/v1/admin/inquiries/{id}
Authorization: Bearer {token}
```

#### Update Inquiry Status
```bash
PUT /api/v1/admin/inquiries/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "resolved",
  "assigned_to": 2
}
```

#### Delete Inquiry
```bash
DELETE /api/v1/admin/inquiries/{id}
Authorization: Bearer {token}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "You do not have permission to perform this action."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found."
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "An error occurred while processing your request."
}
```

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **Public endpoints**: 60 requests per minute
- **Authenticated endpoints**: 120 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1619712000
```

## CORS Configuration

The API supports CORS for the following origins:
- `http://localhost:3000`
- `http://localhost:3001`
- Production frontend URL (configured via `FRONTEND_URL` env variable)

## Testing the API

### Using cURL

```bash
# Get news
curl http://localhost:8001/api/v1/news

# Login
curl -X POST http://localhost:8001/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@lgihe.org","password":"password"}'

# Get authenticated user
curl http://localhost:8001/api/v1/auth/me \
  -H "Authorization: Bearer {token}"
```

### Using Postman

1. Import the API collection (if available)
2. Set base URL: `http://localhost:8001/api/v1`
3. For protected endpoints, add Authorization header with Bearer token

### Using JavaScript (Fetch)

```javascript
// Get news
fetch('http://localhost:8001/api/v1/news')
  .then(response => response.json())
  .then(data => console.log(data));

// Login
fetch('http://localhost:8001/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'admin@lgihe.org',
    password: 'password'
  })
})
  .then(response => response.json())
  .then(data => console.log(data.token));

// Get authenticated user
fetch('http://localhost:8001/api/v1/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
})
  .then(response => response.json())
  .then(data => console.log(data));
```

## Frontend Integration

### React Example

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8001/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Get news
export const getNews = () => api.get('/news');

// Submit application
export const submitApplication = (data) => api.post('/applications', data);

// Login
export const login = (credentials) => api.post('/auth/login', credentials);
```

## Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `204 No Content`: Request successful, no content to return
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## Support

For API support or questions:
- **Email**: it@lgihe.org
- **Documentation**: See project README
- **Issues**: Report on GitHub

---

**API Version**: 1.0
**Last Updated**: April 21, 2026
**Status**: ✅ Fully Operational
