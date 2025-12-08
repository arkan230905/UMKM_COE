# Design Document: Dashboard 403 Fix

## Overview

This design addresses the 403 Forbidden error users encounter when accessing the dashboard. The root cause is that users may not have the required 'admin' or 'owner' role assigned. The solution involves:

1. Ensuring all users have valid roles assigned
2. Creating a user-friendly 403 error page
3. Fixing the redirect logic after registration
4. Adding a database check/migration to backfill missing roles
5. Improving error handling in the RoleMiddleware

## Architecture

The system uses Laravel's middleware-based role checking:

```
Request → auth middleware → role middleware → DashboardController
```

Current flow:
- User authenticates successfully
- RoleMiddleware checks if user has 'admin' or 'owner' role
- If role doesn't match, returns 403 abort()
- Laravel shows generic 403 page

Improved flow:
- User authenticates successfully  
- RoleMiddleware checks if user exists and has valid role
- If role doesn't match, returns 403 with custom error page
- Custom 403 page shows helpful message and redirect options

## Components and Interfaces

### 1. RoleMiddleware Enhancement

**Location:** `app/Http/Middleware/RoleMiddleware.php`

**Current Implementation:**
```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $user = $request->user();
    if (! $user) {
        abort(403);
    }
    if (! in_array($user->role, $roles, true)) {
        abort(403);
    }
    return $next($request);
}
```

**Enhanced Implementation:**
- Add null role checking
- Pass context to 403 error (required roles, user's current role)
- Log access denial attempts for security auditing
- Redirect unauthenticated users to login instead of 403

### 2. Custom 403 Error View

**Location:** `resources/views/errors/403.blade.php`

**Purpose:** Display user-friendly error message when access is denied

**Features:**
- Show which roles are required
- Display user's current role
- Provide navigation links based on user role:
  - pelanggan → pelanggan dashboard
  - pegawai_pembelian → pembelian dashboard  
  - No role → login page
- Match application styling (use layout)

### 3. Database Migration for Role Backfill

**Location:** `database/migrations/YYYY_MM_DD_backfill_user_roles.php`

**Purpose:** Ensure all existing users have valid roles

**Logic:**
- Find users with null or empty role
- Assign default role 'pelanggan' to users without perusahaan_id
- Assign 'admin' to users with perusahaan_id (business users)
- Log all changes for audit trail

### 4. RegisterController Fix

**Location:** `app/Http/Controllers/Auth/RegisterController.php`

**Current Issue:** `$redirectTo` property set to '/dashboard' but `redirectTo()` method returns different routes based on role

**Fix:** Remove the `$redirectTo` property to ensure the `redirectTo()` method is always used

### 5. User Model Enhancement

**Location:** `app/Models/User.php`

**Additions:**
- Add role constants for type safety
- Add helper methods: `isAdmin()`, `isOwner()`, `isPelanggan()`, `isPegawaiPembelian()`
- Add `hasRole($role)` method
- Add role validation in model

## Data Models

### User Model

```php
class User extends Authenticatable
{
    const ROLE_ADMIN = 'admin';
    const ROLE_OWNER = 'owner';
    const ROLE_PELANGGAN = 'pelanggan';
    const ROLE_PEGAWAI_PEMBELIAN = 'pegawai_pembelian';
    
    const VALID_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_OWNER,
        self::ROLE_PELANGGAN,
        self::ROLE_PEGAWAI_PEMBELIAN,
    ];
    
    protected $fillable = [
        'name', 'username', 'email', 'phone', 
        'password', 'role', 'perusahaan_id',
    ];
    
    // Helper methods
    public function isAdmin(): bool
    public function isOwner(): bool  
    public function isPelanggan(): bool
    public function isPegawaiPembelian(): bool
    public function hasRole(string $role): bool
    public function hasAnyRole(array $roles): bool
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Role assignment completeness
*For any* user record in the database, the role field should contain one of the valid role values (admin, owner, pelanggan, pegawai_pembelian)
**Validates: Requirements 1.1, 1.4**

### Property 2: Role-based access control consistency  
*For any* authenticated user attempting to access a protected route, if the user has any of the required roles for that route, then access should be granted
**Validates: Requirements 4.1, 4.3, 4.5**

### Property 3: Access denial with valid feedback
*For any* authenticated user lacking required roles, when access is denied, the system should display an error message that includes the required roles
**Validates: Requirements 2.1, 2.2**

### Property 4: Unauthenticated user redirection
*For any* unauthenticated request to a protected route, the system should redirect to the login page rather than showing a 403 error
**Validates: Requirements 2.4**

### Property 5: Registration role assignment
*For any* new user registration, the created user record should have a non-null role value that matches one of the valid roles
**Validates: Requirements 1.1, 1.3**

### Property 6: Role validation on user creation
*For any* attempt to create a user with an invalid role value, the system should reject the creation and return a validation error
**Validates: Requirements 3.4**

## Error Handling

### 1. Middleware Error Handling

**Scenario:** User lacks required role
- **Action:** Return 403 with custom view
- **Data passed:** Required roles, user's current role, suggested redirect
- **Logging:** Log access attempt with user ID, requested route, and role mismatch

**Scenario:** User not authenticated
- **Action:** Redirect to login with intended URL
- **No 403 error shown**

**Scenario:** User has null/invalid role
- **Action:** Log critical error, force logout, redirect to login
- **Message:** "Your account has an invalid configuration. Please contact support."

### 2. Registration Error Handling

**Scenario:** Invalid role provided
- **Action:** Validation error before user creation
- **Message:** "Invalid role selected"

**Scenario:** Missing company code for admin/pegawai_pembelian
- **Action:** Validation error
- **Message:** "Company code is required for this role"

### 3. Migration Error Handling

**Scenario:** User with null role and no perusahaan_id
- **Action:** Assign 'pelanggan' role
- **Logging:** Log the user ID and assigned role

**Scenario:** User with null role but has perusahaan_id
- **Action:** Assign 'admin' role (business user)
- **Logging:** Log the user ID and assigned role

## Testing Strategy

### Unit Tests

**Test File:** `tests/Unit/UserModelTest.php`

Tests for User model helper methods:
- `test_isAdmin_returns_true_for_admin_role()`
- `test_isOwner_returns_true_for_owner_role()`
- `test_isPelanggan_returns_true_for_pelanggan_role()`
- `test_isPegawaiPembelian_returns_true_for_pegawai_pembelian_role()`
- `test_hasRole_returns_true_when_user_has_role()`
- `test_hasAnyRole_returns_true_when_user_has_any_role()`

**Test File:** `tests/Unit/RoleMiddlewareTest.php`

Tests for RoleMiddleware:
- `test_allows_access_when_user_has_required_role()`
- `test_denies_access_when_user_lacks_required_role()`
- `test_redirects_unauthenticated_users_to_login()`
- `test_handles_null_role_gracefully()`

### Integration Tests

**Test File:** `tests/Feature/DashboardAccessTest.php`

Tests for dashboard access control:
- `test_admin_can_access_dashboard()`
- `test_owner_can_access_dashboard()`
- `test_pelanggan_cannot_access_dashboard()`
- `test_pegawai_pembelian_cannot_access_dashboard()`
- `test_unauthenticated_user_redirected_to_login()`
- `test_403_page_shows_helpful_message()`

**Test File:** `tests/Feature/UserRegistrationTest.php`

Tests for registration:
- `test_new_user_has_role_assigned()`
- `test_pelanggan_registration_assigns_pelanggan_role()`
- `test_owner_registration_assigns_owner_role()`
- `test_admin_registration_requires_company_code()`

### Property-Based Tests

**Test File:** `tests/Property/RoleAccessPropertyTest.php`

Property tests using PHPUnit with random data generation:

**Property Test 1: Role assignment completeness**
- Generate random user data with various role combinations
- Create users and verify all have valid roles
- **Validates: Property 1**

**Property Test 2: Role-based access control consistency**
- Generate random combinations of user roles and route requirements
- Verify access is granted when user has any required role
- **Validates: Property 2**

**Property Test 3: Access denial feedback**
- Generate random role mismatches
- Verify error messages always include required roles
- **Validates: Property 3**

### Manual Testing Checklist

1. Create new user with each role type
2. Attempt dashboard access with each role
3. Verify 403 page displays correctly
4. Verify redirect after registration works for each role
5. Test with user having null role (after manually setting to null)
6. Verify migration backfills roles correctly

## Implementation Notes

### Database Considerations

- The `role` column already has a default value of 'pelanggan' in the migration
- Existing users may have null roles if they were created before the migration
- The backfill migration should be idempotent (safe to run multiple times)

### Security Considerations

- Log all access denial attempts for security monitoring
- Ensure role checking happens after authentication
- Validate roles on user creation/update
- Don't expose sensitive information in error messages

### User Experience Considerations

- 403 page should be helpful, not punitive
- Provide clear next steps for users
- Match application branding and styling
- Consider adding a "Request Access" feature for future enhancement

### Performance Considerations

- Role checking is lightweight (simple string comparison)
- No database queries in middleware (role loaded with user)
- Error page should load quickly
- Consider caching user roles if needed in future
