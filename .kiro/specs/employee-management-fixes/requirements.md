# Requirements Document

## Introduction

This specification addresses critical issues in the employee management system including automatic employee code generation, data persistence problems, and implementation of role-based authentication for cashier and warehouse staff positions.

## Glossary

- **Employee_System**: The employee management module within the application
- **Auto_Code_Generator**: System component that automatically generates sequential employee codes
- **Role_Based_Login**: Authentication system that validates users based on their job position
- **Company_Code**: Unique identifier for the company used in employee authentication
- **Cashier_Portal**: Dedicated login interface for cashier role employees
- **Warehouse_Portal**: Dedicated login interface for warehouse staff employees
- **Admin_Panel**: Administrative interface for managing employee records

## Requirements

### Requirement 1

**User Story:** As an administrator, I want employee codes to be automatically generated in sequential order, so that each employee has a unique identifier without manual intervention.

#### Acceptance Criteria

1. WHEN an administrator creates a new employee record, THE Employee_System SHALL automatically generate the next sequential employee code
2. WHEN the Employee_System generates an employee code, THE Employee_System SHALL follow the format "PGW" followed by a 4-digit sequential number
3. WHEN determining the next employee code, THE Employee_System SHALL query existing records and increment from the highest existing code
4. WHEN an employee code generation fails, THE Employee_System SHALL retry with the next available sequential number
5. WHEN multiple administrators create employees simultaneously, THE Employee_System SHALL prevent duplicate code generation through proper locking mechanisms

### Requirement 2

**User Story:** As an administrator, I want employee data updates to persist correctly in the database, so that changes are not lost and data integrity is maintained.

#### Acceptance Criteria

1. WHEN an administrator updates employee information, THE Employee_System SHALL validate all required fields before saving
2. WHEN employee data is submitted for update, THE Employee_System SHALL save all changes to the database immediately
3. WHEN a database save operation fails, THE Employee_System SHALL display a clear error message and retain the entered data
4. WHEN employee data is successfully updated, THE Employee_System SHALL display a confirmation message
5. WHEN retrieving employee data after an update, THE Employee_System SHALL display the most recent saved values

### Requirement 3

**User Story:** As a cashier employee, I want to access a dedicated login portal using only my email and company code, so that I can quickly access my work interface without complex authentication.

#### Acceptance Criteria

1. WHEN a user accesses the cashier login page, THE Cashier_Portal SHALL display only email and company code input fields
2. WHEN a cashier submits login credentials, THE Employee_System SHALL verify the email exists in the employee database with "kasir" job position
3. WHEN login credentials are valid, THE Cashier_Portal SHALL authenticate the user and redirect to the cashier dashboard
4. WHEN an invalid email is submitted, THE Cashier_Portal SHALL display "Email tidak terdaftar di bagian kasir" message
5. WHEN an invalid company code is submitted, THE Cashier_Portal SHALL display an appropriate error message

### Requirement 4

**User Story:** As a warehouse employee, I want to access a dedicated login portal using only my email and company code, so that I can quickly access my work interface without complex authentication.

#### Acceptance Criteria

1. WHEN a user accesses the warehouse login page, THE Warehouse_Portal SHALL display only email and company code input fields
2. WHEN a warehouse employee submits login credentials, THE Employee_System SHALL verify the email exists in the employee database with "Bagian Gudang" job position
3. WHEN login credentials are valid, THE Warehouse_Portal SHALL authenticate the user and redirect to the warehouse dashboard
4. WHEN an invalid email is submitted, THE Warehouse_Portal SHALL display "Email tidak terdaftar di bagian gudang" message
5. WHEN an invalid company code is submitted, THE Warehouse_Portal SHALL display an appropriate error message

### Requirement 5

**User Story:** As a system administrator, I want role-based access control for employee portals, so that only authorized personnel can access specific work areas.

#### Acceptance Criteria

1. WHEN the Employee_System authenticates a user, THE Employee_System SHALL verify the user's job position matches the requested portal
2. WHEN a user attempts to access a portal without proper job position, THE Employee_System SHALL deny access and display an appropriate message
3. WHEN the Employee_System validates job positions, THE Employee_System SHALL check against the "jabatan" field in the employee database
4. WHEN multiple job positions are valid for a portal, THE Employee_System SHALL accept any of the specified positions
5. WHEN job position validation fails, THE Employee_System SHALL log the attempt for security monitoring

### Requirement 6

**User Story:** As an administrator, I want employees to be created through the admin panel without requiring separate user registration, so that employee management is centralized and controlled.

#### Acceptance Criteria

1. WHEN an administrator creates an employee record, THE Admin_Panel SHALL automatically enable login access for that employee
2. WHEN an employee record is created, THE Employee_System SHALL not require the employee to complete a separate registration process
3. WHEN an employee attempts to login, THE Employee_System SHALL authenticate using only the employee database records
4. WHEN an employee's job position is updated, THE Employee_System SHALL immediately reflect the change in portal access permissions
5. WHEN an employee record is deactivated, THE Employee_System SHALL immediately revoke login access for that employee