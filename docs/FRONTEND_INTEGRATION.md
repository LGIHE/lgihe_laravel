# Frontend Integration Guide

## Overview

This guide explains how to connect your frontend application to the LGIHE Backend API.

## Quick Start

### 1. API Base URL

```javascript
const API_BASE_URL = 'http://localhost:8001/api/v1';
```

For production:
```javascript
const API_BASE_URL = process.env.REACT_APP_API_URL || 'https://api.lgihe.org/api/v1';
```

### 2. Environment Variables

Create a `.env` file in your frontend project:

```env
# Development
REACT_APP_API_URL=http://localhost:8001/api/v1

# Production
# REACT_APP_API_URL=https://api.lgihe.org/api/v1
```

## Setup API Client

### Using Axios (Recommended)

```bash
npm install axios
```

Create `src/services/api.js`:

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8001/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Important for Sanctum
});

// Add token to requests
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Handle responses
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### Using Fetch API

Create `src/services/api.js`:

```javascript
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8001/api/v1';

const getAuthHeaders = () => {
  const token = localStorage.getItem('auth_token');
  return {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(token && { 'Authorization': `Bearer ${token}` }),
  };
};

const handleResponse = async (response) => {
  if (!response.ok) {
    if (response.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    const error = await response.json();
    throw new Error(error.message || 'Request failed');
  }
  return response.json();
};

export const api = {
  get: (endpoint) =>
    fetch(`${API_BASE_URL}${endpoint}`, {
      headers: getAuthHeaders(),
    }).then(handleResponse),

  post: (endpoint, data) =>
    fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: getAuthHeaders(),
      body: JSON.stringify(data),
    }).then(handleResponse),

  put: (endpoint, data) =>
    fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'PUT',
      headers: getAuthHeaders(),
      body: JSON.stringify(data),
    }).then(handleResponse),

  delete: (endpoint) =>
    fetch(`${API_BASE_URL}${endpoint}`, {
      method: 'DELETE',
      headers: getAuthHeaders(),
    }).then(handleResponse),
};
```

## API Service Functions

Create `src/services/content.js`:

```javascript
import api from './api';

// News
export const getNews = (params = {}) => {
  const queryString = new URLSearchParams(params).toString();
  return api.get(`/news${queryString ? `?${queryString}` : ''}`);
};

export const getNewsArticle = (slug) => {
  return api.get(`/news/${slug}`);
};

// Events
export const getEvents = (params = {}) => {
  const queryString = new URLSearchParams(params).toString();
  return api.get(`/events${queryString ? `?${queryString}` : ''}`);
};

export const getEvent = (id) => {
  return api.get(`/events/${id}`);
};

// Jobs
export const getJobs = (params = {}) => {
  const queryString = new URLSearchParams(params).toString();
  return api.get(`/jobs${queryString ? `?${queryString}` : ''}`);
};

export const getJob = (id) => {
  return api.get(`/jobs/${id}`);
};

// Tenders
export const getTenders = () => {
  return api.get('/tenders');
};

export const getTender = (id) => {
  return api.get(`/tenders/${id}`);
};

// Research
export const getResearch = () => {
  return api.get('/research');
};

export const getResearchPublication = (id) => {
  return api.get(`/research/${id}`);
};
```

Create `src/services/forms.js`:

```javascript
import api from './api';

// Contact Form
export const submitContactForm = (data) => {
  return api.post('/contact', data);
};

// Application Form
export const submitApplication = (data) => {
  return api.post('/applications', data);
};
```

Create `src/services/auth.js`:

```javascript
import api from './api';

export const login = async (credentials) => {
  const response = await api.post('/auth/login', credentials);
  if (response.data.token) {
    localStorage.setItem('auth_token', response.data.token);
  }
  return response.data;
};

export const logout = async () => {
  try {
    await api.post('/auth/logout');
  } finally {
    localStorage.removeItem('auth_token');
  }
};

export const getCurrentUser = () => {
  return api.get('/auth/me');
};
```

## React Component Examples

### News List Component

```javascript
import React, { useState, useEffect } from 'react';
import { getNews } from '../services/content';

function NewsList() {
  const [news, setNews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchNews = async () => {
      try {
        const response = await getNews({ per_page: 10 });
        setNews(response.data.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchNews();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div className="news-list">
      {news.map((article) => (
        <div key={article.id} className="news-item">
          <h2>{article.title}</h2>
          <p>{article.excerpt}</p>
          <a href={`/news/${article.slug}`}>Read more</a>
        </div>
      ))}
    </div>
  );
}

export default NewsList;
```

### Contact Form Component

```javascript
import React, { useState } from 'react';
import { submitContactForm } from '../services/forms';

function ContactForm() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
  });
  const [submitting, setSubmitting] = useState(false);
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState(null);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      await submitContactForm(formData);
      setSuccess(true);
      setFormData({ name: '', email: '', phone: '', subject: '', message: '' });
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  };

  if (success) {
    return (
      <div className="success-message">
        Thank you! Your inquiry has been submitted successfully.
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        name="name"
        value={formData.name}
        onChange={handleChange}
        placeholder="Your Name"
        required
      />
      <input
        type="email"
        name="email"
        value={formData.email}
        onChange={handleChange}
        placeholder="Your Email"
        required
      />
      <input
        type="tel"
        name="phone"
        value={formData.phone}
        onChange={handleChange}
        placeholder="Your Phone"
      />
      <input
        type="text"
        name="subject"
        value={formData.subject}
        onChange={handleChange}
        placeholder="Subject"
        required
      />
      <textarea
        name="message"
        value={formData.message}
        onChange={handleChange}
        placeholder="Your Message"
        required
      />
      {error && <div className="error">{error}</div>}
      <button type="submit" disabled={submitting}>
        {submitting ? 'Submitting...' : 'Submit'}
      </button>
    </form>
  );
}

export default ContactForm;
```

### Login Component

```javascript
import React, { useState } from 'react';
import { login } from '../services/auth';
import { useNavigate } from 'react-router-dom';

function Login() {
  const [credentials, setCredentials] = useState({ email: '', password: '' });
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await login(credentials);
      console.log('Logged in:', response.user);
      navigate('/dashboard');
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="email"
        value={credentials.email}
        onChange={(e) => setCredentials({ ...credentials, email: e.target.value })}
        placeholder="Email"
        required
      />
      <input
        type="password"
        value={credentials.password}
        onChange={(e) => setCredentials({ ...credentials, password: e.target.value })}
        placeholder="Password"
        required
      />
      {error && <div className="error">{error}</div>}
      <button type="submit" disabled={loading}>
        {loading ? 'Logging in...' : 'Login'}
      </button>
    </form>
  );
}

export default Login;
```

## React Hooks

Create `src/hooks/useNews.js`:

```javascript
import { useState, useEffect } from 'react';
import { getNews } from '../services/content';

export const useNews = (params = {}) => {
  const [news, setNews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchNews = async () => {
      try {
        setLoading(true);
        const response = await getNews(params);
        setNews(response.data.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };

    fetchNews();
  }, [JSON.stringify(params)]);

  return { news, loading, error };
};
```

Usage:

```javascript
function NewsPage() {
  const { news, loading, error } = useNews({ per_page: 10 });

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      {news.map((article) => (
        <NewsCard key={article.id} article={article} />
      ))}
    </div>
  );
}
```

## Testing the Connection

### 1. Test Public Endpoint

```javascript
// In your browser console or a test file
fetch('http://localhost:8001/api/v1/news')
  .then(res => res.json())
  .then(data => console.log(data));
```

### 2. Test Authentication

```javascript
fetch('http://localhost:8001/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@lgihe.org',
    password: 'password'
  })
})
  .then(res => res.json())
  .then(data => console.log('Token:', data.token));
```

## CORS Configuration

The backend is configured to accept requests from:
- `http://localhost:3000`
- `http://localhost:3001`

If your frontend runs on a different port, update the backend's `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://localhost:3001',
    'http://localhost:YOUR_PORT', // Add your port
    env('FRONTEND_URL', 'http://localhost:3000'),
],
```

## Error Handling

### Global Error Handler

```javascript
// src/utils/errorHandler.js
export const handleApiError = (error) => {
  if (error.response) {
    // Server responded with error
    const { status, data } = error.response;
    
    switch (status) {
      case 400:
        return data.errors || 'Invalid request';
      case 401:
        return 'Please login to continue';
      case 403:
        return 'You do not have permission';
      case 404:
        return 'Resource not found';
      case 422:
        return data.errors || 'Validation failed';
      case 500:
        return 'Server error. Please try again later';
      default:
        return data.message || 'An error occurred';
    }
  } else if (error.request) {
    // Request made but no response
    return 'Network error. Please check your connection';
  } else {
    // Something else happened
    return error.message || 'An unexpected error occurred';
  }
};
```

## Best Practices

### 1. Use Environment Variables

```javascript
// ✅ Good
const API_URL = process.env.REACT_APP_API_URL;

// ❌ Bad
const API_URL = 'http://localhost:8001/api/v1';
```

### 2. Handle Loading States

```javascript
// ✅ Good
if (loading) return <Spinner />;
if (error) return <ErrorMessage error={error} />;
return <Content data={data} />;
```

### 3. Cache API Responses

Use React Query or SWR for better caching:

```bash
npm install @tanstack/react-query
```

```javascript
import { useQuery } from '@tanstack/react-query';
import { getNews } from '../services/content';

function NewsList() {
  const { data, isLoading, error } = useQuery({
    queryKey: ['news'],
    queryFn: () => getNews(),
  });

  // ...
}
```

### 4. Validate Forms

```javascript
import { z } from 'zod';

const contactSchema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  phone: z.string().optional(),
  subject: z.string().min(5),
  message: z.string().min(10),
});

// Validate before submitting
try {
  contactSchema.parse(formData);
  await submitContactForm(formData);
} catch (error) {
  // Handle validation errors
}
```

## Troubleshooting

### CORS Errors

If you see CORS errors:
1. Check backend `config/cors.php` includes your frontend URL
2. Ensure `withCredentials: true` in axios config
3. Restart backend server after config changes

### 401 Unauthorized

1. Check token is stored: `localStorage.getItem('auth_token')`
2. Verify token is sent in headers
3. Check token hasn't expired

### Network Errors

1. Verify backend is running: `http://localhost:8001/api/v1/news`
2. Check firewall settings
3. Verify correct API URL in environment variables

## Production Deployment

### Frontend

1. Update `.env.production`:
```env
REACT_APP_API_URL=https://api.lgihe.org/api/v1
```

2. Build:
```bash
npm run build
```

### Backend

1. Update CORS in `config/cors.php`:
```php
'allowed_origins' => [
    'https://lgihe.org',
    'https://www.lgihe.org',
],
```

2. Update Sanctum in `config/sanctum.php`:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'lgihe.org,www.lgihe.org')),
```

## Support

For integration issues:
- Check API Documentation: `API_DOCUMENTATION.md`
- Test endpoints with Postman
- Check browser console for errors
- Verify backend logs: `php artisan pail`

---

**Last Updated**: April 21, 2026
**Backend URL**: http://localhost:8001/api/v1
**Status**: ✅ Ready for Integration
