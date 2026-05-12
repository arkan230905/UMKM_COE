# Implementation Plan

- [x] 1. Fix Employee Code Auto-Generation System


  - Create EmployeeCodeService for centralized code generation logic
  - Fix the Pegawai model boot method to use proper database locking
  - Implement retry logic for concurrent creation scenarios
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_



- [ ] 1.1 Create EmployeeCodeService class
  - Implement generateNextCode() method with database locking
  - Add getLastUsedCode() method for sequence tracking
  - Include validateCodeFormat() for consistency checks
  - _Requirements: 1.1, 1.2, 1.3_

- [ ]* 1.2 Write property test for employee code generation
  - **Property 1: Employee Code Sequential Uniqueness**
  - **Validates: Requirements 1.1, 1.2, 1.3**

- [ ]* 1.3 Write property test for concurrent code generation
  - **Property 2: Concurrent Code Generation Safety**


  - **Validates: Requirements 1.5**

- [x] 1.4 Update Pegawai model boot method


  - Replace current auto-generation logic with EmployeeCodeService
  - Add proper error handling and retry mechanisms
  - Ensure thread-safe code generation
  - _Requirements: 1.1, 1.4, 1.5_



- [ ] 2. Fix Employee Data Persistence Issues
  - Create proper request validation classes
  - Fix employee update controller methods
  - Implement proper error handling and user feedback


  - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

- [ ] 2.1 Create EmployeeStoreRequest validation class
  - Define validation rules for all required employee fields
  - Add custom validation messages in Indonesian
  - Include unique email validation logic
  - _Requirements: 2.1_

- [ ] 2.2 Create EmployeeUpdateRequest validation class
  - Define validation rules for employee updates
  - Handle unique email validation for existing records
  - Add proper error messaging


  - _Requirements: 2.1_

- [ ]* 2.3 Write property test for data persistence
  - **Property 3: Data Persistence Round Trip**
  - **Validates: Requirements 2.2, 2.5**




- [ ]* 2.4 Write property test for validation rejection
  - **Property 4: Validation Rejection Consistency**
  - **Validates: Requirements 2.1**

- [x] 2.5 Create or fix EmployeeController


  - Implement proper store() method with validation
  - Fix update() method to handle all fields correctly
  - Add proper success/error messaging
  - _Requirements: 2.2, 2.3, 2.4, 2.5_



- [ ] 3. Checkpoint - Ensure employee CRUD operations work
  - Ensure all tests pass, ask the user if questions arise.



- [ ] 4. Enhance Role-Based Authentication System
  - Fix existing KasirAuthController and GudangAuthController
  - Improve error messaging and validation
  - Add proper session management
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5_

- [ ] 4.1 Create EmployeeLoginRequest validation class
  - Define validation rules for email and company code
  - Add custom error messages in Indonesian
  - Include proper field validation
  - _Requirements: 3.1, 4.1_

- [ ] 4.2 Fix KasirAuthController authentication logic
  - Improve email and role validation
  - Fix error messaging to match requirements
  - Enhance session management
  - _Requirements: 3.2, 3.3, 3.4, 3.5_

- [ ] 4.3 Fix GudangAuthController authentication logic
  - Improve email and role validation for warehouse staff
  - Fix error messaging to match requirements
  - Enhance session management
  - _Requirements: 4.2, 4.3, 4.4, 4.5_



- [ ]* 4.4 Write property test for role-based authentication
  - **Property 5: Role-Based Authentication Precision**
  - **Validates: Requirements 3.2, 4.2, 5.1, 5.3**

- [ ]* 4.5 Write property test for cross-portal access denial
  - **Property 6: Cross-Portal Access Denial**
  - **Validates: Requirements 5.2, 5.4**

- [ ] 5. Implement Enhanced Access Control System
  - Add role validation middleware
  - Implement security logging for failed attempts
  - Add real-time permission updates
  - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [ ] 5.1 Create EmployeeRoleMiddleware
  - Implement role validation logic
  - Add proper access denial handling
  - Include security logging for failed attempts
  - _Requirements: 5.1, 5.2, 5.5_

- [ ] 5.2 Add role validation methods to Pegawai model
  - Create isKasir() method for cashier role checking
  - Create isGudang() method for warehouse role checking
  - Add flexible role matching for multiple valid positions
  - _Requirements: 5.3, 5.4_

- [ ]* 5.3 Write unit tests for role validation methods
  - Test isKasir() method with various job titles
  - Test isGudang() method with various job titles
  - Test edge cases and case sensitivity
  - _Requirements: 5.3, 5.4_

- [ ] 6. Implement Immediate Access Provisioning
  - Ensure employee creation enables immediate login
  - Add real-time permission updates
  - Implement access revocation for deactivated employees
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [ ] 6.1 Update employee creation workflow
  - Ensure new employees can login immediately
  - Remove any separate registration requirements
  - Test immediate access availability
  - _Requirements: 6.1, 6.2, 6.3_

- [ ]* 6.2 Write property test for immediate access provisioning
  - **Property 7: Immediate Access Provisioning**
  - **Validates: Requirements 6.1, 6.2, 6.3**

- [ ] 6.3 Implement real-time permission updates
  - Add observer for Pegawai model changes
  - Implement session invalidation for role changes
  - Add access revocation for deactivated employees
  - _Requirements: 6.4, 6.5_

- [ ]* 6.4 Write property test for real-time permission updates
  - **Property 8: Real-Time Permission Updates**
  - **Validates: Requirements 6.4**

- [ ] 7. Update Login Views and Routes
  - Ensure login forms only show required fields
  - Update error message display
  - Test complete login workflows
  - _Requirements: 3.1, 4.1_

- [ ] 7.1 Review and update kasir login view
  - Ensure only email and company code fields are shown
  - Update error message display areas
  - Test form submission and validation
  - _Requirements: 3.1, 3.4, 3.5_

- [ ] 7.2 Review and update gudang login view
  - Ensure only email and company code fields are shown
  - Update error message display areas
  - Test form submission and validation
  - _Requirements: 4.1, 4.4, 4.5_

- [ ]* 7.3 Write integration tests for login workflows
  - Test complete cashier login workflow
  - Test complete warehouse login workflow
  - Test error scenarios and message display
  - _Requirements: 3.1, 3.3, 4.1, 4.3_

- [ ] 8. Final Checkpoint - Complete system testing
  - Ensure all tests pass, ask the user if questions arise.
  - Test employee creation with auto-generated codes
  - Test employee data updates and persistence
  - Test role-based login for both cashier and warehouse portals
  - Verify error messages and user feedback