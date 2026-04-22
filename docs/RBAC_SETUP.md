# Role-Based Access Control (RBAC) Setup Guide

This guide explains how to use the comprehensive Role-Based Access Control system implemented in this application.

## Overview

The RBAC system allows you to:
- Create custom roles with specific permissions
- Assign roles to users
- Control access to different parts of the application
- Manage user permissions granularly

## Features

### 1. **Custom Role Management**
- Create, edit, and delete custom roles
- Assign multiple permissions to each role
- View role details including assigned users and permissions

### 2. **User Management with Roles**
- Assign one or multiple roles to users
- Grant additional permissions beyond role permissions
- Filter users by role
- View user roles in the user list

### 3. **Permission-Based Authorization**
- All resources are protected by permissions
- Users can only access features they're authorized for
- Automatic menu filtering based on permissions

## Available Permissions

The system includes permissions for the following resources:

### User Management
- `view_users` - View user list
- `create_users` - Create new users
- `update_users` - Edit existing users
- `delete_users` - Delete users

### Role Management
- `view_roles` - View role list
- `create_roles` - Create new roles
- `update_roles` - Edit existing roles
- `delete_roles` - Delete roles

### Applications
- `view_applications` - View applications
- `create_applications` - Create applications
- `update_applications` - Edit applications
- `delete_applications` - Delete applications

### Contact Inquiries
- `view_contact_inquiries` - View inquiries
- `create_contact_inquiries` - Create inquiries
- `update_contact_inquiries` - Edit inquiries
- `delete_contact_inquiries` - Delete inquiries

### Events
- `view_events` - View events
- `create_events` - Create events
- `update_events` - Edit events
- `delete_events` - Delete events

### Job Listings
- `view_job_listings` - View job listings
- `create_job_listings` - Create job listings
- `update_job_listings` - Edit job listings
- `delete_job_listings` - Delete job listings

### News
- `view_news` - View news articles
- `create_news` - Create news articles
- `update_news` - Edit news articles
- `delete_news` - Delete news articles

### Analytics
- `view_analytics` - View analytics dashboard

## Default Roles

The system comes with four pre-configured roles:

### 1. Super Admin
- **Full access** to all features
- Can manage users, roles, and all content
- Cannot be deleted if users are assigned

### 2. Admin
- Access to all content management features
- **Cannot** manage users or roles
- Can view, create, edit, and delete content

### 3. Editor
- Can manage content (Events, News, Job Listings)
- Can view and update Applications and Contact Inquiries
- **Cannot** create or delete Applications
- **Cannot** manage users or roles

### 4. Viewer
- **Read-only** access to all content
- Can view all resources but cannot modify anything
- Useful for auditors or observers

## Installation & Setup

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Seed Permissions and Roles
```bash
php artisan db:seed --class=PermissionSeeder
```

This will create:
- All permissions for available resources
- Four default roles (Super Admin, Admin, Editor, Viewer)
- Assign appropriate permissions to each role

### Step 3: Create Your First Super Admin User
```bash
php artisan make:super-admin
```

Follow the prompts to enter:
- Full name
- Email address
- Password (minimum 8 characters)

## Usage Guide

### Creating a Custom Role

1. Navigate to **User Management > Roles** in the admin panel
2. Click **New Role**
3. Enter role details:
   - **Role Name**: Unique name (e.g., "Content Manager")
   - **Description**: Optional description of the role
4. Select permissions by checking the boxes
5. Click **Create**

### Assigning Roles to Users

#### When Creating a New User:
1. Navigate to **User Management > Users**
2. Click **New User**
3. Fill in user information:
   - Full Name
   - Email Address
   - Password
4. In the **Role & Permissions** section:
   - Select one or more roles from the dropdown
   - Optionally grant additional specific permissions
5. Click **Create**

#### When Editing an Existing User:
1. Navigate to **User Management > Users**
2. Click the **Edit** button on the user
3. Update the **Roles** field
4. Click **Save**

### Managing Permissions

#### Role-Based Permissions:
- Permissions are primarily managed through roles
- When you assign a role to a user, they inherit all role permissions

#### Additional User Permissions:
- You can grant specific permissions to individual users
- These are in addition to their role permissions
- Useful for temporary access or special cases

### Viewing User Roles

In the Users table, you can:
- See all roles assigned to each user (displayed as badges)
- Filter users by role using the filter dropdown
- Sort by various columns including creation date

### Deleting Roles

**Important**: You cannot delete a role that has users assigned to it.

To delete a role:
1. First, reassign all users to different roles
2. Then delete the role from **User Management > Roles**

## Security Features

### Panel Access Control
- Only users with assigned roles can access the admin panel
- Users without roles are automatically denied access

### Resource-Level Authorization
- Each resource checks permissions before allowing access
- Navigation menu items are automatically hidden if user lacks permission
- Direct URL access is blocked for unauthorized users

### Action-Level Authorization
- View, Create, Edit, and Delete actions are individually protected
- Users only see action buttons they're authorized to use

## Best Practices

### 1. Use Roles for Common Permission Sets
- Create roles for common job functions (e.g., "Content Editor", "HR Manager")
- Assign users to roles rather than individual permissions
- This makes permission management easier and more maintainable

### 2. Follow the Principle of Least Privilege
- Only grant permissions that users need for their job
- Start with minimal permissions and add as needed
- Regularly review and audit user permissions

### 3. Create Descriptive Role Names
- Use clear, descriptive names (e.g., "Marketing Team" not "Team1")
- Add descriptions to explain what each role can do
- This helps with role assignment and auditing

### 4. Regular Permission Audits
- Periodically review user roles and permissions
- Remove access for users who no longer need it
- Update roles as job responsibilities change

### 5. Super Admin Access
- Limit Super Admin role to a few trusted users
- Use Admin or Editor roles for day-to-day operations
- Keep Super Admin for system configuration and user management

## Troubleshooting

### User Cannot Access Admin Panel
**Solution**: Ensure the user has at least one role assigned.

### User Cannot See a Resource
**Solution**: Check that the user's role includes the `view_*` permission for that resource.

### Cannot Delete a Role
**Solution**: Reassign all users from that role first, then delete.

### Permissions Not Working After Changes
**Solution**: Clear the permission cache:
```bash
php artisan permission:cache-reset
```

### Need to Add New Permissions
**Solution**: Update the `PermissionSeeder` and run:
```bash
php artisan db:seed --class=PermissionSeeder
```

## API Integration

If you're using the API, you can check permissions in your controllers:

```php
// Check if user has permission
if (auth()->user()->can('view_news')) {
    // Allow access
}

// Check if user has role
if (auth()->user()->hasRole('Admin')) {
    // Allow access
}

// Check if user has any of the roles
if (auth()->user()->hasAnyRole(['Admin', 'Super Admin'])) {
    // Allow access
}
```

## Additional Resources

- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission)
- [Filament Documentation](https://filamentphp.com/docs)

## Support

For issues or questions about the RBAC system, please contact your system administrator.
