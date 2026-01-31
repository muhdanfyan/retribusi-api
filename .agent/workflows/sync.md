---
description: Sinkronisasi dan deploy semua repository ke VPS
---

# Sync & Deploy Workflow

Workflow untuk sinkronisasi semua repository (API, Admin, Petugas, Mobile) ke VPS baik branch main maupun dev.

## VPS Credentials
```
IP: 157.10.252.74
User: sipanda
Pass: Sipanda123#
```

## Quick Commands

### 1. Check Status Lokal (Semua Repo)
```bash
# API
cd /Users/pondokit/Herd/retribusi-api && git status

# Admin
cd /Users/pondokit/Herd/retribusi-admin && git status

# Petugas
cd /Users/pondokit/Herd/retribusi-petugas && git status

# Mobile
cd /Users/pondokit/Herd/retribusi-mobile && git status
```

### 2. Push ke GitHub (Main Branch)
```bash
# API
cd /Users/pondokit/Herd/retribusi-api && git add . && git commit -m "update" && git push origin main

# Admin  
cd /Users/pondokit/Herd/retribusi-admin && git add . && git commit -m "update" && git push origin main

# Petugas
cd /Users/pondokit/Herd/retribusi-petugas && git add . && git commit -m "update" && git push origin main

# Mobile
cd /Users/pondokit/Herd/retribusi-mobile && git add . && git commit -m "update" && git push origin main
```

### 3. Deploy API ke VPS (Production - Main)
// turbo
```bash
sshpass -p 'Sipanda123#' ssh -o StrictHostKeyChecking=no sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api && git pull origin main && php artisan migrate --force && php artisan config:cache && php artisan cache:clear"
```

### 4. Deploy API ke VPS (Development - Dev)
// turbo
```bash
sshpass -p 'Sipanda123#' ssh -o StrictHostKeyChecking=no sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api-dev && git pull origin dev && php artisan migrate --force && php artisan config:cache && php artisan cache:clear"
```

### 5. Run Local Dev Servers
// turbo
```bash
# API (port 8000)
cd /Users/pondokit/Herd/retribusi-api && php artisan serve --port=8000

# Petugas (port 5173)
cd /Users/pondokit/Herd/retribusi-petugas && npm run dev -- --port=5173

# Admin (port 5174)
cd /Users/pondokit/Herd/retribusi-admin && npm run dev -- --port=5174

# Mobile (port 5175)
cd /Users/pondokit/Herd/retribusi-mobile && npm run dev -- --port=5175
```

### 6. Database Operations on VPS
```bash
# Run migrations
sshpass -p 'Sipanda123#' ssh sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api && php artisan migrate --force"

# Run seeders
sshpass -p 'Sipanda123#' ssh sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api && php artisan db:seed"

# Clear all cache
sshpass -p 'Sipanda123#' ssh sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api && php artisan config:clear && php artisan cache:clear && php artisan route:clear"
```

### 7. Check VPS Logs
```bash
sshpass -p 'Sipanda123#' ssh sipanda@157.10.252.74 "cd /home/sipanda/retribusi-api && tail -50 storage/logs/laravel.log"
```

### 8. Sync Local Database from VPS
// turbo
```bash
# Export dari VPS
sshpass -p 'Sipanda123#' ssh -o StrictHostKeyChecking=no sipanda@157.10.252.74 "mysqldump -u sipanda -pSipanda123# retribusi --single-transaction --quick" > /tmp/vps_retribusi_backup.sql

# Import ke Local (menimpa data local!)
mysql -u root retribusi < /tmp/vps_retribusi_backup.sql
```

### 9. Sync VPS Database from Local (Reverse)
```bash
# Export dari Local
mysqldump -u root retribusi --single-transaction --quick > /tmp/local_retribusi_backup.sql

# Upload & Import ke VPS
scp /tmp/local_retribusi_backup.sql sipanda@157.10.252.74:/tmp/
sshpass -p 'Sipanda123#' ssh sipanda@157.10.252.74 "mysql -u sipanda -pSipanda123# retribusi < /tmp/local_retribusi_backup.sql"
```

### 10. Pull Latest Code to Local
// turbo
```bash
cd /Users/pondokit/Herd/retribusi-api && git pull origin main
cd /Users/pondokit/Herd/retribusi-admin && git pull origin main
cd /Users/pondokit/Herd/retribusi-petugas && git pull origin main
cd /Users/pondokit/Herd/retribusi-mobile && git pull origin main
```

## URLs

| Environment | URL |
|-------------|-----|
| API Prod | https://api.sipanda.online |
| API Dev | http://api-dev.sipanda.online |
| Admin Prod | https://admin.sipanda.online |
| Admin Dev | https://admin-dev.sipanda.online |
| Mobile Prod | https://sipanda.online |
| Mobile Dev | https://dev.sipanda.online |
| phpMyAdmin | http://157.10.252.74:8080 |

## Local URLs

| App | URL |
|-----|-----|
| API | http://localhost:8000 |
| Petugas | http://localhost:5173 |
| Admin | http://localhost:5174 |
| Mobile | http://localhost:5175 |

## GitHub Repos

- API: https://github.com/muhdanfyan/retribusi-api
- Admin: https://github.com/muhdanfyan/retribusi-admin
- Petugas: https://github.com/muhdanfyan/retribusi-petugas
- Mobile: https://github.com/muhdanfyan/retribusi-mobile
