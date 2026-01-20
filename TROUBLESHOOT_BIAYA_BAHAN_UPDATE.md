# Troubleshooting: Biaya Bahan Update Issues

## Problem
User reports that when inputting data and clicking save in biaya bahan edit page:
1. No notification that data was successfully updated
2. Page doesn't redirect to main page with updated data

## Enhanced Debugging Applied

### 1. Added Comprehensive Logging
```php
\Log::info('=== BIAYA BAHAN UPDATE START ===', [
    'produk_id' => $id,
    'request_data' => $request->all(),
    'request_method' => $request->method(),
    'request_url' => $request->url(),
    'csrf_token' => $request->input('_token'),
    'has_csrf' => $request->hasHeader('X-CSRF-TOKEN') || $request->has('_token')
]);
```

### 2. Added Explicit Validation
```php
$request->validate([
    'bahan_baku' => 'nullable|array',
    'bahan_baku.*.id' => 'nullable|integer|exists:bahan_bakus,id',
    'bahan_baku.*.jumlah' => 'nullable|numeric|min:0',
    'bahan_baku.*.satuan' => 'nullable|string',
    'bahan_pendukung' => 'nullable|array',
    'bahan_pendukung.*.id' => 'nullable|integer|exists:bahan_pendukungs,id',
    'bahan_pendukung.*.jumlah' => 'nullable|numeric|min:0',
    'bahan_pendukung.*.satuan' => 'nullable|string',
]);
```

### 3. Enhanced Error Messages in Index View
```php
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

## Debugging Steps for User

### Step 1: Check Laravel Logs
```bash
# In terminal, navigate to project root and run:
tail -f storage/logs/laravel.log

# Then submit the form and watch for log entries
```

### Step 2: Look for These Log Entries
1. `=== BIAYA BAHAN UPDATE START ===` - Confirms method is called
2. `Validation passed` - Confirms no validation errors
3. `Product found` - Confirms product exists
4. `BOM and BomJobCosting ready` - Confirms database operations
5. `=== REDIRECTING TO INDEX ===` - Confirms redirect attempt

### Step 3: Check Browser Developer Tools
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Submit the form
4. Check for:
   - HTTP 200/302 response
   - Any JavaScript errors in Console tab
   - CSRF token in request headers

### Step 4: Common Issues to Check

#### CSRF Token Issues
- Form must have `@csrf` directive
- Check if token is present in request
- Clear browser cache/cookies

#### JavaScript Preventing Submission
- Check Console tab for JavaScript errors
- Look for `preventDefault()` calls
- Disable JavaScript temporarily to test

#### Database Issues
- Check database connection
- Verify table structures exist
- Check for foreign key constraints

#### Session Issues
- Check session configuration
- Clear session data
- Check session driver (file/database/redis)

#### Validation Errors
- Check if validation rules are too strict
- Look for missing required fields
- Check data types match validation rules

## Test Data for Manual Testing

### Minimal Test Case
```php
// Test with minimal data
bahan_baku[0][id] = 1 (existing bahan baku ID)
bahan_baku[0][jumlah] = 1
bahan_baku[0][satuan] = kg
```

### Complete Test Case
```php
// Test with both bahan baku and bahan pendukung
bahan_baku[0][id] = 1
bahan_baku[0][jumlah] = 2.5
bahan_baku[0][satuan] = kg

bahan_pendukung[0][id] = 1
bahan_pendukung[0][jumlah] = 1
bahan_pendukung[0][satuan] = pcs
```

## Expected Behavior

### Successful Update Flow
1. User fills form and clicks "Simpan Perubahan"
2. Form submits to `PUT /master-data/biaya-bahan/update/{id}`
3. Controller processes data and saves to database
4. Redirect to `/master-data/biaya-bahan` with success message
5. Index page shows green success alert
6. Updated data appears in the table

### Error Flow
1. If validation fails: Stay on edit page with error messages
2. If database error: Stay on edit page with error message
3. If other error: Log error and show generic error message

## Files Modified for Debugging
1. `app/Http/Controllers/BiayaBahanController.php` - Enhanced logging and validation
2. `resources/views/master-data/biaya-bahan/index.blade.php` - Enhanced error messages
3. `test_biaya_bahan_update.php` - Debug helper script

## Next Steps
1. User should check Laravel logs while submitting form
2. Report specific error messages or log entries found
3. Check browser network tab for HTTP response codes
4. Try with minimal test data first

## Status
**DEBUGGING ENHANCED** - Added comprehensive logging and validation to identify the root cause of the update issue.