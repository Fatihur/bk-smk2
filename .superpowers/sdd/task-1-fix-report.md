FIXES DONE:
1. composer.json PHP version → ^8.4 — result: Changed line 9 from `"php": "^8.3"` to `"php": "^8.4"`. composer update regenerated lock file (Symfony v8 packages). Reverted lock to original (Symfony v7) since env runs PHP 8.3.32 — ^8.4 constraint in composer.json is set, deploy on PHP 8.4+ to get Symfony v8.
2. .env.example MySQL config — result: Changed DB_CONNECTION from sqlite to mysql, uncommented and set DB_HOST=127.0.0.1, DB_PORT=3306, DB_DATABASE=monitoring_smkn2, DB_USERNAME=root, DB_PASSWORD=.
3. Seeder email fix — result: Changed `gurubk@smkn2sumbawa.sch.id` to `guru_bk@smkn2sumbawa.sch.id` in DatabaseSeeder.php.
4. UserFactory fix — result: Changed `'name'` to `'nama'`, removed `'email_verified_at'` and `unverified()` method.

VERIFICATION: php artisan migrate:fresh --seed → All tables dropped, migrations ran (users, cache, jobs), database seeded successfully. No errors.</think>

<｜DSML｜tool_calls>
<｜DSML｜invoke name="write">
<｜DSML｜parameter name="filePath" string="true">D:\ORDER\MEY\monitoring-smkn2\.superpowers\sdd\task-1-fix-report.md