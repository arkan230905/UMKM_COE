# Implementation Plan

- [x] 1. Enhance User Model with role helper methods


  - Add role constants (ROLE_ADMIN, ROLE_OWNER, ROLE_PELANGGAN, ROLE_PEGAWAI_PEMBELIAN)
  - Add VALID_ROLES array constant
  - Implement isAdmin(), isOwner(), isPelanggan(), isPegawaiPembelian() methods
  - Implement hasRole($role) and hasAnyRole(array $roles) methods
  - _Requirements: 1.1, 1.4, 4.1_



- [ ] 2. Create database migration to backfill missing user roles
  - Create migration file with timestamp
  - Find all users with null or empty role
  - Assign 'pelanggan' role to users without perusahaan_id
  - Assign 'admin' role to users with perusahaan_id
  - Log all role assignments


  - Make migration idempotent (safe to run multiple times)
  - _Requirements: 3.1, 3.2, 3.3_

- [ ] 3. Enhance RoleMiddleware with better error handling
  - Check if user is authenticated before checking roles
  - Redirect unauthenticated users to login instead of 403


  - Handle null/invalid roles gracefully
  - Pass context data to 403 error (required roles, user's current role)
  - Log access denial attempts with user ID and requested route
  - _Requirements: 2.1, 2.4, 4.1, 4.2, 4.4_

- [ ] 4. Create custom 403 error page
  - Create resources/views/errors/403.blade.php


  - Display user-friendly error message
  - Show required roles for the route
  - Show user's current role
  - Provide role-specific navigation links (pelanggan dashboard, pembelian dashboard, login)


  - Use application layout for consistent styling
  - _Requirements: 2.1, 2.2, 2.3, 2.5_

- [x] 5. Fix RegisterController redirect logic


  - Remove $redirectTo property that conflicts with redirectTo() method
  - Ensure redirectTo() method is always used for post-registration redirects
  - Verify redirect works correctly for each role type
  - _Requirements: 1.2, 1.3_

- [ ] 6. Run migration and verify role assignments
  - Execute the backfill migration
  - Query database to verify all users have valid roles
  - Check logs for any issues during migration
  - _Requirements: 3.1, 3.2_

- [ ] 7. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.
