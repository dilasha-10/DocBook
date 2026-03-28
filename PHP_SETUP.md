# DocBook Auth System - PHP Setup Guide (XAMPP)

## 🚀 Converting from Node.js to Pure PHP

Your system is now **100% vanilla HTML, CSS, JavaScript, and PHP** - no Node.js required!

---

## 📋 What Changed

### Backend: Node.js ❌ → PHP ✅
- **Removed:** `server.js`, `.env`, `package.json`, `node_modules/`
- **Added:** PHP endpoints in `api/` folder
  - `api/config.php` - Configuration and helpers
  - `api/register.php` - Register new user
  - `api/login.php` - Login user
  - `api/verify.php` - Verify token
  - `api/forgot-password.php` - Password recovery

### Frontend: API URLs updated ✅
- `script.js` - Updated to call `api/login.php`
- `auth-pages.js` - Updated to call `api/register.php` and `api/forgot-password.php`
- `dashboard.html` - Updated to call `api/verify.php`

### Storage: In-Memory → File-Based ✅
- `users.json` - User storage (created automatically)
- `tokens.json` - Token storage (created automatically)
- In production, replace with real database

---

## 🖥️ Setup with XAMPP

### Option 1: Put Files in XAMPP's `htdocs` Folder

1. **Copy all files to XAMPP's web root:**
   ```bash
   cp -r /home/tansang/Downloads/rajani/* /opt/lampp/htdocs/rajani/
   ```
   
   Or on Windows:
   ```
   Copy C:\...\rajani\*.* C:\xampp\htdocs\rajani\
   ```

2. **Start XAMPP** (from XAMPP Control Panel)
   - Start Apache
   - Start MySQL (optional, not needed for this)

3. **Open in browser:**
   ```
   http://localhost/rajani/index.html
   ```

### Option 2: Run Without XAMPP (Use PHP Built-in Server)

If you prefer not to use XAMPP, use PHP's built-in development server:

```bash
cd /home/tansang/Downloads/rajani
php -S localhost:8000
```

Then open: `http://localhost:8000/index.html`

---

## 📁 Project Structure

```
rajani/
├── index.html                 # Login page
├── sign-up.html              # Registration page
├── forgot-password.html      # Password recovery
├── dashboard.html            # User dashboard
├── styles.css                # Login styles
├── auth-pages.css            # Other auth styles
├── script.js                 # Login JavaScript (UPDATED)
├── auth-pages.js             # Auth pages JavaScript (UPDATED)
├── .htaccess                 # Apache rewrite rules
│
├── api/
│   ├── config.php            # Configuration and helpers (NEW)
│   ├── register.php          # Register endpoint (NEW)
│   ├── login.php             # Login endpoint (NEW)
│   ├── verify.php            # Token verification (NEW)
│   └── forgot-password.php   # Password reset (NEW)
│
├── users.json                # User storage (created automatically)
├── tokens.json               # Token storage (created automatically)
│
├── API_REFERENCE.md
├── README.md
├── QUICKSTART.md
├── TESTING.md
└── ...docs
```

---

## 🧪 Testing

### 1. Register a User
Go to: `http://localhost/rajani/sign-up.html`
- Name: John Doe
- Email: john@test.com
- Password: test123456
- Click "Sign up"

### 2. Login with Test Account
Go to: `http://localhost/rajani/index.html`
- Email: john@test.com
- Password: test123456
- Role: Patient
- Click "Log in"

### 3. Test with cURL

**Register:**
```bash
curl -X POST http://localhost/rajani/api/register.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@test.com",
    "password": "jane123456",
    "confirmPassword": "jane123456",
    "role": "Doctor"
  }'
```

**Login:**
```bash
curl -X POST http://localhost/rajani/api/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "identifier": "jane@test.com",
    "password": "jane123456",
    "role": "Doctor"
  }'
```

**Verify Token:**
```bash
curl -X GET http://localhost/rajani/api/verify.php \
  -H "Authorization: Bearer <token>"
```

---

## 🔐 Security Features

✅ **Password Hashing**
- Using PHP's built-in `password_hash()` with BCRYPT
- Verified with `password_verify()`

✅ **Token System**
- Simple token-based authentication
- 24-hour token expiration
- Stored in `tokens.json`

✅ **Input Validation**
- Email format validation
- Required field checks
- Password matching
- Role validation

✅ **Error Handling**
- Consistent JSON error responses
- Generic error messages (no info leakage)
- Appropriate HTTP status codes

---

## 📝 API Endpoints

### POST `/api/register.php`
Register new user with all required fields

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "confirmPassword": "password123",
  "role": "Patient"
}
```

### POST `/api/login.php`
Login with email and password

```json
{
  "identifier": "john@example.com",
  "password": "password123",
  "role": "Patient"
}
```

### GET `/api/verify.php`
Verify token with Authorization header

```
Authorization: Bearer <token>
```

### POST `/api/forgot-password.php`
Request password reset

```json
{
  "identifier": "john@example.com"
}
```

---

## 💾 Data Storage

### User Storage (`users.json`)
```json
{
  "1": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "$2y$10$...", // bcrypt hash
    "role": "Patient",
    "createdAt": "2026-03-28T15:45:00+00:00"
  }
}
```

### Token Storage (`tokens.json`)
```json
{
  "abc123def456...": {
    "userId": 1,
    "email": "john@example.com",
    "role": "Patient",
    "created": 1711609500,
    "expires": 1711695900
  }
}
```

---

## 🚀 Moving to Production

### Replace File-Based Storage with Database

1. **Create a MySQL database:**
   ```sql
   CREATE DATABASE docbook;
   
   CREATE TABLE users (
     id INT PRIMARY KEY AUTO_INCREMENT,
     name VARCHAR(255) NOT NULL,
     email VARCHAR(255) UNIQUE NOT NULL,
     password VARCHAR(255) NOT NULL,
     role ENUM('Patient', 'Doctor') NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   
   CREATE TABLE tokens (
     token VARCHAR(255) PRIMARY KEY,
     user_id INT NOT NULL,
     email VARCHAR(255) NOT NULL,
     role ENUM('Patient', 'Doctor') NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     expires_at TIMESTAMP,
     FOREIGN KEY (user_id) REFERENCES users(id)
   );
   ```

2. **Update `api/config.php`** to use database instead of JSON files

3. **Use MySQLi or PDO** for database queries

---

## ⚠️ Important Notes

### File Permissions
Make sure the `api/` folder is writable:
```bash
chmod 755 /home/tansang/Downloads/rajani/api/
```

### Apache
If using XAMPP, make sure:
- Apache is running
- `mod_rewrite` is enabled (for `.htaccess`)
- PHP is enabled

### Development vs Production
- **Development:** Current file-based storage is fine for testing
- **Production:** Must use real database for security and scalability

---

## 🐛 Troubleshooting

### "API not found" Error
- ✅ Check if Apache is running
- ✅ Check if files are in correct location
- ✅ Check file permissions

### Login/Register not working
- ✅ Check if `api/` folder is writable
- ✅ Check PHP error logs
- ✅ Open browser console (F12) to see errors

### Token verification failing
- ✅ Check if token is valid (not expired)
- ✅ Check if `tokens.json` exists and is readable

---

## 📚 Next Steps

1. ✅ Copy files to XAMPP `htdocs`
2. ✅ Start Apache
3. ✅ Open `http://localhost/rajani/index.html`
4. ✅ Test registration and login
5. ✅ For production, set up MySQL database

---

## ✨ Summary

Your DocBook Auth System is now **pure vanilla:**
- ✅ HTML - No frameworks
- ✅ CSS - No preprocessors
- ✅ JavaScript - No libraries (except Fetch API)
- ✅ PHP - Simple, fast, and secure

**No Node.js, no npm, no dependencies required!**

Ready to run on any PHP-enabled server (XAMPP, Linux, etc.)

---

**Need help?** Check the API_REFERENCE.md or README.md for detailed documentation.
