# Frontend Profile Implementation Example

This document provides examples of how to implement profile management in a frontend application using the profile API endpoints.

## React/JavaScript Example

### Profile Service

```javascript
// services/profileService.js
class ProfileService {
    constructor(baseURL = '/api/v1', getToken) {
        this.baseURL = baseURL;
        this.getToken = getToken; // Function that returns the auth token
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const token = this.getToken();
        
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`,
                ...options.headers,
            },
            ...options,
        };

        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }

        return data;
    }

    async getProfile() {
        return this.request('/profile');
    }

    async updateProfile(profileData) {
        return this.request('/profile', {
            method: 'PUT',
            body: JSON.stringify(profileData),
        });
    }

    async changePassword(passwordData) {
        return this.request('/profile/change-password', {
            method: 'POST',
            body: JSON.stringify(passwordData),
        });
    }

    async deleteAccount(password) {
        return this.request('/profile', {
            method: 'DELETE',
            body: JSON.stringify({ password }),
        });
    }
}

export default ProfileService;
```

### React Profile Component

```jsx
// components/Profile.jsx
import React, { useState, useEffect } from 'react';
import ProfileService from '../services/profileService';

const Profile = ({ getToken, onLogout }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');
    
    const profileService = new ProfileService('/api/v1', getToken);

    useEffect(() => {
        loadProfile();
    }, []);

    const loadProfile = async () => {
        try {
            setLoading(true);
            const response = await profileService.getProfile();
            setUser(response.user);
        } catch (err) {
            setError('Failed to load profile');
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <div>Loading...</div>;
    if (!user) return <div>No user data</div>;

    return (
        <div className="profile-container">
            <h2>My Profile</h2>
            
            {error && <div className="alert alert-error">{error}</div>}
            {success && <div className="alert alert-success">{success}</div>}
            
            <ProfileForm 
                user={user} 
                onUpdate={loadProfile}
                profileService={profileService}
                setError={setError}
                setSuccess={setSuccess}
            />
            
            <PasswordChangeForm 
                profileService={profileService}
                setError={setError}
                setSuccess={setSuccess}
                onLogout={onLogout}
            />
            
            <AccountDeletionForm 
                profileService={profileService}
                setError={setError}
                onLogout={onLogout}
            />
        </div>
    );
};

// Profile Update Form
const ProfileForm = ({ user, onUpdate, profileService, setError, setSuccess }) => {
    const [formData, setFormData] = useState({
        name: user.name,
        email: user.email,
    });
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError('');
        setSuccess('');

        try {
            await profileService.updateProfile(formData);
            setSuccess('Profile updated successfully');
            onUpdate();
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="profile-form">
            <h3>Update Profile</h3>
            
            <div className="form-group">
                <label htmlFor="name">Name:</label>
                <input
                    type="text"
                    id="name"
                    value={formData.name}
                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                    required
                />
            </div>
            
            <div className="form-group">
                <label htmlFor="email">Email:</label>
                <input
                    type="email"
                    id="email"
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                    required
                />
            </div>
            
            <button type="submit" disabled={submitting}>
                {submitting ? 'Updating...' : 'Update Profile'}
            </button>
        </form>
    );
};

// Password Change Form
const PasswordChangeForm = ({ profileService, setError, setSuccess, onLogout }) => {
    const [passwordData, setPasswordData] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
        revoke_all_tokens: false,
    });
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError('');
        setSuccess('');

        try {
            const response = await profileService.changePassword(passwordData);
            setSuccess(response.message);
            setPasswordData({
                current_password: '',
                password: '',
                password_confirmation: '',
                revoke_all_tokens: false,
            });
            
            // If all tokens were revoked, logout the user
            if (passwordData.revoke_all_tokens) {
                setTimeout(() => onLogout(), 2000);
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="password-form">
            <h3>Change Password</h3>
            
            <div className="form-group">
                <label htmlFor="current_password">Current Password:</label>
                <input
                    type="password"
                    id="current_password"
                    value={passwordData.current_password}
                    onChange={(e) => setPasswordData({...passwordData, current_password: e.target.value})}
                    required
                />
            </div>
            
            <div className="form-group">
                <label htmlFor="password">New Password:</label>
                <input
                    type="password"
                    id="password"
                    value={passwordData.password}
                    onChange={(e) => setPasswordData({...passwordData, password: e.target.value})}
                    required
                />
            </div>
            
            <div className="form-group">
                <label htmlFor="password_confirmation">Confirm New Password:</label>
                <input
                    type="password"
                    id="password_confirmation"
                    value={passwordData.password_confirmation}
                    onChange={(e) => setPasswordData({...passwordData, password_confirmation: e.target.value})}
                    required
                />
            </div>
            
            <div className="form-group">
                <label>
                    <input
                        type="checkbox"
                        checked={passwordData.revoke_all_tokens}
                        onChange={(e) => setPasswordData({...passwordData, revoke_all_tokens: e.target.checked})}
                    />
                    Log out all other devices
                </label>
            </div>
            
            <button type="submit" disabled={submitting}>
                {submitting ? 'Changing...' : 'Change Password'}
            </button>
        </form>
    );
};

// Account Deletion Form
const AccountDeletionForm = ({ profileService, setError, onLogout }) => {
    const [password, setPassword] = useState('');
    const [confirmDelete, setConfirmDelete] = useState(false);
    const [submitting, setSubmitting] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!confirmDelete) {
            setError('Please confirm that you want to delete your account');
            return;
        }

        setSubmitting(true);
        setError('');

        try {
            await profileService.deleteAccount(password);
            alert('Account deleted successfully');
            onLogout();
        } catch (err) {
            setError(err.message);
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="delete-form">
            <h3>Delete Account</h3>
            <p className="warning">This action cannot be undone!</p>
            
            <div className="form-group">
                <label htmlFor="delete_password">Enter your password to confirm:</label>
                <input
                    type="password"
                    id="delete_password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                />
            </div>
            
            <div className="form-group">
                <label>
                    <input
                        type="checkbox"
                        checked={confirmDelete}
                        onChange={(e) => setConfirmDelete(e.target.checked)}
                    />
                    I understand this action cannot be undone
                </label>
            </div>
            
            <button 
                type="submit" 
                disabled={submitting || !confirmDelete}
                className="btn-danger"
            >
                {submitting ? 'Deleting...' : 'Delete Account'}
            </button>
        </form>
    );
};

export default Profile;
```

### CSS Styles

```css
/* styles/profile.css */
.profile-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.profile-form, .password-form, .delete-form {
    background: #f9f9f9;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input[type="checkbox"] {
    margin-right: 8px;
}

button {
    background: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

button:hover {
    background: #0056b3;
}

button:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.btn-danger {
    background: #dc3545;
}

.btn-danger:hover {
    background: #c82333;
}

.alert {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.warning {
    color: #856404;
    background: #fff3cd;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #ffeaa7;
}
```

## Usage in App

```jsx
// App.jsx
import React, { useState } from 'react';
import Profile from './components/Profile';

const App = () => {
    const [token, setToken] = useState(localStorage.getItem('auth_token'));

    const getToken = () => token;

    const handleLogout = () => {
        localStorage.removeItem('auth_token');
        setToken(null);
        // Redirect to login page
    };

    if (!token) {
        return <LoginComponent onLogin={setToken} />;
    }

    return (
        <div className="app">
            <Profile getToken={getToken} onLogout={handleLogout} />
        </div>
    );
};

export default App;
```

This example provides a complete implementation of profile management functionality that integrates with the Laravel API endpoints.