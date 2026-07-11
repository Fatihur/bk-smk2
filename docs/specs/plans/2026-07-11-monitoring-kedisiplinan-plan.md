# Monitoring Kedisiplinan Siswa — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a web-based student discipline monitoring system for SMKN 2 Sumbawa Besar with auto-generated warning letters and WhatsApp notifications to parents.

**Architecture:** Monolithic Laravel 13 app with Blade + Tailwind frontend (dark theme). MySQL database. WhatsApp integration via `kstmostofa/laravel-whatsapp` with database queue for async delivery.

**Tech Stack:** Laravel 13, MySQL 8, Tailwind CSS, barryvdh/laravel-dompdf, kstmostofa/laravel-whatsapp, maatwebsite/laravel-excel

## Global Constraints

- Database naming: Bahasa Indonesia for all tables & columns except `users` and `id`
- No `created_at` / `updated_at` columns in any table
- PHP 8.4+ required (Laravel 13 + Symfony 8 transitive dependency)
- Frontend: dark theme, IBM Plex Sans font, TypeUI Dashboard design tokens
- WA library requires Node.js 18+ and ~600MB disk for Chromium

---

### Task 1: Project Scaffolding + Auth + Layout

**Files:**
- Create: `D:\ORDER\MEY\monitoring-smkn2` (Laravel project root)
- Modify: `tailwind.config.js`
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/components/sidebar.blade.php`
- Create: `resources/views/dashboard/index.blade.php`
- Create: `app/Http/Middleware/RoleMiddleware.php`
- Create: `app/Http/Controllers/DashboardController.php`

**Interfaces:**
- Consumes: Laravel Breeze auth scaffolding
- Produces: `RoleMiddleware` with `handle($request, $next, ...$roles)`, `DashboardController@index`

- [ ] **Step 1: Scaffold Laravel project with Breeze**

```bash
cd D:\ORDER\MEY
composer create-project laravel/laravel monitoring-smkn2 --prefer-dist
cd monitoring-smkn2
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
```

- [ ] **Step 2: Configure MySQL database**

Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring_smkn2
DB_USERNAME=root
DB_PASSWORD=
```

- [ ] **Step 3: Update users migration to remove timestamps + add role column**

Edit `database/migrations/0001_00_00_000000_create_users_table.php` — replace the schema with:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('role')->default('guru_bk');
});
```

Also modify `app/Models/User.php`:
```php
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = ['nama', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];
}
```

- [ ] **Step 4: Update other default migrations to remove timestamps**

Edit `database/migrations/0001_01_01_000002_create_jobs_table.php` and `0001_01_01_000001_create_cache_table.php` — remove `timestamps()` calls where present.

- [ ] **Step 5: Run initial migration**

```bash
php artisan migrate
```

- [ ] **Step 6: Create RoleMiddleware**

```php
<?php
// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!in_array($request->user()->role, $roles)) {
            abort(403);
        }
        return $next($request);
    }
}
```

- [ ] **Step 7: Register middleware in `bootstrap/app.php`**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

- [ ] **Step 8: Configure Tailwind + TypeUI theme**

Edit `tailwind.config.js`:
```js
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"IBM Plex Sans"', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: '#e8f0fb',
                    100: '#c5dbf5',
                    200: '#9ec4ee',
                    300: '#6aa8e6',
                    400: '#3d8ee0',
                    500: '#0C5CAB',
                    600: '#0a4a8a',
                    700: '#083869',
                    800: '#062648',
                    900: '#041427',
                },
                surface: '#09090b',
                'surface-light': '#1a1a1e',
            },
            borderRadius: {
                'card': '12px',
                'btn': '8px',
            },
        },
    },
    plugins: [],
};
```

- [ ] **Step 9: Add IBM Plex Sans to layout**

Edit `resources/views/layouts/app.blade.php` — add inside `<head>`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
```

- [ ] **Step 10: Create sidebar component**

`resources/views/components/sidebar.blade.php`:
```blade
<aside class="w-64 min-h-screen bg-surface border-r border-zinc-800 p-4 flex flex-col">
    <div class="mb-8 px-3">
        <h1 class="text-lg font-semibold text-white">SMKN 2 Sumbawa</h1>
        <p class="text-xs text-zinc-400">Monitoring Kedisiplinan</p>
    </div>
    <nav class="flex-1 space-y-1">
        @foreach (Auth::user()->role === 'guru_bk' ? $menuGuruBk : $menuKepsek as $item)
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm {{ request()->is(ltrim($item['url'], '/')) ? 'bg-primary/20 text-primary-300 border border-primary/30' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                <span class="text-lg">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="pt-4 border-t border-zinc-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 px-3 py-2.5 text-sm text-zinc-400 hover:text-white w-full rounded-lg hover:bg-zinc-800">
                <span>🚪</span>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
```

- [ ] **Step 11: Update main layout with sidebar**

`resources/views/layouts/app.blade.php`:
```blade
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Monitoring Kedisiplinan')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-surface text-zinc-100 antialiased">
    @auth
        <div class="flex min-h-screen">
            <x-sidebar />
            <main class="flex-1 p-6 overflow-auto">
                @yield('content')
            </main>
        </div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth
</body>
</html>
```

- [ ] **Step 12: Create dashboard controller**

```php
<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk,kepala_sekolah']);
    }

    public function index()
    {
        return view('dashboard.index');
    }
}
```

- [ ] **Step 13: Create dashboard view**

`resources/views/dashboard/index.blade.php`:
```blade
@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-white">Dashboard</h2>
        <p class="text-sm text-zinc-400 mt-1">Selamat datang, {{ Auth::user()->nama }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider">Total Siswa</p>
            <p class="text-3xl font-bold text-white mt-2" id="total-siswa">-</p>
        </div>
        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider">Pelanggaran Bulan Ini</p>
            <p class="text-3xl font-bold text-white mt-2" id="total-pelanggaran">-</p>
        </div>
        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider">Surat Teguran Terbit</p>
            <p class="text-3xl font-bold text-white mt-2" id="total-teguran">-</p>
        </div>
        <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
            <p class="text-xs text-zinc-400 uppercase tracking-wider">Siswa Poin Tertinggi</p>
            <p class="text-3xl font-bold text-white mt-2" id="poin-tertinggi">-</p>
        </div>
    </div>

    <div class="bg-surface-light rounded-card p-4 border border-zinc-800">
        <h3 class="text-sm font-medium text-white mb-3">10 Pelanggaran Terakhir</h3>
        <table class="w-full text-sm" id="tabel-terbaru">
            <thead>
                <tr class="text-zinc-400 text-xs uppercase tracking-wider">
                    <th class="text-left pb-2 font-medium">Siswa</th>
                    <th class="text-left pb-2 font-medium">Kelas</th>
                    <th class="text-left pb-2 font-medium">Pelanggaran</th>
                    <th class="text-left pb-2 font-medium">Tanggal</th>
                </tr>
            </thead>
            <tbody id="daftar-terbaru">
                <tr><td colspan="4" class="text-zinc-500 py-4 text-center">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
<script>
fetch('/api/dashboard/stats')
    .then(r => r.json())
    .then(d => {
        document.getElementById('total-siswa').textContent = d.total_siswa;
        document.getElementById('total-pelanggaran').textContent = d.total_pelanggaran;
        document.getElementById('total-teguran').textContent = d.total_teguran;
        document.getElementById('poin-tertinggi').textContent = d.poin_tertinggi;
        document.getElementById('daftar-terbaru').innerHTML = d.terbaru.map(p =>
            `<tr class="border-t border-zinc-800">
                <td class="py-2">${p.siswa}</td>
                <td class="py-2">${p.kelas}</td>
                <td class="py-2">${p.jenis}</td>
                <td class="py-2">${p.tanggal}</td>
            </tr>`
        ).join('');
    });
</script>
@endpush
```

- [ ] **Step 14: Update routes**

`routes/web.php`:
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::get('/', function () {
    return redirect('/dashboard');
});
```

- [ ] **Step 15: Build assets and verify**

```bash
npm run build
php artisan migrate:fresh
php artisan serve
```

Verify login page renders with dark theme, sidebar shows, dashboard loads.

- [ ] **Step 16: Commit**

```bash
git init
git add .
git commit -m "feat: project scaffolding with auth and layout"
```

---

### Task 2: Database Migrations & Models

**Files:**
- Create: `database/migrations/2026_07_11_000001_create_kelas_table.php`
- Create: `database/migrations/2026_07_11_000002_create_siswa_table.php`
- Create: `database/migrations/2026_07_11_000003_create_orang_tua_table.php`
- Create: `database/migrations/2026_07_11_000004_create_jenis_pelanggaran_table.php`
- Create: `database/migrations/2026_07_11_000005_create_pelanggaran_table.php`
- Create: `database/migrations/2026_07_11_000006_create_surat_teguran_table.php`
- Create: `database/migrations/2026_07_11_000007_create_pengaturan_poin_table.php`
- Create: `app/Models/Kelas.php`
- Create: `app/Models/Siswa.php`
- Create: `app/Models/OrangTua.php`
- Create: `app/Models/JenisPelanggaran.php`
- Create: `app/Models/Pelanggaran.php`
- Create: `app/Models/SuratTeguran.php`
- Create: `app/Models/PengaturanPoin.php`

- [ ] **Step 1: Create kelas migration**

```php
// database/migrations/2026_07_11_000001_create_kelas_table.php
Schema::create('kelas', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kelas', 50);
    $table->enum('tingkat', ['X', 'XI', 'XII']);
});
```

- [ ] **Step 2: Create siswa migration**

```php
// database/migrations/2026_07_11_000002_create_siswa_table.php
Schema::create('siswa', function (Blueprint $table) {
    $table->id();
    $table->string('nisn', 20)->unique();
    $table->string('nama', 100);
    $table->foreignId('id_kelas')->constrained('kelas');
});
```

- [ ] **Step 3: Create orang_tua migration**

```php
// database/migrations/2026_07_11_000003_create_orang_tua_table.php
Schema::create('orang_tua', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();
    $table->string('nama', 100);
    $table->string('nomor_wa', 20);
    $table->enum('hubungan', ['ayah', 'ibu', 'wali']);
});
```

- [ ] **Step 4: Create jenis_pelanggaran migration**

```php
// database/migrations/2026_07_11_000004_create_jenis_pelanggaran_table.php
Schema::create('jenis_pelanggaran', function (Blueprint $table) {
    $table->id();
    $table->string('nama', 100);
    $table->integer('poin');
});
```

- [ ] **Step 5: Create pelanggaran migration**

```php
// database/migrations/2026_07_11_000005_create_pelanggaran_table.php
Schema::create('pelanggaran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();
    $table->foreignId('id_jenis')->constrained('jenis_pelanggaran');
    $table->date('tanggal');
    $table->text('keterangan')->nullable();
});
```

- [ ] **Step 6: Create surat_teguran migration**

```php
// database/migrations/2026_07_11_000006_create_surat_teguran_table.php
Schema::create('surat_teguran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('id_siswa')->constrained('siswa')->cascadeOnDelete();
    $table->enum('tingkat', ['sp1', 'sp2', 'sp3']);
    $table->integer('total_poin');
    $table->string('file_pdf', 255);
    $table->date('tanggal_terbit');
    $table->boolean('status_terkirim')->default(false);
});
```

- [ ] **Step 7: Create pengaturan_poin migration**

```php
// database/migrations/2026_07_11_000007_create_pengaturan_poin_table.php
Schema::create('pengaturan_poin', function (Blueprint $table) {
    $table->id();
    $table->enum('tingkat', ['sp1', 'sp2', 'sp3']);
    $table->integer('batas_poin');
});
```

- [ ] **Step 8: Create all models**

```php
<?php
// app/Models/Kelas.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    public $timestamps = false;
    protected $table = 'kelas';
    protected $fillable = ['nama_kelas', 'tingkat'];

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_kelas');
    }
}
```

```php
<?php
// app/Models/Siswa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    public $timestamps = false;
    protected $table = 'siswa';
    protected $fillable = ['nisn', 'nama', 'id_kelas'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function orangTua()
    {
        return $this->hasMany(OrangTua::class, 'id_siswa');
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'id_siswa');
    }

    public function suratTeguran()
    {
        return $this->hasMany(SuratTeguran::class, 'id_siswa');
    }
}
```

```php
<?php
// app/Models/OrangTua.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    public $timestamps = false;
    protected $table = 'orang_tua';
    protected $fillable = ['id_siswa', 'nama', 'nomor_wa', 'hubungan'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
```

```php
<?php
// app/Models/JenisPelanggaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    public $timestamps = false;
    protected $table = 'jenis_pelanggaran';
    protected $fillable = ['nama', 'poin'];

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'id_jenis');
    }
}
```

```php
<?php
// app/Models/Pelanggaran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    public $timestamps = false;
    protected $table = 'pelanggaran';
    protected $fillable = ['id_siswa', 'id_jenis', 'tanggal', 'keterangan'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisPelanggaran::class, 'id_jenis');
    }
}
```

```php
<?php
// app/Models/SuratTeguran.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratTeguran extends Model
{
    public $timestamps = false;
    protected $table = 'surat_teguran';
    protected $fillable = ['id_siswa', 'tingkat', 'total_poin', 'file_pdf', 'tanggal_terbit', 'status_terkirim'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
```

```php
<?php
// app/Models/PengaturanPoin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanPoin extends Model
{
    public $timestamps = false;
    protected $table = 'pengaturan_poin';
    protected $fillable = ['tingkat', 'batas_poin'];
}
```

- [ ] **Step 9: Seed default pengaturan_poin**

Create `database/seeders/DatabaseSeeder.php`:
```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pengaturan_poin')->insert([
            ['tingkat' => 'sp1', 'batas_poin' => 50],
            ['tingkat' => 'sp2', 'batas_poin' => 100],
            ['tingkat' => 'sp3', 'batas_poin' => 150],
        ]);

        DB::table('users')->insert([
            'nama' => 'Guru BK',
            'email' => 'gurubk@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);

        DB::table('users')->insert([
            'nama' => 'Kepala Sekolah',
            'email' => 'kepsek@smkn2sumbawa.sch.id',
            'password' => bcrypt('password'),
            'role' => 'kepala_sekolah',
        ]);
    }
}
```

- [ ] **Step 10: Run migrations + seeder**

```bash
php artisan migrate:fresh --seed
```

- [ ] **Step 11: Commit**

```bash
git add .
git commit -m "feat: database migrations and models"
```

---

### Task 3: Master Data CRUD (Kelas, Siswa, Orang Tua)

**Files:**
- Create: `app/Http/Controllers/KelasController.php`
- Create: `app/Http/Controllers/SiswaController.php`
- Create: `app/Http/Controllers/OrangTuaController.php`
- Create: `resources/views/kelas/index.blade.php`
- Create: `resources/views/siswa/index.blade.php`
- Create: `resources/views/orang-tua/index.blade.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: Models from Task 2
- Produces: KelasController, SiswaController, OrangTuaController with CRUD methods

- [ ] **Step 1: Create KelasController**

```php
<?php
// app/Http/Controllers/KelasController.php
namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
    }

    public function index()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('kelas.index', compact('kelas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);
        Kelas::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|in:X,XI,XII',
        ]);
        $kelas->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 2: Create kelas index view with modal CRUD**

`resources/views/kelas/index.blade.php`:
```blade
@extends('layouts.app')
@section('title', 'Data Kelas')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-semibold text-white">Data Kelas</h2>
        <p class="text-sm text-zinc-400 mt-1">Kelola data kelas</p>
    </div>
    <button onclick="bukaModal()" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">+ Tambah Kelas</button>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                <th class="text-left p-4 font-medium">Nama Kelas</th>
                <th class="text-left p-4 font-medium">Tingkat</th>
                <th class="text-right p-4 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($kelas as $k)
            <tr class="border-t border-zinc-800/50 hover:bg-zinc-800/30">
                <td class="p-4">{{ $k->nama_kelas }}</td>
                <td class="p-4">{{ $k->tingkat }}</td>
                <td class="p-4 text-right">
                    <button onclick="editKelas({{ $k->id }}, '{{ $k->nama_kelas }}', '{{ $k->tingkat }}')" class="text-primary-400 hover:text-primary-300 text-xs mr-2">Edit</button>
                    <button onclick="hapusKelas({{ $k->id }})" class="text-red-400 hover:text-red-300 text-xs">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-surface-light rounded-card border border-zinc-800 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4" id="modal-title">Tambah Kelas</h3>
        <form id="form-kelas" method="POST">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">Nama Kelas</label>
                <input type="text" name="nama_kelas" id="input-nama" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div class="mb-6">
                <label class="text-sm text-zinc-400 mb-1 block">Tingkat</label>
                <select name="tingkat" id="input-tingkat" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                    <option value="X">X</option>
                    <option value="XI">XI</option>
                    <option value="XII">XII</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="text-sm text-zinc-400 hover:text-white px-4 py-2">Batal</button>
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let modal = document.getElementById('modal');
let form = document.getElementById('form-kelas');

function bukaModal() {
    document.getElementById('modal-title').textContent = 'Tambah Kelas';
    document.getElementById('method').value = 'POST';
    form.action = '/data-kelas';
    document.getElementById('input-nama').value = '';
    document.getElementById('input-tingkat').value = 'X';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function editKelas(id, nama, tingkat) {
    document.getElementById('modal-title').textContent = 'Edit Kelas';
    document.getElementById('method').value = 'PUT';
    form.action = '/data-kelas/' + id;
    document.getElementById('input-nama').value = nama;
    document.getElementById('input-tingkat').value = tingkat;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function tutupModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function hapusKelas(id) {
    if (confirm('Yakin hapus kelas ini?')) {
        fetch('/data-kelas/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new URLSearchParams({ _method: 'DELETE' }) })
            .then(() => location.reload());
    }
}

form.onsubmit = function(e) {
    e.preventDefault();
    fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(form) })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
};

modal.onclick = function(e) { if (e.target === modal) tutupModal(); };
</script>
@endpush
@endsection
```

- [ ] **Step 3: Create SiswaController**

```php
<?php
// app/Http/Controllers/SiswaController.php
namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
    }

    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('siswa.index', compact('siswa', 'kelas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nisn' => 'required|string|max:20|unique:siswa,nisn',
            'nama' => 'required|string|max:100',
            'id_kelas' => 'required|exists:kelas,id',
        ]);
        Siswa::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'nisn' => 'required|string|max:20|unique:siswa,nisn,' . $siswa->id,
            'nama' => 'required|string|max:100',
            'id_kelas' => 'required|exists:kelas,id',
        ]);
        $siswa->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);
        // ponytail: simple Excel import, row-by-row insert.
        // Add batch validation + error reporting if file size grows beyond 500 rows.
        $rows = Excel::toCollection(null, $request->file('file'))->first();
        foreach ($rows as $row) {
            if (!isset($row[0]) || !isset($row[1]) || !isset($row[2])) continue;
            $kelas = Kelas::where('nama_kelas', $row[2])->first();
            if (!$kelas) continue;
            Siswa::updateOrCreate(
                ['nisn' => $row[0]],
                ['nama' => $row[1], 'id_kelas' => $kelas->id]
            );
        }
        return back()->with('success', 'Import berhasil');
    }
}
```

- [ ] **Step 4: Create siswa index view**

`resources/views/siswa/index.blade.php` — similar pattern to kelas but with import Excel button:

```blade
@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-semibold text-white">Data Siswa</h2>
        <p class="text-sm text-zinc-400 mt-1">Kelola data siswa</p>
    </div>
    <div class="flex gap-3">
        <form action="/data-siswa/import" method="POST" enctype="multipart/form-data" id="form-import">
            @csrf
            <input type="file" name="file" accept=".xlsx,.csv" class="hidden" id="input-file" onchange="this.form.submit()">
            <button type="button" onclick="document.getElementById('input-file').click()" class="border border-zinc-700 text-zinc-300 hover:text-white text-sm px-4 py-2 rounded-btn">Import Excel</button>
        </form>
        <button onclick="bukaModal()" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">+ Tambah Siswa</button>
    </div>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                <th class="text-left p-4 font-medium">NISN</th>
                <th class="text-left p-4 font-medium">Nama</th>
                <th class="text-left p-4 font-medium">Kelas</th>
                <th class="text-right p-4 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $s)
            <tr class="border-t border-zinc-800/50 hover:bg-zinc-800/30">
                <td class="p-4">{{ $s->nisn }}</td>
                <td class="p-4">{{ $s->nama }}</td>
                <td class="p-4">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                <td class="p-4 text-right">
                    <button onclick="editSiswa({{ $s->id }}, '{{ $s->nisn }}', '{{ $s->nama }}', {{ $s->id_kelas }})" class="text-primary-400 hover:text-primary-300 text-xs mr-2">Edit</button>
                    <button onclick="hapusSiswa({{ $s->id }})" class="text-red-400 hover:text-red-300 text-xs">Hapus</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-surface-light rounded-card border border-zinc-800 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4" id="modal-title">Tambah Siswa</h3>
        <form id="form-siswa" method="POST">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">NISN</label>
                <input type="text" name="nisn" id="input-nisn" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">Nama</label>
                <input type="text" name="nama" id="input-nama" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div class="mb-6">
                <label class="text-sm text-zinc-400 mb-1 block">Kelas</label>
                <select name="id_kelas" id="input-kelas" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                    @foreach($kelas as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="text-sm text-zinc-400 hover:text-white px-4 py-2">Batal</button>
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let modal = document.getElementById('modal');
let form = document.getElementById('form-siswa');

function bukaModal() {
    document.getElementById('modal-title').textContent = 'Tambah Siswa';
    document.getElementById('method').value = 'POST';
    form.action = '/data-siswa';
    document.getElementById('input-nisn').value = '';
    document.getElementById('input-nama').value = '';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function editSiswa(id, nisn, nama, idKelas) {
    document.getElementById('modal-title').textContent = 'Edit Siswa';
    document.getElementById('method').value = 'PUT';
    form.action = '/data-siswa/' + id;
    document.getElementById('input-nisn').value = nisn;
    document.getElementById('input-nama').value = nama;
    document.getElementById('input-kelas').value = idKelas;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function tutupModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function hapusSiswa(id) {
    if (confirm('Yakin hapus siswa ini?')) {
        fetch('/data-siswa/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new URLSearchParams({ _method: 'DELETE' }) })
            .then(() => location.reload());
    }
}

form.onsubmit = function(e) {
    e.preventDefault();
    fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(form) })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
};

modal.onclick = function(e) { if (e.target === modal) tutupModal(); };
</script>
@endpush
@endsection
```

- [ ] **Step 5: Create OrangTuaController**

```php
<?php
// app/Http/Controllers/OrangTuaController.php
namespace App\Http\Controllers;

use App\Models\OrangTua;
use App\Models\Siswa;
use Illuminate\Http\Request;

class OrangTuaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
    }

    public function index()
    {
        $siswa = Siswa::with(['kelas', 'orangTua'])->orderBy('nama')->get();
        return view('orang-tua.index', compact('siswa'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'nama' => 'required|string|max:100',
            'nomor_wa' => 'required|string|max:20',
            'hubungan' => 'required|in:ayah,ibu,wali',
        ]);
        OrangTua::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, OrangTua $orangTua)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nomor_wa' => 'required|string|max:20',
            'hubungan' => 'required|in:ayah,ibu,wali',
        ]);
        $orangTua->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(OrangTua $orangTua)
    {
        $orangTua->delete();
        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 6: Create orang-tua index view**

`resources/views/orang-tua/index.blade.php`:
```blade
@extends('layouts.app')
@section('title', 'Data Orang Tua')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-semibold text-white">Data Orang Tua / Wali</h2>
        <p class="text-sm text-zinc-400 mt-1">Input nomor WhatsApp orang tua per siswa</p>
    </div>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                <th class="text-left p-4 font-medium">Nama Siswa</th>
                <th class="text-left p-4 font-medium">Kelas</th>
                <th class="text-left p-4 font-medium">Orang Tua</th>
                <th class="text-left p-4 font-medium">No. WA</th>
                <th class="text-right p-4 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($siswa as $s)
                @forelse($s->orangTua as $o)
                <tr class="border-t border-zinc-800/50 hover:bg-zinc-800/30">
                    <td class="p-4">{{ $s->nama }}</td>
                    <td class="p-4">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                    <td class="p-4">{{ $o->nama }} ({{ $o->hubungan }})</td>
                    <td class="p-4">{{ $o->nomor_wa }}</td>
                    <td class="p-4 text-right">
                        <button onclick="editOrangTua({{ $o->id }}, {{ $s->id }}, '{{ $o->nama }}', '{{ $o->nomor_wa }}', '{{ $o->hubungan }}')" class="text-primary-400 hover:text-primary-300 text-xs mr-2">Edit</button>
                        <button onclick="hapusOrangTua({{ $o->id }})" class="text-red-400 hover:text-red-300 text-xs">Hapus</button>
                    </td>
                </tr>
                @empty
                <tr class="border-t border-zinc-800/50">
                    <td class="p-4">{{ $s->nama }}</td>
                    <td class="p-4">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                    <td colspan="3" class="p-4">
                        <button onclick="bukaModal({{ $s->id }}, '{{ $s->nama }}')" class="text-primary-400 hover:text-primary-300 text-xs">+ Tambah Orang Tua</button>
                    </td>
                </tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="bg-surface-light rounded-card border border-zinc-800 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-white mb-4" id="modal-title">Tambah Orang Tua</h3>
        <form id="form-ortu" method="POST">
            @csrf
            <input type="hidden" name="_method" id="method" value="POST">
            <input type="hidden" name="id_siswa" id="input-id-siswa">
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">Siswa</label>
                <p class="text-sm text-white" id="label-siswa"></p>
            </div>
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">Nama Orang Tua/Wali</label>
                <input type="text" name="nama" id="input-nama" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div class="mb-4">
                <label class="text-sm text-zinc-400 mb-1 block">Nomor WhatsApp</label>
                <input type="text" name="nomor_wa" id="input-wa" placeholder="628xxx" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div class="mb-6">
                <label class="text-sm text-zinc-400 mb-1 block">Hubungan</label>
                <select name="hubungan" id="input-hubungan" required
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                    <option value="ayah">Ayah</option>
                    <option value="ibu">Ibu</option>
                    <option value="wali">Wali</option>
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModal()" class="text-sm text-zinc-400 hover:text-white px-4 py-2">Batal</button>
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let modal = document.getElementById('modal');
let form = document.getElementById('form-ortu');

function bukaModal(idSiswa, namaSiswa) {
    document.getElementById('modal-title').textContent = 'Tambah Orang Tua';
    document.getElementById('method').value = 'POST';
    form.action = '/data-orang-tua';
    document.getElementById('input-id-siswa').value = idSiswa;
    document.getElementById('label-siswa').textContent = namaSiswa;
    document.getElementById('input-nama').value = '';
    document.getElementById('input-wa').value = '';
    document.getElementById('input-hubungan').value = 'ayah';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function editOrangTua(id, idSiswa, nama, wa, hubungan) {
    document.getElementById('modal-title').textContent = 'Edit Orang Tua';
    document.getElementById('method').value = 'PUT';
    form.action = '/data-orang-tua/' + id;
    document.getElementById('input-id-siswa').value = idSiswa;
    document.getElementById('input-nama').value = nama;
    document.getElementById('input-wa').value = wa;
    document.getElementById('input-hubungan').value = hubungan;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function tutupModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function hapusOrangTua(id) {
    if (confirm('Yakin hapus data orang tua ini?')) {
        fetch('/data-orang-tua/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new URLSearchParams({ _method: 'DELETE' }) })
            .then(() => location.reload());
    }
}

form.onsubmit = function(e) {
    e.preventDefault();
    fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(form) })
        .then(r => r.json()).then(d => { if (d.success) location.reload(); });
};

modal.onclick = function(e) { if (e.target === modal) tutupModal(); };
</script>
@endphp
@endsection
```

- [ ] **Step 7: Register routes**

In `routes/web.php`:
```php
Route::middleware(['auth', 'role:guru_bk'])->group(function () {
    // Kelas
    Route::get('/data-kelas', [KelasController::class, 'index'])->name('kelas.index');
    Route::post('/data-kelas', [KelasController::class, 'store']);
    Route::put('/data-kelas/{kelas}', [KelasController::class, 'update']);
    Route::delete('/data-kelas/{kelas}', [KelasController::class, 'destroy']);

    // Siswa
    Route::get('/data-siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('/data-siswa', [SiswaController::class, 'store']);
    Route::post('/data-siswa/import', [SiswaController::class, 'import']);
    Route::put('/data-siswa/{siswa}', [SiswaController::class, 'update']);
    Route::delete('/data-siswa/{siswa}', [SiswaController::class, 'destroy']);

    // Orang Tua
    Route::get('/data-orang-tua', [OrangTuaController::class, 'index'])->name('orangtua.index');
    Route::post('/data-orang-tua', [OrangTuaController::class, 'store']);
    Route::put('/data-orang-tua/{orangTua}', [OrangTuaController::class, 'update']);
    Route::delete('/data-orang-tua/{orangTua}', [OrangTuaController::class, 'destroy']);
});
```

- [ ] **Step 8: Install maatwebsite/excel**

```bash
composer require maatwebsite/excel
```

- [ ] **Step 9: Commit**

```bash
git add .
git commit -m "feat: master data CRUD for kelas, siswa, orang tua"
```

---

### Task 4: Violation Management (Jenis Pelanggaran, Pengaturan Poin, Input Pelanggaran)

**Files:**
- Create: `app/Http/Controllers/JenisPelanggaranController.php`
- Create: `app/Http/Controllers/PengaturanPoinController.php`
- Create: `app/Http/Controllers/PelanggaranController.php`
- Create: `resources/views/jenis-pelanggaran/index.blade.php`
- Create: `resources/views/pengaturan-poin/index.blade.php`
- Create: `resources/views/pelanggaran/index.blade.php`
- Create: `resources/views/pelanggaran/create.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create JenisPelanggaranController**

```php
<?php
namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
    }

    public function index()
    {
        $jenis = JenisPelanggaran::orderBy('nama')->get();
        return view('jenis-pelanggaran.index', compact('jenis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'poin' => 'required|integer|min:1',
        ]);
        JenisPelanggaran::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'poin' => 'required|integer|min:1',
        ]);
        $jenisPelanggaran->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        $jenisPelanggaran->delete();
        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 2: Create PengaturanPoinController**

```php
<?php
namespace App\Http\Controllers;

use App\Models\PengaturanPoin;
use Illuminate\Http\Request;

class PengaturanPoinController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk']);
    }

    public function index()
    {
        $pengaturan = PengaturanPoin::orderBy('batas_poin')->get();
        return view('pengaturan-poin.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'batas.*' => 'required|integer|min:1',
        ]);

        foreach ($request->batas as $id => $batas) {
            PengaturanPoin::where('id', $id)->update(['batas_poin' => $batas]);
        }

        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 3: Create PelanggaranController with PoinService logic**

```php
<?php
namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\JenisPelanggaran;
use App\Models\PengaturanPoin;
use App\Models\SuratTeguran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PelanggaranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk,kepala_sekolah'])
            ->only(['index', 'riwayat']);
        $this->middleware(['auth', 'role:guru_bk'])
            ->except(['index', 'riwayat']);
    }

    public function index()
    {
        if (auth()->user()->role === 'kepala_sekolah') {
            return redirect()->route('pelanggaran.riwayat');
        }
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();
        $jenis = JenisPelanggaran::orderBy('nama')->get();
        return view('pelanggaran.create', compact('siswa', 'jenis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:siswa,id',
            'id_jenis' => 'required|exists:jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        Pelanggaran::create($data);

        $this->cekDanTerbitkanTeguran($data['id_siswa']);

        return response()->json(['success' => true]);
    }

    public function riwayat(Request $request)
    {
        $query = Pelanggaran::with(['siswa.kelas', 'jenis']);

        if ($request->id_siswa) {
            $query->where('id_siswa', $request->id_siswa);
        }
        if ($request->id_kelas) {
            $query->whereHas('siswa', fn($q) => $q->where('id_kelas', $request->id_kelas));
        }
        if ($request->dari) {
            $query->where('tanggal', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->where('tanggal', '<=', $request->sampai);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->paginate(50);
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();

        if (auth()->user()->role === 'kepala_sekolah') {
            return view('pelanggaran.index', compact('pelanggaran', 'siswa'));
        }

        return view('pelanggaran.index', compact('pelanggaran', 'siswa'));
    }

    private function cekDanTerbitkanTeguran($idSiswa)
    {
        $totalPoin = Pelanggaran::where('id_siswa', $idSiswa)
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->sum('jenis_pelanggaran.poin');

        $pengaturan = PengaturanPoin::orderBy('batas_poin', 'desc')->get();

        foreach ($pengaturan as $p) {
            if ($totalPoin >= $p->batas_poin) {
                $exists = SuratTeguran::where('id_siswa', $idSiswa)
                    ->where('tingkat', $p->tingkat)
                    ->exists();
                if (!$exists) {
                    $siswa = Siswa::with('kelas')->find($idSiswa);
                    $pdf = Pdf::loadView('pdf.surat-teguran', [
                        'siswa' => $siswa,
                        'tingkat' => strtoupper($p->tingkat),
                        'total_poin' => $totalPoin,
                        'tanggal' => now()->format('Y-m-d'),
                    ]);

                    $filename = 'teguran_' . $p->tingkat . '_' . $idSiswa . '_' . now()->format('Ymd') . '.pdf';
                    $path = storage_path('app/public/teguran/' . $filename);
                    $pdf->save($path);

                    SuratTeguran::create([
                        'id_siswa' => $idSiswa,
                        'tingkat' => $p->tingkat,
                        'total_poin' => $totalPoin,
                        'file_pdf' => 'teguran/' . $filename,
                        'tanggal_terbit' => now()->format('Y-m-d'),
                        'status_terkirim' => false,
                    ]);

                    KirimWaTeguran::dispatch($idSiswa, $p->tingkat, $filename);
                }
                break;
            }
        }
    }
}
```

- [ ] **Step 4: Create input pelanggaran view**

`resources/views/pelanggaran/create.blade.php`:
```blade
@extends('layouts.app')
@section('title', 'Input Pelanggaran')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-white">Input Pelanggaran</h2>
    <p class="text-sm text-zinc-400 mt-1">Catat pelanggaran siswa</p>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 p-6 max-w-lg">
    <form id="form-pelanggaran">
        @csrf
        <div class="mb-4">
            <label class="text-sm text-zinc-400 mb-1 block">Siswa</label>
            <select name="id_siswa" id="input-siswa" required
                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                <option value="">Pilih siswa...</option>
                @foreach($siswa as $s)
                <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="text-sm text-zinc-400 mb-1 block">Jenis Pelanggaran</label>
            <select name="id_jenis" id="input-jenis" required
                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                <option value="">Pilih jenis...</option>
                @foreach($jenis as $j)
                <option value="{{ $j->id }}">{{ $j->nama }} ({{ $j->poin }} poin)</option>
                @endforeach
            </select>
        </div>
        <div class="mb-4">
            <label class="text-sm text-zinc-400 mb-1 block">Tanggal</label>
            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
        </div>
        <div class="mb-6">
            <label class="text-sm text-zinc-400 mb-1 block">Keterangan (opsional)</label>
            <textarea name="keterangan" rows="2"
                class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none"></textarea>
        </div>
        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-6 py-2 rounded-btn">Simpan</button>
    </form>
    <div id="notif" class="hidden mt-4 p-3 rounded-lg text-sm"></div>
</div>

@push('scripts')
<script>
document.getElementById('form-pelanggaran').onsubmit = function(e) {
    e.preventDefault();
    let notif = document.getElementById('notif');
    fetch('/pelanggaran', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_siswa: document.getElementById('input-siswa').value,
            id_jenis: document.getElementById('input-jenis').value,
            tanggal: document.querySelector('[name=tanggal]').value,
            keterangan: document.querySelector('[name=keterangan]').value,
        })
    }).then(r => r.json()).then(d => {
        if (d.success) {
            notif.className = 'mt-4 p-3 rounded-lg text-sm bg-green-900/50 text-green-300';
            notif.textContent = 'Pelanggaran berhasil dicatat!';
            notif.classList.remove('hidden');
            this.reset();
            document.querySelector('[name=tanggal]').value = '{{ date("Y-m-d") }}';
        }
    }).catch(() => {
        notif.className = 'mt-4 p-3 rounded-lg text-sm bg-red-900/50 text-red-300';
        notif.textContent = 'Gagal menyimpan pelanggaran.';
        notif.classList.remove('hidden');
    });
};
</script>
@endphp
@endsection
```

- [ ] **Step 5: Create riwayat pelanggaran view**

`resources/views/pelanggaran/index.blade.php`:
```blade
@extends('layouts.app')
@section('title', 'Riwayat Pelanggaran')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-2xl font-semibold text-white">Riwayat Pelanggaran</h2>
        <p class="text-sm text-zinc-400 mt-1">Semua catatan pelanggaran siswa</p>
    </div>
    @if(Auth::user()->role === 'guru_bk')
    <a href="/pelanggaran/input" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-4 py-2 rounded-btn">+ Input Baru</a>
    @endif
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                <th class="text-left p-4 font-medium">Siswa</th>
                <th class="text-left p-4 font-medium">Kelas</th>
                <th class="text-left p-4 font-medium">Pelanggaran</th>
                <th class="text-left p-4 font-medium">Poin</th>
                <th class="text-left p-4 font-medium">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelanggaran as $p)
            <tr class="border-t border-zinc-800/50 hover:bg-zinc-800/30">
                <td class="p-4">{{ $p->siswa->nama ?? '-' }}</td>
                <td class="p-4">{{ $p->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td class="p-4">{{ $p->jenis->nama ?? '-' }}</td>
                <td class="p-4">{{ $p->jenis->poin ?? 0 }}</td>
                <td class="p-4">{{ $p->tanggal }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $pelanggaran->links() }}
</div>
@endphp
@endsection
```

- [ ] **Step 6: Create view for jenis-pelanggaran, pengaturan-poin (CRUD modal pattern)**

`resources/views/jenis-pelanggaran/index.blade.php` — same modal CRUD pattern as kelas.

`resources/views/pengaturan-poin/index.blade.php` — simple form with 3 rows (SP1/SP2/SP3), each with number input for batas_poin, save button.

- [ ] **Step 7: Install DomPDF**

```bash
composer require barryvdh/laravel-dompdf
```

- [ ] **Step 8: Create storage link**

```bash
php artisan storage:link
```

- [ ] **Step 9: Register routes**

```php
Route::middleware(['auth', 'role:guru_bk'])->group(function () {
    Route::resource('jenis-pelanggaran', JenisPelanggaranController::class)->except(['show', 'edit', 'create']);
    Route::get('/pengaturan-poin', [PengaturanPoinController::class, 'index']);
    Route::put('/pengaturan-poin', [PengaturanPoinController::class, 'update']);

    Route::get('/pelanggaran/input', [PelanggaranController::class, 'index'])->name('pelanggaran.input');
    Route::post('/pelanggaran', [PelanggaranController::class, 'store']);
});

Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/pelanggaran', [PelanggaranController::class, 'riwayat'])->name('pelanggaran.riwayat');
});
```

- [ ] **Step 10: Commit**

```bash
git add .
git commit -m "feat: violation management and auto warning letter"
```

---

### Task 5: WhatsApp Integration + Queue

**Files:**
- Create: `app/Jobs/KirimWaTeguran.php`
- Create: `resources/views/pdf/surat-teguran.blade.php`
- Create: `app/Http/Controllers/SuratTeguranController.php`
- Create: `resources/views/surat-teguran/index.blade.php`
- Modify: `routes/web.php`
- Modify: `.env`

- [ ] **Step 1: Install WA library**

```bash
composer require kstmostofa/laravel-whatsapp
php artisan vendor:publish --tag=laravel-whatsapp-config
php artisan vendor:publish --tag=laravel-whatsapp-migrations
```

- [ ] **Step 2: Configure WA in `.env`**

```
WHATSAPP_SESSION_NAME=smkn2_monitoring
```

- [ ] **Step 3: Create surat teguran PDF template**

`resources/views/pdf/surat-teguran.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Teguran {{ $tingkat }}</title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 5px 0; }
        .content { text-align: justify; }
        .content p { line-height: 1.6; }
        .footer { margin-top: 50px; text-align: right; }
        .footer .ttd { margin-top: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>PEMERINTAH PROVINSI NUSA TENGGARA BARAT</h3>
        <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
        <h2>SMK NEGERI 2 SUMBAWA BESAR</h2>
        <p>Jalan Garuda No.102, Lempeh, Kec. Sumbawa, Kab. Sumbawa</p>
        <hr style="border: 1px solid black;">
    </div>

    <div class="content" style="margin-top: 30px;">
        <center><h4>SURAT TEGURAN {{ $tingkat }}</h4></center>
        <p>Nomor: {{ sprintf('SMK.02/ST/%s/%s', strtolower($tingkat), date('Y')) }}</p>

        <p>Yang bertanda tangan di bawah ini, Kepala SMK Negeri 2 Sumbawa Besar, memberikan teguran kepada:</p>

        <table style="margin-left: 30px;">
            <tr><td>Nama</td><td>:</td><td>{{ $siswa->nama }}</td></tr>
            <tr><td>NISN</td><td>:</td><td>{{ $siswa->nisn }}</td></tr>
            <tr><td>Kelas</td><td>:</td><td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td></tr>
        </table>

        <p style="margin-top: 20px;">Dengan ini diberitahukan bahwa siswa tersebut telah mencapai akumulasi poin pelanggaran sebesar <strong>{{ $total_poin }} poin</strong>.</p>

        <p>Berdasarkan hal tersebut, kami menerbitkan Surat Teguran {{ $tingkat }} sebagai bentuk pembinaan dan peringatan agar siswa bersangkutan dapat memperbaiki perilakunya.</p>

        <p>Demikian surat teguran ini dibuat untuk diketahui dan ditindaklanjuti.</p>
    </div>

    <div class="footer">
        <p>Sumbawa Besar, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM Y') }}</p>
        <p>Kepala Sekolah,</p>
        <div class="ttd">
            <p>___________________________</p>
            <p>NIP. ........................</p>
        </div>
    </div>
</body>
</html>
```

- [ ] **Step 4: Create KirimWaTeguran job**

```php
<?php
// app/Jobs/KirimWaTeguran.php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Siswa;
use App\Models\SuratTeguran;
use Kstmostofa\LaravelWhatsApp\WhatsApp;

class KirimWaTeguran implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $idSiswa,
        public string $tingkat,
        public string $filename
    ) {}

    public function handle(): void
    {
        $siswa = Siswa::with(['kelas', 'orangTua'])->find($this->idSiswa);
        if (!$siswa) return;

        $surat = SuratTeguran::where('id_siswa', $this->idSiswa)
            ->where('tingkat', $this->tingkat)
            ->latest('id')
            ->first();

        $filePath = storage_path('app/public/teguran/' . $this->filename);

        $pesan = "Assalamu'alaikum Wr. Wb.\n\n"
            . "Kepada Yth. Bapak/Ibu {nama}\n"
            . "Orang tua/wali dari {$siswa->nama} - Kelas {$siswa->kelas->nama_kelas}\n\n"
            . "Dengan ini kami sampaikan bahwa putra/putri Bapak/Ibu telah mencapai "
            . "akumulasi poin pelanggaran sebesar {$surat->total_poin} poin "
            . "dan diterbitkan Surat Teguran " . strtoupper($this->tingkat) . ".\n\n"
            . "Untuk informasi lebih lanjut, silakan lihat surat teguran terlampir.\n\n"
            . "Atas perhatian dan kerja samanya, kami ucapkan terima kasih.\n\n"
            . "Wassalamu'alaikum Wr. Wb.\n"
            . "SMK Negeri 2 Sumbawa Besar";

        foreach ($siswa->orangTua as $ortu) {
            try {
                $wa = new WhatsApp();
                $wa->sendDocument($ortu->nomor_wa, $filePath, "Surat_Teguran_{$this->tingkat}.pdf");
                $wa->sendMessage($ortu->nomor_wa, str_replace('{nama}', $ortu->nama, $pesan));
            } catch (\Exception $e) {
                // ponytail: log error per recipient, no retry for individual failure
                \Log::error("WA send failed for {$ortu->nomor_wa}: " . $e->getMessage());
            }
        }

        if ($surat) {
            $surat->update(['status_terkirim' => true]);
        }
    }
}
```

- [ ] **Step 5: Configure queue**

In `.env`:
```
QUEUE_CONNECTION=database
```

```bash
php artisan queue:table
php artisan migrate
```

- [ ] **Step 6: Create SuratTeguranController**

```php
<?php
namespace App\Http\Controllers;

use App\Models\SuratTeguran;
use Illuminate\Http\Request;

class SuratTeguranController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk,kepala_sekolah']);
    }

    public function index()
    {
        $teguran = SuratTeguran::with('siswa.kelas')
            ->orderBy('tanggal_terbit', 'desc')
            ->paginate(50);
        return view('surat-teguran.index', compact('teguran'));
    }
}
```

- [ ] **Step 7: Create surat teguran index view**

```blade
@extends('layouts.app')
@section('title', 'Surat Teguran')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-white">Surat Teguran</h2>
    <p class="text-sm text-zinc-400 mt-1">Daftar surat teguran yang telah diterbitkan</p>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-zinc-400 text-xs uppercase tracking-wider border-b border-zinc-800">
                <th class="text-left p-4 font-medium">Siswa</th>
                <th class="text-left p-4 font-medium">Kelas</th>
                <th class="text-left p-4 font-medium">Tingkat</th>
                <th class="text-left p-4 font-medium">Total Poin</th>
                <th class="text-left p-4 font-medium">Tanggal</th>
                <th class="text-left p-4 font-medium">Status WA</th>
                <th class="text-right p-4 font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teguran as $t)
            <tr class="border-t border-zinc-800/50 hover:bg-zinc-800/30">
                <td class="p-4">{{ $t->siswa->nama ?? '-' }}</td>
                <td class="p-4">{{ $t->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td class="p-4"><span class="px-2 py-1 rounded text-xs {{ $t->tingkat === 'sp3' ? 'bg-red-900/50 text-red-300' : ($t->tingkat === 'sp2' ? 'bg-yellow-900/50 text-yellow-300' : 'bg-blue-900/50 text-blue-300') }}">{{ strtoupper($t->tingkat) }}</span></td>
                <td class="p-4">{{ $t->total_poin }}</td>
                <td class="p-4">{{ $t->tanggal_terbit }}</td>
                <td class="p-4">{!! $t->status_terkirim ? '<span class="text-green-400">Terkirim</span>' : '<span class="text-zinc-500">Menunggu</span>' !!}</td>
                <td class="p-4 text-right">
                    <a href="{{ asset('storage/' . $t->file_pdf) }}" target="_blank" class="text-primary-400 hover:text-primary-300 text-xs">Lihat PDF</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-4">
    {{ $teguran->links() }}
</div>
@endsection
```

- [ ] **Step 8: Register routes**

```php
Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/surat-teguran', [SuratTeguranController::class, 'index'])->name('teguran.index');
});
```

- [ ] **Step 9: Run WA session pairing**

```bash
php artisan whatsapp:pair
```

Follow the QR pairing instructions (scan with WhatsApp).

- [ ] **Step 10: Commit**

```bash
git add .
git commit -m "feat: WhatsApp integration with auto-surat teguran"
```

---

### Task 6: Laporan Module + Dashboard API

**Files:**
- Create: `app/Http/Controllers/LaporanController.php`
- Create: `app/Http/Controllers/ApiDashboardController.php`
- Create: `resources/views/laporan/index.blade.php`
- Modify: `routes/web.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Create Dashboard API controller**

```php
<?php
namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pelanggaran;
use App\Models\SuratTeguran;
use Illuminate\Http\Request;

class ApiDashboardController extends Controller
{
    public function stats()
    {
        $totalSiswa = Siswa::count();
        $totalPelanggaran = Pelanggaran::whereMonth('tanggal', now()->month)->count();
        $totalTeguran = SuratTeguran::count();

        $poinTertinggi = Pelanggaran::selectRaw('id_siswa, sum(jenis_pelanggaran.poin) as total')
            ->join('jenis_pelanggaran', 'pelanggaran.id_jenis', '=', 'jenis_pelanggaran.id')
            ->groupBy('id_siswa')
            ->orderBy('total', 'desc')
            ->first();

        $terbaru = Pelanggaran::with(['siswa.kelas', 'jenis'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'siswa' => $p->siswa->nama ?? '-',
                'kelas' => $p->siswa->kelas->nama_kelas ?? '-',
                'jenis' => $p->jenis->nama ?? '-',
                'tanggal' => $p->tanggal,
            ]);

        return response()->json([
            'total_siswa' => $totalSiswa,
            'total_pelanggaran' => $totalPelanggaran,
            'total_teguran' => $totalTeguran,
            'poin_tertinggi' => $poinTertinggi ? $poinTertinggi->total : 0,
            'terbaru' => $terbaru,
        ]);
    }
}
```

- [ ] **Step 2: Create LaporanController**

```php
<?php
namespace App\Http\Controllers;

use App\Models\Pelanggaran;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:guru_bk,kepala_sekolah']);
    }

    public function index()
    {
        $siswa = Siswa::with('kelas')->orderBy('nama')->get();
        return view('laporan.index', compact('siswa'));
    }

    public function cetak(Request $request)
    {
        $query = Pelanggaran::with(['siswa.kelas', 'jenis']);

        if ($request->id_siswa) {
            $query->where('id_siswa', $request->id_siswa);
        }
        if ($request->dari) {
            $query->where('tanggal', '>=', $request->dari);
        }
        if ($request->sampai) {
            $query->where('tanggal', '<=', $request->sampai);
        }

        $pelanggaran = $query->orderBy('tanggal', 'desc')->get();

        $pdf = Pdf::loadView('pdf.laporan', [
            'pelanggaran' => $pelanggaran,
            'dari' => $request->dari,
            'sampai' => $request->sampai,
        ]);

        return $pdf->download('laporan-kedisiplinan.pdf');
    }
}
```

- [ ] **Step 3: Create laporan index view**

```blade
@extends('layouts.app')
@section('title', 'Laporan')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-semibold text-white">Laporan Kedisiplinan</h2>
    <p class="text-sm text-zinc-400 mt-1">Cetak laporan rekap pelanggaran</p>
</div>

<div class="bg-surface-light rounded-card border border-zinc-800 p-6 max-w-lg">
    <form action="/laporan/cetak" method="GET" target="_blank">
        <div class="mb-4">
            <label class="text-sm text-zinc-400 mb-1 block">Filter Siswa (opsional)</label>
            <select name="id_siswa" class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
                <option value="">Semua Siswa</option>
                @foreach($siswa as $s)
                <option value="{{ $s->id }}">{{ $s->nama }} - {{ $s->kelas->nama_kelas ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm text-zinc-400 mb-1 block">Dari Tanggal</label>
                <input type="date" name="dari" class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm text-zinc-400 mb-1 block">Sampai Tanggal</label>
                <input type="date" name="sampai" class="w-full bg-zinc-900 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-white focus:border-primary-500 focus:outline-none">
            </div>
        </div>
        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white text-sm px-6 py-2 rounded-btn">Cetak PDF</button>
    </form>
</div>
@endsection
```

- [ ] **Step 4: Create PDF laporan view**

`resources/views/pdf/laporan.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kedisiplinan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #eee; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN REKAP KEDISIPLINAN SISWA</h3>
        <p>SMK Negeri 2 Sumbawa Besar</p>
        @if($dari && $sampai)
        <p>Periode: {{ $dari }} s/d {{ $sampai }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Pelanggaran</th>
                <th>Poin</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelanggaran as $i => $p)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $p->siswa->nama ?? '-' }}</td>
                <td>{{ $p->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td>{{ $p->jenis->nama ?? '-' }}</td>
                <td>{{ $p->jenis->poin ?? 0 }}</td>
                <td>{{ $p->tanggal }}</td>
            </tr>
            @endforeach
            @if($pelanggaran->isEmpty())
            <tr><td colspan="6" style="text-align: center;">Tidak ada data</td></tr>
            @endif
        </tbody>
    </table>
</body>
</html>
```

- [ ] **Step 5: Register routes**

In `routes/api.php`:
```php
Route::middleware('auth')->get('/dashboard/stats', [ApiDashboardController::class, 'stats']);
```

In `routes/web.php`:
```php
Route::middleware(['auth', 'role:guru_bk,kepala_sekolah'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
});
```

- [ ] **Step 6: Update sidebar menu items**

Add the menu arrays to sidebar component. Pass from layout or define inline.

- [ ] **Step 7: Commit**

```bash
git add .
git commit -m "feat: laporan module and dashboard API"
```

---

### Task 7: Final Integration & Testing

**Files:**
- Modify: all files as needed

- [ ] **Step 1: Run full migration with seed**

```bash
php artisan migrate:fresh --seed
```

- [ ] **Step 2: Verify all routes**

```bash
php artisan route:list
```

Check all routes are registered and middleware applied correctly.

- [ ] **Step 3: Manual smoke test**

- Login as Guru BK → verify all menus visible
- Tambah kelas → verify appears in table
- Tambah siswa (manual + import) → verify
- Tambah orang tua + nomor WA → verify
- Tambah jenis pelanggaran → verify
- Atur batas poin SP1/SP2/SP3 → verify
- Input pelanggaran → verify auto-created surat teguran if poin ≥ batas
- Login as Kepala Sekolah → verify read-only access, different sidebar
- Cetak laporan → verify PDF downloads

- [ ] **Step 4: Run queue worker**

```bash
php artisan queue:work --daemon &
```

- [ ] **Step 5: Commit**

```bash
git add .
git commit -m "feat: final integration and testing"
```
