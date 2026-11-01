#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${PROJECT_DIR}/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=${BACKUP_RETENTION_DAYS:-14}

set -a
source "${PROJECT_DIR}/.env"
set +a

mkdir -p "${BACKUP_DIR}"

# Database backup
DB_FILE="${BACKUP_DIR}/db-${DATE}.sql.gz"
docker compose -f "${PROJECT_DIR}/docker/compose.prod.yaml" exec -T db mysqldump \
    -u root \
    -p"${DB_ROOT_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    "${DB_DATABASE}" | gzip > "${DB_FILE}"

echo "Database backup: ${DB_FILE}"

# Storage backup
STORAGE_FILE="${BACKUP_DIR}/storage-${DATE}.tar.gz"
tar -czf "${STORAGE_FILE}" -C "${PROJECT_DIR}/storage" app/attachments 2>/dev/null || true

echo "Storage backup: ${STORAGE_FILE}"

# Rotation
find "${BACKUP_DIR}" -type f -name 'db-*.sql.gz' -mtime +"${RETENTION_DAYS}" -delete
find "${BACKUP_DIR}" -type f -name 'storage-*.tar.gz' -mtime +"${RETENTION_DAYS}" -delete

echo "Backup complete."
