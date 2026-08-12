# VICOBA Manager — Standalone PHP Application

Mfumo wa kisasa wa kusimamia vikundi vya akiba na mikopo (VICOBA/VSLA), uliojengwa kama PHP application ya kujitegemea — **bila WordPress!**

## 🏗️ Muundo wa Faili

```
vicoba-app/
├── public/                # ← Document root (nginx inapeleka hapa)
│   ├── index.php          # Front controller — maombi yote yanapita hapa
│   ├── .htaccess          # Apache URL rewriting
│   └── assets/            # CSS, JS, picha (accessible publicly)
├── app/
│   ├── Core/
│   │   ├── Database.php   # PDO wrapper (replaces $wpdb)
│   │   ├── Auth.php       # Session auth (replaces wp_users)
│   │   ├── Router.php     # URL routing (replaces WP rewrite rules)
│   │   └── Response.php   # JSON/redirect helpers
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── ApiController.php
│   ├── Models/
│   │   ├── Members.php
│   │   ├── Shares.php
│   │   ├── Loans.php
│   │   ├── Domain.php     # Fines, Meetings, SocialFund, Ledger, Notifications, Audit
│   │   └── Finance.php    # Shareout, Reports, Export
│   ├── Views/
│   │   ├── auth/          # Login, Register pages
│   │   └── dashboard/     # All dashboard pages
│   └── helpers.php        # Global helper functions
├── config/
│   └── config.php         # ← EDIT THIS! DB credentials, app settings
├── migrations/
│   └── install.sql        # Run once to create all 17 tables
├── migrate/
│   └── from_wordpress.php # Run once to migrate from WP
├── cron/
│   └── overdue_check.php  # System cron (mkopo overdue + reminders)
└── nginx.conf             # Nginx server configuration
```

## 🚀 Jinsi ya Kuinstall kwenye Server

### Hatua 1: Upload Files
```bash
# Clone au upload kwenye /www/wwwroot/vikoba.mdandu.com/
rsync -avz vicoba-app/ root@server:/www/wwwroot/vikoba.mdandu.com/
```

### Hatua 2: Tengeneza Database
```bash
# Unda database mpya (au tumia ile ya WordPress)
mysql -u root -p -e "CREATE DATABASE vicoba_standalone CHARACTER SET utf8mb4;"
mysql -u root -p vicoba_standalone < migrations/install.sql
```

### Hatua 3: Sasisha Config
```bash
nano config/config.php
# Weka: db.name, db.user, db.pass, app.url, security.nida_encrypt_key
```

### Hatua 4: Sasisha Nginx
Badilisha `root` kwenye nginx config kutoka:
```nginx
root /www/wwwroot/vikoba.mdandu.com;  # ← zamani (WordPress root)
```
Kwenda:
```nginx
root /www/wwwroot/vikoba.mdandu.com/public;  # ← mpya
```

Kisha:
```bash
nginx -t && nginx -s reload
```

### Hatua 5: Hamisha Data (Kama unatumia DB ya WP)
```bash
php migrate/from_wordpress.php
```

### Hatua 6: Weka System Cron
```bash
crontab -e
# Ongeza:
0 2 * * * /usr/bin/php /www/wwwroot/vikoba.mdandu.com/cron/overdue_check.php >> /var/log/vicoba_cron.log 2>&1
```

### Hatua 7: Ruhusa za Faili
```bash
chmod -R 755 /www/wwwroot/vikoba.mdandu.com/
chmod -R 777 /www/wwwroot/vikoba.mdandu.com/public/uploads/
```

---

## 👤 Login ya Kwanza

| Field | Value |
|---|---|
| URL | https://vikoba.mdandu.com/login |
| Username | `admin` |
| Password | `Admin@1234` (**Badilisha mara moja!**) |

---

## 🔑 Roles za Mfumo

| Role | Uwezo |
|---|---|
| `super_admin` | Simamia vikundi vyote, users wote |
| `group_admin` (Mwenyekiti) | Simamia kikundi chake pekee |
| `secretary` (Katibu) | Ongeza wanachama, rekodi mikutano, mahudhurio |
| `treasurer` (Mweka Hazina) | Rekodi hisa, mikopo, faini, malipo |
| `member` (Mwanachama) | Ona tu: hisa zake, mikopo yake, historia yake |

---

## 📡 REST API Endpoints

Base URL: `https://vikoba.mdandu.com/api/`

| Method | Endpoint | Maelezo |
|---|---|---|
| GET | `/api/members` | Pata wanachama wote |
| POST | `/api/members/add` | Ongeza mwanachama |
| POST | `/api/members/update-role` | Badilisha jukumu |
| POST | `/api/shares/record` | Rekodi hisa |
| POST | `/api/loans/apply` | Omba mkopo |
| POST | `/api/loans/disburse` | Toa mkopo |
| POST | `/api/loans/repay` | Lipa mkopo |
| POST | `/api/fines/issue` | Toa faini |
| POST | `/api/meetings/create` | Unda mkutano |
| POST | `/api/meetings/attendance` | Rekodi mahudhurio |
| GET  | `/api/notifications` | Pata arifa |
| GET  | `/export/ledger-csv` | Pakua daftari la fedha |
| GET  | `/export/statement-csv` | Pakua taarifa ya mwanachama |

---

## 🔧 Requirements za Server

- PHP 8.0+ (na extensions: pdo_mysql, openssl, mbstring, json)
- MySQL 5.7+ au MariaDB 10.3+
- Nginx au Apache
- **HAPANA WordPress!**

---

## ❓ Tofauti kuu na Toleo la WP

| Zamani (WP Theme) | Sasa (Standalone PHP) |
|---|---|
| WordPress inahitajika | PHP tu |
| $wpdb | PDO moja kwa moja |
| wp_users | vc_users |
| WP Cron (unreliable) | System cron (reliable) |
| WP REST API | Custom routing |
| wp_nonce | CSRF tokens |
| canonical redirect issues | Hakuna — routes ni safi |
