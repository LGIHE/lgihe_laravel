# RBAC System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         LGIHE RBAC System                        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────┐     ┌──────────────┐     ┌─────────────────────┐
│    Users    │────▶│    Roles     │────▶│    Permissions      │
└─────────────┘     └──────────────┘     └─────────────────────┘
  Many-to-Many       Many-to-Many          Assigned to Roles
                                           or Users directly
```

## Component Hierarchy

```
┌────────────────────────────────────────────────────────────────┐
│                        Admin Panel                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    Navigation Menu                        │  │
│  │  ┌────────────────┐  ┌────────────────┐  ┌────────────┐ │  │
│  │  │ User Management│  │Content Mgmt    │  │ Admissions │ │  │
│  │  │  • Users       │  │  • Events      │  │  • Apps    │ │  │
│  │  │  • Roles       │  │  • News        │  │            │ │  │
│  │  └────────────────┘  │  • Jobs        │  └────────────┘ │  │
│  │                      └────────────────┘                   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │              Authorization Layer                          │  │
│  │  • canViewAny()  • canCreate()                           │  │
│  │  • canEdit()     • canDelete()                           │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────┘
```

## Data Model

```
┌─────────────────┐
│     Users       │
├─────────────────┤
│ id              │
│ name            │
│ email           │
│ password        │
│ email_verified  │
└────────┬────────┘
         │
         │ Many-to-Many
         │
         ▼
┌─────────────────┐         ┌─────────────────────┐
│ model_has_roles │◀───────▶│      Roles          │
├─────────────────┤         ├─────────────────────┤
│ model_id        │         │ id                  │
│ role_id         │         │ name                │
└─────────────────┘         │ description         │
                            │ guard_name          │
                            └──────────┬──────────┘
                                       │
                                       │ Many-to-Many
                                       │
                                       ▼
                            ┌─────────────────────┐
                            │ role_has_permissions│
                            ├─────────────────────┤
                            │ role_id             │
                            │ permission_id       │
                            └──────────┬──────────┘
                                       │
                                       ▼
                            ┌─────────────────────┐
                            │    Permissions      │
                            ├─────────────────────┤
                            │ id                  │
                            │ name                │
                            │ guard_name          │
                            └─────────────────────┘
```

## Permission Flow

```
User Login
    │
    ▼
┌─────────────────────┐
│ Check if user has   │
│ any role assigned   │
└──────┬──────────────┘
       │
       ├─── No ──▶ Deny Panel Access
       │
       └─── Yes ──▶ Allow Panel Access
                    │
                    ▼
            ┌───────────────────┐
            │ User navigates to │
            │    a resource     │
            └────────┬──────────┘
                     │
                     ▼
            ┌───────────────────┐
            │ Check permission  │
            │  canViewAny()     │
            └────────┬──────────┘
                     │
                     ├─── No ──▶ Hide from menu
                     │
                     └─── Yes ──▶ Show in menu
                                  │
                                  ▼
                          ┌───────────────────┐
                          │ User clicks       │
                          │ action button     │
                          └────────┬──────────┘
                                   │
                                   ▼
                          ┌───────────────────┐
                          │ Check permission  │
                          │ canCreate/Edit/   │
                          │ Delete()          │
                          └────────┬──────────┘
                                   │
                                   ├─── No ──▶ Show error
                                   │
                                   └─── Yes ──▶ Allow action
```

## Role Hierarchy

```
┌─────────────────────────────────────────────────────────────┐
│                      Super Admin                             │
│  All 29 Permissions                                          │
│  • User Management  • Role Management  • All Content         │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         Admin                                │
│  21 Permissions (No User/Role Management)                    │
│  • All Content Management  • Applications  • Inquiries       │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Editor                                │
│  14 Permissions (Content Creation Only)                      │
│  • Events  • News  • Jobs  • View/Update Apps & Inquiries   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        Viewer                                │
│  7 Permissions (Read-Only)                                   │
│  • View All Resources                                        │
└─────────────────────────────────────────────────────────────┘
```

## Permission Categories

```
┌──────────────────────────────────────────────────────────────┐
│                    29 Total Permissions                       │
└──────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌───────────────┐    ┌───────────────┐    ┌───────────────┐
│User Management│    │Content Mgmt   │    │ Other         │
│  8 perms      │    │  16 perms     │    │  5 perms      │
├───────────────┤    ├───────────────┤    ├───────────────┤
│• view_users   │    │• view_events  │    │• view_apps    │
│• create_users │    │• create_events│    │• create_apps  │
│• update_users │    │• update_events│    │• update_apps  │
│• delete_users │    │• delete_events│    │• delete_apps  │
│• view_roles   │    │• view_news    │    │• view_inq     │
│• create_roles │    │• create_news  │    │• create_inq   │
│• update_roles │    │• update_news  │    │• update_inq   │
│• delete_roles │    │• delete_news  │    │• delete_inq   │
│               │    │• view_jobs    │    │• view_analytics│
│               │    │• create_jobs  │    └───────────────┘
│               │    │• update_jobs  │
│               │    │• delete_jobs  │
└───────────────┘    └───────────────┘
```

## Authorization Check Flow

```
Request to Resource
        │
        ▼
┌─────────────────┐
│ Is user logged  │
│     in?         │
└────────┬────────┘
         │
         ├─── No ──▶ Redirect to Login
         │
         └─── Yes
              │
              ▼
┌─────────────────────┐
│ Does user have any  │
│      role?          │
└──────────┬──────────┘
           │
           ├─── No ──▶ Deny Panel Access
           │
           └─── Yes
                │
                ▼
┌─────────────────────────────┐
│ Check Resource Permission   │
│ (via Role or Direct)        │
└──────────┬──────────────────┘
           │
           ├─── Has Permission ──▶ Allow Access
           │
           └─── No Permission ──▶ Deny Access
```

## File Organization

```
project/
│
├── app/
│   ├── Console/Commands/
│   │   └── CreateSuperAdmin.php          [Command to create admin]
│   │
│   ├── Filament/Resources/
│   │   ├── RoleResource.php              [Role management]
│   │   ├── RoleResource/Pages/           [Role CRUD pages]
│   │   ├── UserResource.php              [User management]
│   │   ├── ApplicationResource.php       [Protected resource]
│   │   ├── ContactInquiryResource.php    [Protected resource]
│   │   ├── EventResource.php             [Protected resource]
│   │   ├── JobListingResource.php        [Protected resource]
│   │   └── NewsResource.php              [Protected resource]
│   │
│   └── Models/
│       └── User.php                      [User model with roles]
│
├── database/
│   ├── migrations/
│   │   └── *_add_description_to_roles_table.php
│   │
│   └── seeders/
│       └── PermissionSeeder.php          [Seeds permissions & roles]
│
├── tests/Feature/
│   └── RBACTest.php                      [Test suite]
│
└── Documentation/
    ├── RBAC_SETUP.md                     [User guide]
    ├── RBAC_DEVELOPER_GUIDE.md           [Developer guide]
    ├── RBAC_QUICK_REFERENCE.md           [Quick reference]
    ├── RBAC_IMPLEMENTATION_SUMMARY.md    [Implementation details]
    ├── RBAC_ARCHITECTURE.md              [This file]
    └── IMPLEMENTATION_COMPLETE.md        [Completion summary]
```

## Request Lifecycle

```
1. User Request
   └─▶ /admin/news

2. Middleware Check
   └─▶ auth:web
       └─▶ Is authenticated?
           ├─ No → Redirect to login
           └─ Yes → Continue

3. Panel Access Check
   └─▶ User::canAccessPanel()
       └─▶ Has any role?
           ├─ No → Deny access
           └─ Yes → Continue

4. Resource Authorization
   └─▶ NewsResource::canViewAny()
       └─▶ Check permission: view_news
           ├─ No → 403 Forbidden
           └─ Yes → Show resource

5. Action Authorization
   └─▶ User clicks "Create"
       └─▶ NewsResource::canCreate()
           └─▶ Check permission: create_news
               ├─ No → Hide button / 403
               └─ Yes → Allow creation
```

## Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│                    Layer 1: Authentication                   │
│  Laravel Sanctum / Session Authentication                    │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Layer 2: Panel Access                     │
│  User must have at least one role                           │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Layer 3: Resource Access                  │
│  Check canViewAny() permission                              │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Layer 4: Action Access                    │
│  Check canCreate/Edit/Delete() permissions                  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Layer 5: UI Filtering                     │
│  Hide unauthorized menu items and buttons                   │
└─────────────────────────────────────────────────────────────┘
```

## Integration Points

```
┌─────────────────────────────────────────────────────────────┐
│                    External Systems                          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    API Endpoints                             │
│  /api/v1/admin/*                                            │
│  • Can use same permission checks                           │
│  • Sanctum token authentication                             │
│  • Permission middleware available                          │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    RBAC System                               │
│  • Spatie Laravel Permission                                │
│  • Filament Authorization                                   │
│  • Laravel Gates & Policies                                 │
└─────────────────────────────────────────────────────────────┘
```

## Scalability

```
Current: 29 Permissions, 4 Roles
         │
         ▼
Easy to Add:
├─ New Resources
│  └─ Add to PermissionSeeder
│     └─ Run seeder
│        └─ Update roles
│
├─ New Permissions
│  └─ Add to PermissionSeeder
│     └─ Run seeder
│        └─ Assign to roles
│
├─ New Roles
│  └─ Create via admin panel
│     └─ Select permissions
│        └─ Assign to users
│
└─ Custom Logic
   └─ Override canEdit/Delete
      └─ Add policy classes
         └─ Register in AuthServiceProvider
```

## Performance Considerations

```
┌─────────────────────────────────────────────────────────────┐
│                    Caching Strategy                          │
└─────────────────────────────────────────────────────────────┘

Permissions are cached for 24 hours
    │
    ├─ Cache Key: spatie.permission.cache
    │
    ├─ Auto-cleared on:
    │  • Role changes
    │  • Permission changes
    │  • User role assignment
    │
    └─ Manual clear:
       php artisan permission:cache-reset
```

## Maintenance Workflow

```
Regular Maintenance
    │
    ├─ Monthly: Review user roles
    │  └─ Remove departed users
    │     └─ Update changed roles
    │
    ├─ Quarterly: Audit permissions
    │  └─ Review custom roles
    │     └─ Update descriptions
    │
    └─ As Needed: Add resources
       └─ Update PermissionSeeder
          └─ Run seeder
             └─ Update roles
                └─ Clear cache
```

---

This architecture provides:
- ✅ Clear separation of concerns
- ✅ Multiple security layers
- ✅ Easy to understand and maintain
- ✅ Scalable for future growth
- ✅ Well-documented structure
