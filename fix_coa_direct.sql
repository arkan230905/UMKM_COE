-- Direct fix for expense_payments COA data
UPDATE expense_payments SET coa_beban_id = '551' WHERE id = 2;
UPDATE expense_payments SET coa_beban_id = '550' WHERE id = 3;

-- Delete old journal entries
DELETE FROM journal_entries WHERE ref_type = 'expense_payment' AND ref_id IN (2, 3);
DELETE FROM journal_lines WHERE journal_entry_id NOT IN (SELECT id FROM journal_entries);

-- Verify the fix
SELECT id, tanggal, coa_beban_id, nominal_pembayaran FROM expense_payments WHERE id IN (2, 3);
