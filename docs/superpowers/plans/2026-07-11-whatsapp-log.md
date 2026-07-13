# WhatsApp Log Implementation Plan

> **For agentic workers:** Use subagent-driven-development or executing-plans to implement this plan task-by-task.

**Goal:** Add a message log view to the WhatsApp settings page showing history of sent/received WhatsApp messages.

**Architecture:** Single-page tabbed layout — existing "Pengaturan" tab keeps current connection settings; new "Log Pesan" tab shows a paginated table of `wa_messages` records fetched via JSON API. Uses `Kstmostofa\LaravelWhatsApp\Models\WaMessage` (already installed) for querying.

**Tech Stack:** Laravel, Alpine.js, Tailwind CSS

**Model:** `Kstmostofa\LaravelWhatsApp\Models\WaMessage` (package model, table: `wa_messages`)

## Global Constraints

- No new npm/composer dependencies
- Follow existing Alpine.js patterns in `pengaturan-whatsapp/index.blade.php`
- All new API routes under `auth` + `role:guru_bk` middleware
- Indonesian UI labels

---

### Task 1: Add logs API endpoint

**Files:**
- Modify: `app/Http/Controllers/WhatsappSettingController.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Kstmostofa\LaravelWhatsApp\Models\WaMessage`
- Produces: `GET /api/whatsapp/logs` JSON response with `{ data: [...], next_page_url, prev_page_url }`

- [ ] **Step 1: Add `logs()` method to controller**

Add after `destroy()` method:

```php
use Kstmostofa\LaravelWhatsApp\Models\WaMessage;

public function logs(Request $request)
{
    $perPage = $request->input('per_page', 15);
    $sessionId = $this->sessionId;

    $messages = WaMessage::where('session_id', $sessionId)
        ->whereIn('direction', ['outgoing', 'incoming'])
        ->orderBy('wa_timestamp', 'desc')
        ->orderBy('id', 'desc')
        ->paginate($perPage);

    $messages->getCollection()->transform(function ($msg) {
        $contact = $msg->direction === 'outgoing' ? $msg->to_id : $msg->from_id;
        $body = $msg->body;
        if ($msg->type !== 'text' && $msg->type !== 'chat') {
            $body = '[' . strtoupper($msg->type) . '] ' . ($body ?? '');
        }

        return [
            'id' => $msg->id,
            'wa_message_id' => $msg->wa_message_id,
            'direction' => $msg->direction,
            'contact' => $contact,
            'type' => $msg->type,
            'body' => $body,
            'status' => $msg->status,
            'ack' => $msg->ack,
            'wa_timestamp' => $msg->wa_timestamp ? $msg->wa_timestamp->toIso8601String() : null,
            'created_at' => $msg->created_at?->toIso8601String(),
        ];
    });

    return response()->json($messages);
}
```

- [ ] **Step 2: Add route**

In `routes/web.php`, inside `Route::prefix('api/whatsapp')->group(...)` (after the `/destroy` line):

```php
        Route::get('/logs', [WhatsappSettingController::class, 'logs']);
```

- [ ] **Step 3: Verify route works**

Run: `php artisan route:list --path=api/whatsapp`
Expected: `GET /api/whatsapp/logs` listed

---

### Task 2: Add log table to WhatsApp settings view

**Files:**
- Modify: `resources/views/pengaturan-whatsapp/index.blade.php`

- [ ] **Step 1: Add tab navigation below page header**

Add after the `<div class="flex justify-between items-center">` block and before the status cards grid:

```html
{{-- Tab Navigation --}}
<div class="flex gap-4 border-b border-gray-200">
    <button @click="tab = 'settings'"
            class="pb-2 text-sm font-medium transition-colors"
            :class="tab === 'settings' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'">
        Pengaturan
    </button>
    <button @click="tab = 'logs'; fetchLogs()"
            class="pb-2 text-sm font-medium transition-colors"
            :class="tab === 'logs' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700'">
        Log Pesan
    </button>
</div>
```

- [ ] **Step 2: Wrap existing settings content in tab condition**

Wrap everything from the status cards (`{{-- Status Cards --}}`) down to the sidecar offline notice in:

```html
<div x-show="tab === 'settings'">
    ... existing content ...
</div>
```

- [ ] **Step 3: Add log tab content after settings div**

```html
{{-- Log Tab --}}
<div x-show="tab === 'logs'">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Riwayat Pesan WhatsApp</h3>
            <button @click="fetchLogs()" class="text-xs text-purple-600 hover:text-purple-700 font-medium">
                Refresh
            </button>
        </div>

        <div x-show="logsLoading" class="p-8 text-center text-sm text-gray-500">
            <div class="inline-block w-5 h-5 border-2 border-purple-600 border-t-transparent rounded-full animate-spin mb-2"></div>
            <p>Memuat...</p>
        </div>

        <div x-show="!logsLoading && logs.length === 0" class="p-8 text-center text-sm text-gray-500">
            <p>Belum ada riwayat pesan.</p>
        </div>

        <div x-show="!logsLoading && logs.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3 font-medium">Waktu</th>
                        <th class="px-4 py-3 font-medium">Arah</th>
                        <th class="px-4 py-3 font-medium">Kontak</th>
                        <th class="px-4 py-3 font-medium">Pesan</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="msg in logs" :key="msg.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs" x-text="formatTime(msg.wa_timestamp || msg.created_at)"></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-xs font-medium"
                                      :class="msg.direction === 'outgoing' ? 'text-purple-600' : 'text-blue-600'">
                                    <span x-text="msg.direction === 'outgoing' ? 'KELUAR' : 'MASUK'"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-medium max-w-[160px] truncate" x-text="msg.contact || '-'"></td>
                            <td class="px-4 py-3 text-gray-600 max-w-[280px] truncate" x-text="msg.body || '-'"></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium"
                                      :class="statusClass(msg)">
                                    <span x-text="labelAck(msg)"></span>
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div x-show="!logsLoading && logs.length > 0" class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-500" x-text="'Halaman ' + logsCurrentPage + ' dari ' + logsLastPage"></p>
            <div class="flex gap-2">
                <button @click="loadLogsPage(logsPrevPage)"
                        x-show="logsPrevPage"
                        class="px-3 py-1 text-xs font-medium text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-md transition-colors">
                    Sebelumnya
                </button>
                <button @click="loadLogsPage(logsNextPage)"
                        x-show="logsNextPage"
                        class="px-3 py-1 text-xs font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors">
                    Selanjutnya
                </button>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Add log state and methods to Alpine component**

Replace the existing `return { ... }` object with the expanded version. Merge these new properties and methods into the existing ones:

New properties in `return { ... }`:
```js
tab: 'settings',
logs: [],
logsLoading: false,
logsCurrentPage: 1,
logsLastPage: 1,
logsNextPage: null,
logsPrevPage: null,
```

New methods:
```js
fetchLogs() {
    this.logsLoading = true;
    this.loadLogsPage('/api/whatsapp/logs');
},

loadLogsPage(url) {
    if (!url) return;
    this.logsLoading = true;
    fetch(url)
        .then(r => r.json())
        .then(d => {
            this.logs = d.data;
            this.logsCurrentPage = d.current_page;
            this.logsLastPage = d.last_page;
            this.logsNextPage = d.next_page_url;
            this.logsPrevPage = d.prev_page_url;
            this.logsLoading = false;
        })
        .catch(() => { this.logsLoading = false; });
},

formatTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
},

labelAck(msg) {
    if (msg.direction === 'incoming') return msg.status || 'Diterima';
    const ack = msg.ack;
    if (ack === -1) return 'Gagal';
    if (ack === 0) return msg.status || 'Terproses';
    if (ack === 1) return 'Terkirim';
    if (ack === 2) return 'Terbaca';
    if (ack === 3) return 'Dibaca';
    return msg.status || '-';
},

statusClass(msg) {
    if (msg.direction === 'incoming') return 'bg-blue-50 text-blue-700';
    const ack = msg.ack;
    if (ack === -1) return 'bg-red-50 text-red-700';
    if (ack >= 2) return 'bg-green-50 text-green-700';
    return 'bg-yellow-50 text-yellow-700';
},
```

> **ponytail:** Using existing `WaMessage` model from package. No custom model, no eager-loading, no search filter — add when page performance demands it.

