const fs = require('node:fs');
const path = require('node:path');
const { DatabaseSync } = require('node:sqlite');

function normalizeDbPath(dbPath) {
  if (!dbPath || dbPath === ':memory:') {
    return ':memory:';
  }
  return path.isAbsolute(dbPath) ? dbPath : path.join(process.cwd(), dbPath);
}

function createDatabase(dbPath) {
  const normalizedPath = normalizeDbPath(dbPath);
  if (normalizedPath !== ':memory:') {
    fs.mkdirSync(path.dirname(normalizedPath), { recursive: true });
  }

  const db = new DatabaseSync(normalizedPath);
  db.exec('PRAGMA foreign_keys = ON;');
  migrate(db);
  seed(db);
  return new AppDatabase(db);
}

function migrate(db) {
  db.exec(`
    CREATE TABLE IF NOT EXISTS roles (
      email TEXT PRIMARY KEY,
      role TEXT NOT NULL,
      permissions TEXT NOT NULL,
      team_id TEXT NOT NULL,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS permits (
      id INTEGER PRIMARY KEY,
      permit_number TEXT NOT NULL UNIQUE,
      citizen_email TEXT NOT NULL,
      permit_type TEXT NOT NULL,
      address TEXT NOT NULL,
      status TEXT NOT NULL,
      amount INTEGER NOT NULL,
      created_at TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS payments (
      id INTEGER PRIMARY KEY,
      permit_id INTEGER NOT NULL,
      status TEXT NOT NULL,
      amount INTEGER NOT NULL,
      method TEXT,
      paid_at TEXT,
      receipt_number TEXT,
      created_by TEXT,
      audit_status TEXT NOT NULL DEFAULT 'PENDING',
      publish_status TEXT NOT NULL DEFAULT 'PENDING',
      created_at TEXT NOT NULL,
      updated_at TEXT NOT NULL,
      FOREIGN KEY (permit_id) REFERENCES permits(id)
    );

    CREATE TABLE IF NOT EXISTS audit_logs (
      id INTEGER PRIMARY KEY,
      payment_id INTEGER,
      team_id TEXT NOT NULL,
      activity_name TEXT NOT NULL,
      request_xml TEXT NOT NULL,
      response_xml TEXT,
      receipt_number TEXT,
      status TEXT NOT NULL,
      error TEXT,
      created_at TEXT NOT NULL,
      FOREIGN KEY (payment_id) REFERENCES payments(id)
    );

    CREATE TABLE IF NOT EXISTS published_events (
      id INTEGER PRIMARY KEY,
      payment_id INTEGER,
      routing_key TEXT NOT NULL,
      event_json TEXT NOT NULL,
      response_json TEXT,
      status TEXT NOT NULL,
      error TEXT,
      created_at TEXT NOT NULL,
      FOREIGN KEY (payment_id) REFERENCES payments(id)
    );
  `);
}

function seed(db) {
  const now = '2026-06-12T00:00:00.000Z';
  db.prepare(`
    INSERT OR IGNORE INTO roles (email, role, permissions, team_id, created_at)
    VALUES (?, ?, ?, ?, ?)
  `).run(
    'warga38@ktp.iae.id',
    'WARGA',
    JSON.stringify(['permit:read', 'permit:pay']),
    'TEAM-38',
    now,
  );

  db.prepare(`
    INSERT OR IGNORE INTO permits
      (id, permit_number, citizen_email, permit_type, address, status, amount, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  `).run(
    1,
    'IMB-DC-2026-0038',
    'warga38@ktp.iae.id',
    'Izin Mendirikan Bangunan Digital City',
    'Jl. Integrasi Aplikasi No. 38, Enterprise Digital City',
    'APPROVED_WAITING_PAYMENT',
    350000,
    now,
  );

  db.prepare(`
    INSERT OR IGNORE INTO payments
      (id, permit_id, status, amount, method, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  `).run(1, 1, 'PENDING', 350000, 'VIRTUAL_ACCOUNT', now, now);
}

function parseJson(value, fallback) {
  if (!value) return fallback;
  try {
    return JSON.parse(value);
  } catch {
    return fallback;
  }
}

function mapRole(row) {
  if (!row) return null;
  return {
    email: row.email,
    role: row.role,
    permissions: parseJson(row.permissions, []),
    teamId: row.team_id,
    createdAt: row.created_at,
  };
}

function mapPermit(row) {
  if (!row) return null;
  return {
    id: row.id,
    permitNumber: row.permit_number,
    citizenEmail: row.citizen_email,
    permitType: row.permit_type,
    address: row.address,
    permitStatus: row.permit_status || row.status,
    amount: row.amount,
    createdAt: row.created_at,
    payment: row.payment_id
      ? {
          id: row.payment_id,
          status: row.payment_status,
          amount: row.payment_amount,
          method: row.method,
          paidAt: row.paid_at,
          receiptNumber: row.receipt_number,
          auditStatus: row.audit_status,
          publishStatus: row.publish_status,
        }
      : null,
  };
}

class AppDatabase {
  constructor(db) {
    this.db = db;
  }

  close() {
    this.db.close();
  }

  getRoleByEmail(email) {
    return mapRole(this.db.prepare('SELECT * FROM roles WHERE email = ?').get(email));
  }

  listPermitsByEmail(email) {
    const rows = this.db.prepare(`
      SELECT
        p.id,
        p.permit_number,
        p.citizen_email,
        p.permit_type,
        p.address,
        p.status AS permit_status,
        p.amount,
        p.created_at,
        pay.id AS payment_id,
        pay.status AS payment_status,
        pay.amount AS payment_amount,
        pay.method,
        pay.paid_at,
        pay.receipt_number,
        pay.audit_status,
        pay.publish_status
      FROM permits p
      LEFT JOIN payments pay ON pay.permit_id = p.id
      WHERE p.citizen_email = ?
      ORDER BY p.id
    `).all(email);
    return rows.map(mapPermit);
  }

  getPermitPayment(permitId, email) {
    const row = this.db.prepare(`
      SELECT
        p.id,
        p.permit_number,
        p.citizen_email,
        p.permit_type,
        p.address,
        p.status AS permit_status,
        p.amount,
        p.created_at,
        pay.id AS payment_id,
        pay.status AS payment_status,
        pay.amount AS payment_amount,
        pay.method,
        pay.paid_at,
        pay.receipt_number,
        pay.audit_status,
        pay.publish_status
      FROM permits p
      INNER JOIN payments pay ON pay.permit_id = p.id
      WHERE p.id = ? AND p.citizen_email = ?
    `).get(Number(permitId), email);
    return mapPermit(row);
  }

  insertAuditLog(record) {
    const result = this.db.prepare(`
      INSERT INTO audit_logs
        (payment_id, team_id, activity_name, request_xml, response_xml, receipt_number, status, error, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    `).run(
      record.paymentId,
      record.teamId,
      record.activityName,
      record.requestXml,
      record.responseXml || null,
      record.receiptNumber || null,
      record.status,
      record.error || null,
      record.createdAt,
    );
    return Number(result.lastInsertRowid);
  }

  markPaymentPaid(paymentId, update) {
    this.db.prepare(`
      UPDATE payments
      SET status = 'PAID',
          method = ?,
          paid_at = ?,
          receipt_number = ?,
          created_by = ?,
          audit_status = 'SUCCESS',
          publish_status = ?,
          updated_at = ?
      WHERE id = ?
    `).run(
      update.method,
      update.paidAt,
      update.receiptNumber,
      update.createdBy,
      update.publishStatus || 'PENDING',
      update.updatedAt,
      paymentId,
    );
  }

  markPaymentPublishStatus(paymentId, status, updatedAt) {
    this.db.prepare(`
      UPDATE payments
      SET publish_status = ?, updated_at = ?
      WHERE id = ?
    `).run(status, updatedAt, paymentId);
  }

  insertPublishedEvent(record) {
    const result = this.db.prepare(`
      INSERT INTO published_events
        (payment_id, routing_key, event_json, response_json, status, error, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    `).run(
      record.paymentId,
      record.routingKey,
      JSON.stringify(record.event),
      record.response ? JSON.stringify(record.response) : null,
      record.status,
      record.error || null,
      record.createdAt,
    );
    return Number(result.lastInsertRowid);
  }
}

module.exports = {
  AppDatabase,
  createDatabase,
};
