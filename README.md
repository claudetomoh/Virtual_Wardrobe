# 👗 Virtual Wardrobe & Outfit Planner

A comprehensive web application for digitally organizing your wardrobe, creating outfit combinations, and planning your weekly looks. Built with modern web technologies and featuring real-time updates, this application helps users streamline their fashion choices and track their clothing usage.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Security Features](#security-features)
- [Admin Features](#admin-features)
- [API & Real-Time Features](#api--real-time-features)

---

## 🎯 Overview

Virtual Wardrobe is a full-featured wardrobe management system designed to help users:
- **Digitize their wardrobe** by uploading photos of clothing items
- **Create and save outfit combinations** from their wardrobe
- **Plan outfits** for specific dates using an interactive calendar
- **Track clothing usage** and identify most-worn items
- **Share outfits** with friends via secure, time-limited links
- **Organize items** into custom collections
- **Analyze wardrobe statistics** to make informed fashion decisions

Perfect for fashion enthusiasts, minimalists practicing capsule wardrobes, or anyone looking to organize their closet digitally.

---

## ✨ Key Features

### 👤 User Management
- **Secure Authentication System**
  - User registration with email validation
  - Secure login with bcrypt password hashing
  - Password reset via email with time-limited tokens (30 minutes)
  - Session management with IP validation and timeout (30 minutes inactivity)
  - Role-based access control (User/Admin)

### 👕 Wardrobe Management
- **Clothing Item Organization**
  - Upload clothing items with images
  - Categorize by type: Tops, Bottoms, Shoes, Accessories
  - Add color information for better filtering
  - Mark items as favorites ⭐
  - Track items in laundry 🧺
  - Automatic wear count tracking
  - Last worn date recording
  - Delete unwanted items
  - Image preview on upload

- **Advanced Filtering & Search**
  - Filter by category
  - Filter by favorites
  - Filter by laundry status
  - Visual grid layout with hover effects

### 👔 Outfit Builder
- **Create Custom Outfits**
  - Combine tops, bottoms, shoes, and accessories
  - Visual outfit preview with item images
  - Name and save outfit combinations
  - Mark outfits as favorites
  - Track outfit wear count
  - Edit existing outfits
  - Delete unwanted outfits

- **Outfit Management**
  - View all saved outfits in a grid
  - Quick outfit preview
  - One-click "Wear" action (increments wear count)
  - Outfit sharing with expiring public links
  - Public outfit sharing page with clean preview

### 📅 Outfit Planner (Calendar & List Views)
- **Interactive Calendar Planning**
  - FullCalendar integration for monthly/weekly views
  - Drag-and-drop outfit scheduling
  - Visual outfit thumbnails on calendar
  - Click events to edit notes and season hints
  - Move events by dragging
  - Delete planned outfits
  
- **Smart Planning Features**
  - Dedicated outfit library panel
  - Drag outfits from library to calendar
  - Auto-suggest empty dates for planning
  - Season tagging (Spring, Summer, Fall, Winter, All)
  - Personal notes for each planned outfit
  - Weekly list view showing upcoming outfits

- **Real-Time Sync** (Optional)
  - Live updates via Socket.IO
  - SSE (Server-Sent Events) fallback
  - Multi-tab synchronization
  - Live notification badge
  - Activity panel showing recent updates

### 📊 Analytics & Statistics
- **Personal Wardrobe Insights**
  - Most worn clothing items (Top 5)
  - Most worn outfits (Top 5)
  - Favorite items count
  - Laundry basket status
  - Total wardrobe size
  - Visual charts and graphs

- **Admin Analytics Dashboard**
  - User growth charts (30-day trends)
  - Content creation metrics
  - Category distribution
  - User engagement statistics
  - Top active users
  - System-wide activity overview

### 🗂️ Collections
- **Custom Grouping**
  - Create named collections (e.g., "Travel Capsule", "Work Outfits")
  - Add clothing items to collections
  - Add outfits to collections
  - View collection contents
  - Remove items from collections
  - Delete collections

### 📤 Sharing System
- **Secure Outfit Sharing**
  - Generate unique share links
  - Time-limited access (configurable expiration)
  - Public preview page with outfit details
  - Share via URL without authentication
  - View shared outfit with item images and names
  - Automatic token generation and validation

### 🏠 Dashboard
- **Comprehensive Overview**
  - Quick access to all major features
  - Wardrobe summary (total items, outfits)
  - Category breakdown statistics
  - Suggested outfits carousel (7 recent)
  - Most worn items showcase
  - Favorite outfits quick access
  - Weekly planner preview (Monday-Sunday)
  - Empty date suggestions for planning
  - Real-time activity feed
  - Quick navigation buttons

---

## 🛠️ Tech Stack

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL 5.7+ / MariaDB 10.3+** - Relational database
- **PDO** - Database abstraction layer with prepared statements
- **Session Management** - Secure PHP sessions with custom handlers

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Modern styling with gradients, animations, and grid/flexbox layouts
- **Vanilla JavaScript (ES6+)** - No framework dependencies
- **FullCalendar 6.1** - Interactive calendar component
- **FontAwesome 6.4** - Icon library
- **Google Fonts (Inter)** - Typography

### Real-Time Features (Optional)
- **Node.js 14+** - JavaScript runtime for Socket server
- **Socket.IO 4.7** - WebSocket library for real-time bidirectional communication
- **Express.js** - Web framework for Node.js
- **JWT** - JSON Web Tokens for authentication

### Development & Testing
- **Playwright** - E2E testing framework
- **PM2** - Process manager for Node.js (production)

---

## 🏗️ System Architecture

### Database Schema

**All tables use `vw_` prefix** to avoid conflicts on shared hosting environments.

```
vw_users
├── vw_clothes (1:many)
├── vw_outfits (1:many)
│   ├── vw_outfits_planned (1:many)
│   └── vw_shared_outfits (1:many)
├── vw_collections (1:many)
│   └── vw_collection_items (many:many with clothes/outfits)
├── vw_password_resets (1:many)
└── vw_audit_log (1:many)

Supporting Tables:
- vw_planner_updates (real-time sync triggers)
- vw_login_attempts (security/rate limiting)
```

**Total: 11 tables** with `vw_` prefix for conflict-free deployment.

### Request Flow
```
User Request
    ↓
Apache/Nginx
    ↓
PHP Handler
    ↓
config.php (Security Headers, Session, DB Connection)
    ↓
Authentication Check (requireLogin)
    ↓
Business Logic (Controllers)
    ↓
Database Query (PDO with Prepared Statements)
    ↓
Template Rendering (header.php + content + footer.php)
    ↓
Response with CSRF Token & Socket Token
    ↓
Client-Side JS (Real-time updates via Socket.IO/SSE)
```

---

## 📦 Tech Stack

## 📥 Installation

### Prerequisites

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 7.4+ (PHP 8.0+ recommended)
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Node.js**: 14+ (optional, for real-time features)
- **Composer**: (optional, for dependency management)

### Quick Start (XAMPP - Windows/Mac/Linux)

1. **Install XAMPP**
   ```bash
   # Download from https://www.apachefriends.org
   # Install and start Apache and MySQL from XAMPP Control Panel
   ```

2. **Clone/Download Project**
   ```bash
   # Copy project to XAMPP htdocs
   cd C:\xampp\htdocs  # Windows
   # OR
   cd /opt/lampp/htdocs  # Linux
   
   git clone <repository-url> Virtual_Wardrobe
   # OR extract ZIP file here
   ```

3. **Create Database**
   ```bash
   # Option A: Via phpMyAdmin
   # 1. Open http://localhost/phpmyadmin
   # 2. Create new database 'wardrobe_app' (or use existing database)
   # 3. Import sql/db_init.sql - creates 11 tables with vw_ prefix
   
   # Option B: Via Command Line
   mysql -u root -p < sql/db_init.sql
   ```
   
   **Note:** The `sql/db_init.sql` file creates all tables with `vw_` prefix. Safe for shared hosting!

4. **Configure Environment** (Optional)
   ```bash
   # Copy example environment file
   cp .env.example .env
   
   # Edit .env with your settings
   # Default XAMPP settings usually work without changes
   ```

5. **Set File Permissions**
   ```bash
   # Linux/Mac
   chmod 755 public/uploads
   chmod 644 public/uploads/*
   
   # Windows - Usually automatic
   # Ensure uploads folder is writable
   ```

6. **Access Application**
   ```
   http://localhost/Virtual_Wardrobe/public/index.php
   
   # Or with XAMPP port 8080:
   http://localhost:8080/Virtual_Wardrobe/public/index.php
   ```

### Database Configuration

The application uses environment variables with fallbacks:

```env
# Local Development (XAMPP)
DB_HOST=127.0.0.1
DB_PORT=3307        # 3306 for standard MySQL
DB_USER=root
DB_PASS=            # Empty for XAMPP default
DB_NAME=wardrobe_app

# Production/School Server
DB_HOST=localhost
DB_PORT=3306
DB_USER=your_username
DB_PASS=your_password
DB_NAME=your_database_name
```

**Table Prefix:** All database tables use `vw_` prefix (e.g., `vw_users`, `vw_clothes`) to prevent conflicts when deploying to shared hosting environments.

Edit `src/config.php` or create `.env` file if you need custom settings.

### Optional: Real-Time Features Setup

Enable live synchronization across tabs/devices:

```bash
cd node/socket-server

# Install dependencies
npm install

# Set environment variables (optional)
# Windows PowerShell:
$env:SOCKET_API_KEY='your-secret-key'
$env:SOCKET_JWT_SECRET='your-jwt-secret'

# Linux/Mac:
export SOCKET_API_KEY='your-secret-key'
export SOCKET_JWT_SECRET='your-jwt-secret'

# Start server
npm start

# OR use PM2 for production
npm install -g pm2
pm2 start server.js --name wardrobe-socket
pm2 save
```

**Socket Server Environment Variables:**
- `SOCKET_SERVER_URL` - Server URL (default: `http://localhost:3000`)
- `SOCKET_API_KEY` - API authentication key
- `SOCKET_JWT_SECRET` - JWT signing secret (should match PHP config)
- `SOCKET_ALLOWED_ORIGINS` - Comma-separated allowed origins
- `SOCKET_EMIT_TTL` - Timestamp validity in seconds (default: 60)
- `SOCKET_RATE_MAX` - Max emits per window (default: 60)
- `SOCKET_RATE_WINDOW_MS` - Rate limit window in ms (default: 60000)

### Demo Data (Optional)

Populate with sample data for testing:

```bash
# Create demo user and sample items
php src/admin/seed_sample.php

# Demo credentials:
# Email: demo@wardrobe.local
# Password: demopass
```

---

## � Deployment

### Deploying to School/Production Server

For detailed deployment instructions, see:
- **[SCHOOL_SERVER_DEPLOYMENT.md](SCHOOL_SERVER_DEPLOYMENT.md)** - Complete step-by-step guide
- **[QUICK_DEPLOY_GUIDE.md](QUICK_DEPLOY_GUIDE.md)** - Quick reference card

**Quick Overview:**

1. **Upload Files** via FTP/cPanel to your web directory
2. **Import Database** - Use `sql/db_init.sql` in phpMyAdmin
3. **Configure .env** - Set your database credentials
4. **Set Permissions** - Make uploads folder writable
5. **Test** - Access your application URL

**Key Points:**
- ✅ All tables use `vw_` prefix - no conflicts!
- ✅ Works with shared hosting (cPanel, etc.)
- ✅ No root access required
- ✅ Compatible with existing databases

---

## �📖 Usage Guide

### For Regular Users

1. **Register/Login**
   - Visit the landing page
   - Click "Register" to create an account
   - Or login with existing credentials

2. **Add Clothing Items**
   - Navigate to "Wardrobe"
   - Click "Upload New Item"
   - Select image, name, category, and color
   - Submit to add to your wardrobe

3. **Create Outfits**
   - Go to "Outfits" section
   - Click "Create New Outfit"
   - Select items from each category
   - Name your outfit and save

4. **Plan Your Week**
   - Visit "Planner" section
   - Use Calendar View for drag-and-drop planning
   - Drag outfits from the sidebar to calendar dates
   - Click events to add notes or season tags
   - Use List View to see chronological plans

5. **Track Usage**
   - Click "Wear" on outfits to log usage
   - View statistics to see most-worn items
   - Mark favorites for quick access

6. **Share Outfits**
   - Open any outfit
   - Click "Share" button
   - Copy the generated link
   - Share with friends (link expires after set time)

7. **Organize Collections**
   - Create collections for specific purposes
   - Add clothing items or entire outfits
   - Perfect for travel, work, or seasonal capsules

### For Administrators

1. **Access Admin Panel**
   - Login with admin role account
   - Access via dashboard "Admin" link

2. **View Analytics**
   - User growth trends
   - Content creation metrics
   - System usage statistics
   - User engagement data

3. **User Management**
   - View all users
   - Toggle user roles (user/admin)
   - Delete user accounts
   - View user activity

4. **System Monitoring**
   - Check error logs
   - Monitor audit trail
   - Review security events

---

## 🔒 Security Features

### Authentication & Session Security
- **Password Security**
  - Bcrypt hashing with salt
  - Minimum complexity requirements
  - Secure password reset with time-limited tokens (30 min)
  
- **Session Protection**
  - HTTP-only session cookies
  - Session timeout (30 minutes inactivity)
  - IP address validation
  - Session regeneration on privilege change
  - Secure cookie settings (SameSite, Secure flags)

### Input Validation & Protection
- **SQL Injection Prevention**
  - PDO with prepared statements throughout
  - Parameterized queries only
  - Input sanitization and validation
  
- **XSS (Cross-Site Scripting) Prevention**
  - HTML entity encoding via `htmlspecialchars()`
  - Content Security Policy (CSP) headers
  - Output sanitization for all user content
  
- **CSRF (Cross-Site Request Forgery) Protection**
  - CSRF tokens on all forms
  - Token validation on POST requests
  - Token regeneration per session

### HTTP Security Headers
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: (configured)
Permissions-Policy: geolocation=(), microphone=(), camera=()
Strict-Transport-Security: max-age=31536000 (HTTPS only)
```

### Rate Limiting & Abuse Prevention
- **Login Attempt Limiting**
  - Max 5 failed attempts per 15 minutes (by IP)
  - Max 5 failed attempts per email
  - Automatic cleanup of old attempts
  - Brute force protection

### File Upload Security
- **Image Upload Protection**
  - File type validation (images only)
  - File size limits
  - Unique filename generation
  - Upload directory outside document root (recommended)
  - MIME type checking

### Audit Logging
- User actions logged
- Security events tracked
- Admin activity monitoring
- Database change tracking

---

## 👨‍💼 Admin Features

### User Management Dashboard
- View all registered users
- User statistics and metrics
- Role management (promote/demote)
- User deletion with cascade
- Last login tracking

### Analytics & Insights
```
📊 Available Metrics:
├── User Growth (30-day trend)
├── Content Creation (clothes & outfits)
├── Category Distribution
├── Top Active Users
├── Engagement Statistics
└── System Usage Overview
```

### Content Moderation
- View all outfits (system-wide)
- View all wardrobe items
- Delete inappropriate content
- Monitor shared content

### System Administration
- Database health monitoring
- Error log viewing
- Audit trail review
- Security event tracking
- Session management

### Demo Data Management
```bash
# Create demo accounts
php src/admin/seed_admin.php  # Creates admin account
php src/admin/seed_sample.php # Creates demo user with data

# Check demo mode
php src/admin/check_demo.php
```

---

## 🔄 API & Real-Time Features

### Real-Time Synchronization

The application supports real-time updates via two methods:

#### 1. Socket.IO (Primary - Recommended)
- **Bidirectional Communication**: WebSocket-based
- **Features**:
  - Instant planner updates across tabs/devices
  - Live notification badge
  - Connection status indicator
  - Automatic reconnection
  - JWT-based authentication
  
- **Events**:
  ```javascript
  socket.on('planner_update', (payload) => {
    // Refresh calendar and show notification
  });
  ```

#### 2. Server-Sent Events (Fallback)
- **Unidirectional Communication**: HTTP-based
- **Features**:
  - Automatic fallback if Socket.IO unavailable
  - Keep-alive messages
  - Same update events as Socket.IO
  
- **Endpoint**: `src/planner/stream.php`
- **Session Management**: Closes session immediately to prevent blocking

### REST Endpoints

#### Planner Operations
```
POST /src/planner/plan.php      - Create planned outfit
POST /src/planner/update.php    - Update plan notes/season
POST /src/planner/delete.php    - Delete planned outfit
POST /src/planner/move.php      - Move outfit to different date
GET  /src/planner/events.php    - Get all planned events (JSON)
GET  /src/planner/stream.php    - SSE stream for updates
```

#### Outfit Operations
```
POST /src/outfits/create.php    - Create new outfit
POST /src/outfits/delete.php    - Delete outfit
POST /src/outfits/toggle.php    - Toggle favorite status
POST /src/outfits/wear.php      - Log outfit wear
POST /src/outfits/share.php     - Generate share link
POST /src/outfits/unshare.php   - Revoke share link
GET  /public/share.php          - View shared outfit (public)
```

#### Wardrobe Operations
```
POST /src/clothes/upload.php    - Upload new item
POST /src/clothes/delete.php    - Delete item
POST /src/clothes/toggle.php    - Toggle favorite/laundry
POST /src/clothes/clear_laundry.php - Clear laundry status
GET  /src/clothes/list.php      - View all items
```

### WebSocket Events

**Client → Server:**
```javascript
// Trigger planner update for all user's clients
socket.emit('trigger_planner_update', {
  userId: 123,
  action: 'create|update|delete',
  planId: 456
});
```

**Server → Client:**
```javascript
// Planner changed notification
socket.on('planner_update', {
  action: 'create',
  userId: 123,
  timestamp: '2025-12-17T10:30:00Z'
});

// Connection events
socket.on('connect', () => { /* Connected */ });
socket.on('disconnect', () => { /* Disconnected */ });
socket.on('connect_error', (err) => { /* Error */ });
```

---
            Use the included test script to validate the socket server's `/emit` endpoint. From the `node/socket-server` folder:

            ```bash
            cd node/socket-server
            npm install
            SOCKET_SERVER_URL=http://localhost:3000 SOCKET_API_KEY=your_api_key SOCKET_JWT_SECRET=your_jwt_secret node test_emit.js --user 1 --event planner_update --msg "hello world"
            ```

            Replace `SOCKET_API_KEY` and `SOCKET_JWT_SECRET` with environment values matching the server and `src/config.php`.

            - When `emit_socket_event()` in PHP sends events, it now signs an emitter JWT and passes it as `X-EMITTER-JWT` and an additional `X-EMITTER-HMAC` header which is an HMAC-SHA256 hex digest of the raw JSON body (computed with `SOCKET_JWT_SECRET`). Ensure the server's `SOCKET_JWT_SECRET` is the same value in environment variables.
            ### Local E2E tests (Playwright)
            To run the E2E tests locally you'll need Playwright and Chrome/Chromium dependencies:

            ```bash
            cd node/socket-server
            npm install
            npx playwright install --with-deps
            SOCKET_SERVER_URL=http://localhost:3000 SOCKET_API_KEY=your_api_key SOCKET_JWT_SECRET=your_jwt_secret npm run test-e2e
            ```

            Replace `your_api_key` and `your_jwt_secret` with values matching your environment.

            # Push secrets to GitHub with CLI
            To help rotate secrets you can use the `push_secret.sh` script:

            ```bash
            cd node/socket-server
            PUSH_SECRETS_GH_REPO=owner/repo ./push_secret.sh
            ```
            This requires the GitHub CLI (`gh`) and correct authentication; the script generates and uploads `SOCKET_API_KEY` and `SOCKET_JWT_SECRET` for the repository.

## Usage

1. **Register**: Create a new account
2. **Upload Clothes**: Add clothing items with photos and details
3. **Browse Wardrobe**: View and filter your items by category
4. **Create Outfits**: Combine items to plan outfits
5. **View Outfits**: See all your saved outfit combinations

## Project Structure

```
Virtual_Wardrobe/
├── public/
│   ├── uploads/         # Uploaded images
│   ├── css/            # Stylesheets
│   └── index.php       # Landing page
├── src/
│   ├── auth/           # Authentication (login, register, logout)
│   ├── clothes/        # Wardrobe management
│   ├── outfits/        # Outfit creation and management
│   ├── templates/      # Header and footer
│   └── config.php      # Database connection
├── sql/
│   └── db_init.sql     # Database schema
└── README.md
```
## Local Docker (optional)

To mirror CI locally via Docker, run:

```powershell
# PowerShell (recommended)
Set-Location 'C:\xampp\htdocs\Virtual_Wardrobe'
./start_all.ps1 -Seed

# Windows (cmd) wrapper
start_all.bat -Local -Seed
```

This will start MySQL (3307), Redis (6379), PHP/Apache (8000 and 8080), and Node socket server (3001). The `-Seed` flag will seed the DB automatically. If you prefer to seed separately, run the seed command from the host or container as described previously.

If Docker is not available, `start_all.ps1` has a Local mode that starts PHP and Node locally using your XAMPP PHP and Node on PATH. Usage:

```powershell
# Start local servers (php + node) and seed DB
.
Set-Location 'C:\xampp\htdocs\Virtual_Wardrobe'
# Use -Port to customize the HTTP port (default 8080)
./start_all.ps1 -Local -Port 8080 -Seed

# Run the Playwright tests after starting
./start_all.ps1 -Local -Seed -Test

# Stop local servers
./start_all.ps1 -StopLocal
```


## 📁 Project Structure

```
Virtual_Wardrobe/
│
├── public/                          # Public web root
│   ├── index.php                   # Landing page
│   ├── share.php                   # Public outfit sharing
│   ├── css/                        # Stylesheets
│   ├── js/                         # Client-side JavaScript
│   ├── uploads/                    # User uploaded images
│   └── errors/                     # Error pages
│
├── src/                             # Application source code
│   ├── auth/                       # Authentication
│   ├── clothes/                    # Wardrobe management
│   ├── outfits/                    # Outfit management
│   ├── planner/                    # Outfit planning
│   ├── collections/                # Collections management
│   ├── stats/                      # Analytics & statistics
│   ├── admin/                      # Admin panel
│   ├── templates/                  # Reusable templates
│   └── dashboard.php               # Main dashboard
│
├── sql/                             # Database files
├── node/socket-server/              # Real-time server
├── docs/                            # Documentation
└── .env.example                     # Environment template
```

---

## 🎓 For Professors & Evaluators

### Academic Context

This project demonstrates proficiency in:

**Backend Development:**
- ✅ Object-Oriented PHP (classes, encapsulation)
- ✅ RESTful API design principles
- ✅ Database design and normalization (3NF)
- ✅ SQL query optimization with prepared statements
- ✅ Server-side session management
- ✅ File upload handling

**Frontend Development:**
- ✅ Responsive web design (mobile-first)
- ✅ Modern CSS (Grid, Flexbox, Custom Properties)
- ✅ Vanilla JavaScript (ES6+)
- ✅ AJAX/Fetch API integration
- ✅ DOM manipulation and event handling
- ✅ Third-party library integration

**Security Implementation:**
- ✅ OWASP Top 10 mitigation strategies
- ✅ Secure authentication and authorization
- ✅ Input validation and sanitization
- ✅ CSRF and XSS prevention
- ✅ SQL injection prevention
- ✅ Security headers and CSP

**Software Engineering:**
- ✅ MVC-inspired architecture
- ✅ Code organization and modularity
- ✅ Error handling and logging
- ✅ Version control (Git)
- ✅ Documentation
- ✅ End-to-end testing

### Key Technical Implementations

1. **Session Locking Resolution** - Fixed concurrent request blocking
2. **Real-Time Synchronization** - Socket.IO + SSE dual-mode
3. **Security Architecture** - Multi-layered security approach
4. **Database Optimization** - Indexed queries and efficient schema
5. **Responsive Design** - Mobile-first approach

---

## 🐛 Troubleshooting

**Page loads indefinitely:**
- Clear session files: `rm -rf /tmp/sess_*` or `del C:\xampp\tmp\sess_*`
- Restart Apache server

**Database connection failed:**
- Verify MySQL is running
- Check database credentials in `src/config.php`

**Images not uploading:**
- Check folder permissions on `public/uploads/`
- Verify PHP upload settings (upload_max_filesize, post_max_size)

**Real-time updates not working:**
- Socket.IO server may not be running
- Application automatically falls back to SSE
- Check browser console for connection errors

---

## 🚀 Deployment

See detailed guides:
- **`DEPLOYMENT_GUIDE.md`** - Production deployment
- **`PRE_HOSTING_CHECKLIST.md`** - Pre-launch checklist

### Quick Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Change all default passwords
- [ ] Enable HTTPS with SSL
- [ ] Configure database backups
- [ ] Set up error monitoring
- [ ] Enable OPcache
- [ ] Configure firewall rules

---

## 🤝 Contributing

This is an academic project. For improvements:
1. Fork the repository
2. Create a feature branch
3. Submit pull request with description

---

## 📄 License

Educational project for academic purposes.

---

## 👥 Credits

**Project**: Virtual Wardrobe & Outfit Planner  
**Year**: 2025  
**Technologies**: PHP, MySQL, JavaScript, Socket.IO, FullCalendar

---

## 🎯 Future Enhancements

- [ ] AI-powered outfit suggestions
- [ ] Weather-based recommendations
- [ ] Social features (follow users, like outfits)
- [ ] Mobile native app
- [ ] Wardrobe analytics with ML
- [ ] Shopping platform integration
- [ ] Clothing care reminders
- [ ] Sustainability tracking
- [ ] Multi-language support
- [ ] Dark/Light theme toggle

---

**Last Updated**: December 2025  
**Version**: 2.0  
**Status**: ✅ Production Ready
