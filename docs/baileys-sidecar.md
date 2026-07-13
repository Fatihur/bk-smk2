# Baileys WhatsApp Sidecar

Node.js sidecar untuk koneksi WhatsApp Web menggunakan **Baileys v6** (`@whiskeysockets/baileys`).

## Architecture

```
Laravel App (PHP) ──HTTP──> Baileys Sidecar (Node.js :3001) ──WhatsApp Protocol──> WhatsApp Web
```

Laravel mengirim request HTTP ke sidecar, sidecar yang berkomunikasi langsung dengan WhatsApp.

## Directory

```
whatsapp-sidecar/
├── package.json
├── server.js
└── .gitignore
```

## Persistent Data

Session/auth disimpan di:

```
storage/app/baileys/
```

Folder ini berisi file kredensial WhatsApp. Jangan dihapus selama session masih aktif. Hapus folder ini = logout dari WhatsApp.

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/status` | Status koneksi: `disconnected`, `connecting`, `qr`, `connected` |
| GET | `/start` | Start/Mulai koneksi, return QR jika belum terautentikasi |
| POST | `/send-text` | Kirim pesan teks `{ "target": "08xxx", "message": "text" }` |
| POST | `/send-document` | Kirim dokumen `{ "target": "08xxx", "filePath": "/abs/path/file.pdf", "filename": "doc.pdf", "caption": "text" }` |
| POST | `/stop` | Stop koneksi (tidak hapus session) |
| POST | `/logout` | Logout + hapus session |

## Cara Menjalankan

### Local Development (Windows)

```bash
cd whatsapp-sidecar
npm install
npm start
```

Atau background:

```bash
Start-Process node -ArgumentList "server.js" -WindowStyle Minimized
```

### VPS / Production (Linux)

#### 1. Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

#### 2. Install dependencies

```bash
cd /var/www/monitoring-smkn2/whatsapp-sidecar
npm install
```

#### 3. Run with PM2 (recommended)

```bash
npm install -g pm2
pm2 start server.js --name whatsapp-sidecar
pm2 save
pm2 startup
```

PM2 akan auto-restart sidecar jika crash, dan auto-start saat server reboot.

#### 4. Configure .env

Pastikan `BAILEYS_URL` di `.env` Laravel mengarah ke sidecar:

```
BAILEYS_URL=http://127.0.0.1:3001
```

Jika sidecar di server berbeda, ganti `127.0.0.1` dengan IP server sidecar.

### PM2 Commands

```bash
pm2 status                  # Cek status
pm2 logs whatsapp-sidecar   # Lihat log
pm2 restart whatsapp-sidecar
pm2 stop whatsapp-sidecar
pm2 delete whatsapp-sidecar
```

## Alur Koneksi

1. Start sidecar → status `disconnected`
2. Akses `/pengaturan-whatsapp` → klik "Mulai Koneksi" → status `qr` → scan QR
3. Setelah scan → status `connected`
4. Siap kirim pesan

## Troubleshooting

**QR tidak muncul:**
```
curl http://127.0.0.1:3001/status
```
Jika status `disconnected`, restart sidecar.

**Session expired / logout:**
```
curl -X POST http://127.0.0.1:3001/logout
```
Scan QR ulang dari halaman pengaturan.

**Port 3001 sudah dipakai:**
Ubah port di `server.js` atau set env `SIDECAR_PORT=3002`, lalu update `BAILEYS_URL` di `.env`.

**Log error sidecar:**
```bash
# Windows
Get-Content storage/logs/baileys-sidecar.err.log

# Linux
pm2 logs whatsapp-sidecar
```
