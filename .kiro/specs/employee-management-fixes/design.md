# Design Document

## Overview

This design addresses critical issues in the employee management system including automatic employee code generation, data persistence problems, and role-based authentication for cashier and warehouse staff. The solution involves fixing the auto-increment logic, implementing proper data validation and persistence, and enhancing the existing role-based login system.

## Architecture

The system follows a layered architecture with the following components:

1. **Model Layer**: Enhanced Pegawai model with improved auto-generation logic
2. **Controller Layer**: Fixed authentication controllers and employee management controllers
3. **Service Layer**: New EmployeeCodeService for reliable code generation
4. **Validation Layer**: Enhanced request validation for data integrity
5. **Authentication Layer**: Improved role-based authentication system

## Components and Interfaces

### EmployeeCodeService
- **Purpose**: Centralized service for generating sequential employee codes
- **Methods**:
  - `generateNextCode()`: Returns next available employee code
  - `getLastUsedCode()`: Retrieves highest existing code
  - `validateCodeFormat()`: Validates code format consistency

### Enhanced Pegawai Model
- **Auto-generation**: Improved boot method with proper locking mechanism
- **Validation**: Enhanced fillable fields and validation rules
- **Relationships**: Maintained existing relationships with other models

### Authentication Controllers
- **KasirAuthController**: Enhanced with better error messages and validation
- **GudangAuthController**: Enhanced with better error messages and validation
- **EmployeeController**: New controller for employee CRUD operations

### Request Validation Classes
- **EmployeeStoreRequest**: Validation for creating employees
- **EmployeeUpdateRequest**: Validation for updating employees
- **EmployeeLoginRequest**: Validation for employee login

## Data Models

### Pegawai Model Enhancements
```php
// Enhanced fillable fields
protected $fillable = [
    'kode_pegawai',
    'nomor_induk_pegawai', 
    'nama',
    'email',
    'no_telp',
    'alamat',
    'jenis_kelamin',
    'jabatan',
    'gaji',
    'kategori_tenaga_kerja',
    'gaji_pokok',
    'tarif_per_jam',
    'jam_kerja_per_minggu',
    'tunjangan',
    'jenis_pegawai',
    'bank',
    'nomor_rekening',
    'nama_rekening',
    'asuransi'
];

// Enhanced validation rules
protected $rules = [
    'nama' => 'required|string|max:255',
    'email' => 'required|email|unique:pegawais,email',
    'jabatan' => 'required|string|max:255',
    'gaji_pokok' => 'required|numeric|min:0',
    'tarif_per_jam' => 'required|numeric|min:0'
];
```

### Database Schema Considerations
- Ensure proper indexing on `kode_pegawai` and `email` fields
- Add database constraints for data integrity
- Implement proper foreign key relationships

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Employee Code Sequential Uniqueness
*For any* employee creation operation, the generated employee code should be unique across all existing employee records, follow the format "PGW" + 4-digit sequential number, and be exactly one greater than the highest existing code
**Validates: Requirements 1.1, 1.2, 1.3**

### Property 2: Concurrent Code Generation Safety
*For any* simultaneous employee creation operations, no duplicate employee codes should be generated regardless of timing or concurrency
**Validates: Requirements 1.5**

### Property 3: Data Persistence Round Trip
*For any* valid employee data update, retrieving the employee record immediately after the update should return exactly the same data that was submitted
**Validates: Requirements 2.2, 2.5**

### Property 4: Validation Rejection Consistency
*For any* employee data submission with invalid required fields, the system should reject the submission and return appropriate validation errors without saving partial data
**Validates: Requirements 2.1**

### Property 5: Role-Based Authentication Precision
*For any* login attempt, authentication should succeed if and only if the email exists in the employee database with a job position that matches the portal being accessed ("kasir" for cashier portal, "Bagian Gudang" for warehouse portal)
**Validates: Requirements 3.2, 4.2, 5.1, 5.3**

### Property 6: Cross-Portal Access Denial
*For any* employee with a specific job position, attempting to access a portal not matching their role should be denied with appropriate error messaging
**Validates: Requirements 5.2, 5.4**

### Property 7: Immediate Access Provisioning
*For any* newly created employee record, login access should be immediately available without requiring additional registration steps
**Validates: Requirements 6.1, 6.2, 6.3**

### Property 8: Real-Time Permission Updates
*For any* employee job position change, portal access permissions should immediately reflect the new role without requiring system restart or cache clearing
**Validates: Requirements 6.4**

## Error Handling

### Employee Code Generation Errors
- **Concurrent Creation**: Implement database-level locking to prevent duplicate codes
- **Sequence Gaps**: Handle missing sequence numbers gracefully
- **Format Validation**: Ensure all codes follow the PGW#### format

### Data Persistence Errors
- **Validation Failures**: Return detailed field-level error messages
- **Database Constraints**: Handle unique constraint violations gracefully
- **Transaction Rollback**: Ensure data consistency on partial failures

### Authentication Errors
- **Invalid Credentials**: Clear, localized error messages
- **Role Mismatch**: Specific messages for role-based access denial
- **Session Timeout**: Proper cleanup and redirect handling

## Testing Strategy

### Unit Testing
- Test employee code generation logic with various scenarios
- Test data validation rules for all employee fields
- Test authentication logic for different role combinations
- Test error handling for edge cases

### Property-Based Testing
Using PHPUnit with Faker for property-based testing:

**Property Tests Required:**
1. **Employee Code Generation**: Generate random employee data and verify codes are always unique and sequential
2. **Data Persistence**: Create random employee updates and verify all data persists correctly
3. **Role Authentication**: Generate random login attempts and verify role-based access control
4. **Error Message Consistency**: Test various failure scenarios and verify appropriate error messages
5. **Session Management**: Test session creation and cleanup across multiple scenarios

**Configuration:**
- Minimum 100 iterations per property test
- Use database transactions for test isolation
- Mock external dependencies appropriately

### Integration Testing
- Test complete employee creation workflow
- Test role-based login flows end-to-end
- Test data update workflows with validation
- Test concurrent employee creation scenarios