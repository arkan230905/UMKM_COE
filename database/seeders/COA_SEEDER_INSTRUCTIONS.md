# HOW TO USE THE COA SEEDER

## 1. Register the Seeder
Add this line to `database/seeders/DatabaseSeeder.php` in the run() method:
```php
$this->call(CurrentCoaSeeder::class);
```

## 2. Run the Seeder
Execute one of these commands:

### Run all seeders:
```bash
php artisan db:seed
```

### Run only COA seeder:
```bash
php artisan db:seed --class=CurrentCoaSeeder
```

## 3. Fresh Migration with Seeder
To reset database and run seeder:
```bash
php artisan migrate:fresh --seed
```

## IMPORTANT NOTES:
- This seeder will TRUNCATE (delete all) existing COA data
- Make sure to backup your database before running
- The seeder includes all current accounts with their opening balances
- Total accounts: 83
- Accounts with opening balances: 3
