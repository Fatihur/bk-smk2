import { makeWASocket, useMultiFileAuthState, fetchLatestBaileysVersion, Browsers, DisconnectReason } from '@whiskeysockets/baileys';
import express from 'express';
import qrcode from 'qrcode';
import pino from 'pino';
import { existsSync, mkdirSync, readFileSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';
import http from 'http';

const __dirname = dirname(fileURLToPath(import.meta.url));
const PORT = parseInt(process.env.SIDECAR_PORT || '3001');
const AUTH_DIR = join(__dirname, '..', 'storage', 'app', 'baileys');
const STORAGE_DIR = join(__dirname, '..', 'storage', 'app', 'public');

if (!existsSync(AUTH_DIR)) mkdirSync(AUTH_DIR, { recursive: true });

let sock = null;
let state = { status: 'disconnected', qr: null, phone: null, pushName: null };
let qrResolve = null;

const app = express();
app.use(express.json());

function logger() {
  return pino({ level: 'silent' });
}

async function startBaileys() {
  const { version, isLatest } = await fetchLatestBaileysVersion();
  const { state: authState, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

  sock = makeWASocket({
    version,
    auth: authState,
    logger: logger(),
    browser: Browsers.windows('Desktop'),
    printQRInTerminal: false,
    markOnlineOnConnect: true,
    syncFullHistory: false,
  });

  state.status = 'connecting';

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      state.qr = await qrcode.toDataURL(qr);
      state.status = 'qr';
      if (qrResolve) { qrResolve(state.qr); qrResolve = null; }
    }

    if (connection === 'open') {
      state.status = 'connected';
      state.phone = sock.user?.id?.split(':')[0] || null;
      state.pushName = sock.user?.name || null;
      state.qr = null;
    }

    if (connection === 'close') {
      const code = lastDisconnect?.error?.output?.statusCode || 500;
      state.status = 'disconnected';
      state.qr = null;
      state.phone = null;
      state.pushName = null;

      if (code === DisconnectReason.loggedOut) {
        const fs = await import('fs');
        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
        if (!existsSync(AUTH_DIR)) mkdirSync(AUTH_DIR, { recursive: true });
      }

      setTimeout(() => startBaileys(), 3000);
    }
  });

  sock.ev.on('creds.update', saveCreds);
}

// API
app.get('/status', (req, res) => {
  res.json(state);
});

app.get('/start', (req, res) => {
  if (state.status === 'connected') {
    return res.json({ success: true, status: 'already_connected', phone: state.phone });
  }
  res.json({ success: true, status: state.status, qr: state.qr });
});

function withTimeout(promise, ms) {
  return Promise.race([
    promise,
    new Promise((_, reject) => setTimeout(() => reject(new Error('TIMEOUT')), ms)),
  ]);
}

app.post('/send-text', async (req, res) => {
  const { target, message } = req.body;
  if (!target || !message) return res.status(400).json({ success: false, reason: 'target and message required' });
  if (state.status !== 'connected') return res.status(400).json({ success: false, reason: 'WA not connected' });

  try {
    const jid = target.includes('@s.whatsapp.net') ? target : `${target}@s.whatsapp.net`;
    await withTimeout(sock.sendMessage(jid, { text: message }), 15000);
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, reason: e.message === 'TIMEOUT' ? 'send timed out' : e.message });
  }
});

app.post('/send-document', async (req, res) => {
  const { target, filePath, filename, caption } = req.body;
  if (!target || !filePath) return res.status(400).json({ success: false, reason: 'target and filePath required' });
  if (state.status !== 'connected') return res.status(400).json({ success: false, reason: 'WA not connected' });

  try {
    if (!existsSync(filePath)) return res.status(400).json({ success: false, reason: 'file not found' });

    const data = readFileSync(filePath);
    const jid = target.includes('@s.whatsapp.net') ? target : `${target}@s.whatsapp.net`;

    await withTimeout(sock.sendMessage(jid, {
      document: data,
      fileName: filename || 'document.pdf',
      mimetype: 'application/pdf',
      caption: caption || '',
    }), 15000);

    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, reason: e.message === 'TIMEOUT' ? 'send timed out' : e.message });
  }
});

app.post('/stop', async (req, res) => {
  try {
    if (sock) sock.end(DisconnectReason.loggedOut);
    state = { status: 'disconnected', qr: null, phone: null, pushName: null };
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, reason: e.message });
  }
});

app.post('/logout', async (req, res) => {
  try {
    if (sock) sock.end(DisconnectReason.loggedOut);
    const fs = await import('fs');
    fs.rmSync(AUTH_DIR, { recursive: true, force: true });
    if (!existsSync(AUTH_DIR)) mkdirSync(AUTH_DIR, { recursive: true });
    state = { status: 'disconnected', qr: null, phone: null, pushName: null };
    res.json({ success: true });
  } catch (e) {
    res.status(500).json({ success: false, reason: e.message });
  }
});

const server = http.createServer(app);

server.listen(PORT, () => {
  console.log(`WhatsApp sidecar running on port ${PORT}`);
  startBaileys();
});
