# Testing Customer Data Isolation Fix

**Duration:** ~10 minutes  
**Requires:** 2 different store owner accounts

## Test Scenario

We'll verify that customers created in Store A are NOT visible in Store B.

## Step-by-Step Test

### Step 1: Prepare - Create/Use Two Store Owner Accounts

You need two separate perusahaan accounts:
- **Store A Owner:** Account with permission to manage one perusahaan
- **Store B Owner:** Account with permission to manage another perusahaan

If you only have one, you'll need to create a second test account first.

### Step 2: Test as Store A Owner

1. **Login to Store A Owner account**
   - Navigate to your ERP dashboard
   - Verify you're logged in as Store A owner

2. **Go to Master Data → Pelanggan**
   - View current customer list
   - Note the count of customers

3. **Register a NEW customer** (this tests the registration fix)
   - Go to customer registration page: `/pelanggan/login`
   - Fill out form:
     - Name: `Test Customer A`
     - Email: `testcustomer_a@test.com`
     - Phone: `08123456789`
     - Password: `password123`
   - Click Register
   - **✓ VERIFY:** Redirect to customer dashboard successful
   - Customer should now be created with `user_id` and `perusahaan_id` set

4. **Return to Master Data → Pelanggan**
   - **✓ VERIFY:** `Test Customer A` appears in your list
   - Note the total customer count

5. **Logout from Store A**

### Step 3: Test as Store B Owner

1. **Login to Store B Owner account**
   - Use a DIFFERENT store owner account
   - Verify you're logged in as Store B owner

2. **Go to Master Data → Pelanggan**
   - View customer list
   - **⚠️ CRITICAL CHECK:** `Test Customer A` should NOT be visible here
   - Only Store B's own customers should show
   - Customer count should be different from Store A

3. **Verify Edit/Delete Operations are Scoped**
   - Try to access Store A's customer directly:
     - Go to URL: `master-data/pelanggan/STORE_A_CUSTOMER_ID/edit`
     - **✓ VERIFY:** Should get 404 or error (not found)
     - This confirms scope filtering works

4. **Register a NEW customer for Store B** (test registration in different store)
   - Go to customer registration: `/pelanggan/login`
   - Fill out form:
     - Name: `Test Customer B`
     - Email: `testcustomer_b@test.com`
     - Phone: `08987654321`
     - Password: `password456`
   - Click Register
   - **✓ VERIFY:** Registration successful
   - Return to Master Data → Pelanggan
   - **✓ VERIFY:** `Test Customer B` appears in list

5. **Logout from Store B**

### Step 4: Verify Back in Store A

1. **Login back to Store A Owner account**

2. **Go to Master Data → Pelanggan**
   - **✓ VERIFY:** `Test Customer A` still visible
   - **✓ VERIFY:** `Test Customer B` is NOT visible
   - Only original customers + `Test Customer A` should show

3. **Try to directly access Store B's customer**
   - Go to URL: `master-data/pelanggan/STORE_B_CUSTOMER_ID/edit`
   - **✓ VERIFY:** Should get 404 or error (not found)
   - Confirms scope filtering works both ways

## Expected Results

| Test | Expected | Status |
|------|----------|--------|
| Store A customer visible to A | ✅ Yes | 🔲 |
| Store A customer visible to B | ❌ No | 🔲 |
| Store B customer visible to B | ✅ Yes | 🔲 |
| Store B customer visible to A | ❌ No | 🔲 |
| Register A creates with user_id | ✅ Yes | 🔲 |
| Register B creates with user_id | ✅ Yes | 🔲 |
| Edit scope check A→B | ❌ Not found | 🔲 |
| Edit scope check B→A | ❌ Not found | 🔲 |

## Troubleshooting

### Issue: Customer appears in wrong store
**Cause:** `perusahaan_id` filter not working  
**Check:**
- Verify new customers have `perusahaan_id` set during registration
- Run: `SELECT * FROM users WHERE email='testcustomer_a@test.com'`
- Check database: `user_id` and `perusahaan_id` should be populated

### Issue: Cannot edit/delete customer in own store
**Cause:** Scope filter might be too strict  
**Check:**
- Verify current user's `perusahaan_id` matches customer's `perusahaan_id`
- Check PelangganTableController for correct filter logic

### Issue: 500 Error on customer list
**Cause:** Database issue or column missing  
**Check:**
- Verify `perusahaan_id` column exists in `users` table
- Run: `DESCRIBE users;` to check columns
- Check Laravel logs for errors

## Database Verification (Advanced)

After testing, verify in database:

```sql
-- Check Test Customer A
SELECT id, name, email, user_id, perusahaan_id 
FROM users 
WHERE email = 'testcustomer_a@test.com';

-- Check Test Customer B  
SELECT id, name, email, user_id, perusahaan_id
FROM users
WHERE email = 'testcustomer_b@test.com';

-- Verify they have different user_id values
-- Verify they have different perusahaan_id values
```

## Rollback if Issues Found

If testing reveals critical issues:

```bash
# Revert the commit
git reset --soft HEAD~1

# Or revert specific file
git checkout HEAD~ -- app/Http/Controllers/MasterData/PelangganTableController.php
git checkout HEAD~ -- app/Http/Controllers/Pelanggan/Auth/LoginController.php
```

## Success Criteria ✅

All of these must pass:
- [ ] Store A customers NOT visible in Store B
- [ ] Store B customers NOT visible in Store A
- [ ] New customer registration works
- [ ] Customer scoping prevents unauthorized access
- [ ] No errors in application logs

## Report Results

Once testing is complete, please confirm:
- ✅ All tests passed - ready to push
- ⚠️ Some issues found - need debugging
- ❌ Major problem - needs rollback

Then we can proceed with: `git push origin ghitha`
