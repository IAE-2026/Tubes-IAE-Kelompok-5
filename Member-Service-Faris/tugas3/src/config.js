const fs = require('node:fs');
const path = require('node:path');

function stripQuotes(value) {
  const trimmed = value.trim();
  if (
    (trimmed.startsWith('"') && trimmed.endsWith('"')) ||
    (trimmed.startsWith("'") && trimmed.endsWith("'"))
  ) {
    return trimmed.slice(1, -1);
  }
  return trimmed;
}

function loadEnvFile(filePath = path.join(process.cwd(), '.env')) {
  if (!fs.existsSync(filePath)) {
    return;
  }

  const content = fs.readFileSync(filePath, 'utf8');
  for (const rawLine of content.split(/\r?\n/)) {
    const line = rawLine.trim();
    if (!line || line.startsWith('#')) {
      continue;
    }

    const separator = line.indexOf('=');
    if (separator === -1) {
      continue;
    }

    const key = line.slice(0, separator).trim();
    const value = stripQuotes(line.slice(separator + 1));
    if (key && process.env[key] === undefined) {
      process.env[key] = value;
    }
  }
}

function getConfig(overrides = {}) {
  loadEnvFile(overrides.envFile);

  const baseUrl = overrides.baseUrl || process.env.IAE_BASE_URL || 'https://iae-sso.virtualfri.id';
  return {
    baseUrl: baseUrl.replace(/\/+$/, ''),
    teamId: overrides.teamId || process.env.IAE_TEAM_ID || 'TEAM-38',
    apiKey: overrides.apiKey || process.env.IAE_API_KEY || '',
    citizenEmail:
      overrides.citizenEmail || process.env.IAE_CITIZEN_EMAIL || 'warga38@ktp.iae.id',
    citizenPassword: overrides.citizenPassword || process.env.IAE_CITIZEN_PASSWORD || '',
    port: Number(overrides.port || process.env.PORT || 3000),
    dbPath: overrides.dbPath || process.env.DB_PATH || path.join('data', 'tugas3.sqlite'),
  };
}

function requireConfigured(config) {
  const missing = [];
  if (!config.apiKey) missing.push('IAE_API_KEY');
  if (!config.citizenPassword) missing.push('IAE_CITIZEN_PASSWORD');
  if (missing.length) {
    throw new Error(`Missing required environment values: ${missing.join(', ')}`);
  }
}

module.exports = {
  getConfig,
  loadEnvFile,
  requireConfigured,
};
