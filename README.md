# 👗 Virtual Wardrobe & Outfit Planner

A comprehensive web application for digitally organizing your wardrobe, creating outfit combinations, and planning your weekly looks. Built with modern web technologies and featuring real-time updates, this application helps users streamline their fashion choices and track their clothing usage.

## 🌐 Live Application

**🔗 Access the Application:**  
**http://169.239.251.102:341/~tomoh.ikfingeh/Virtual_Wardrobe/public/index.php**

**Demo User Credentials:**
- Email: demo@example.com
- Password: Demo@1234

**Admin Credentials:**
- Email: admin@example.com
- Password: Admin123!

**GitHub Repository:** https://github.com/claudetomoh/Virtual_Wardrobe

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Security Features](#security-features)
- [Admin Features](#admin-features)
- [API & Real-Time Features](#api--real-time-features)
- [Project Structure](#project-structure)
- [Deployment](#deployment)

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

### Technical Highlights
- 🗄️ **11 Database Tables** with proper relationships and foreign key constraints
- 🔐 **Enterprise-Grade Security** with CSRF protection, XSS prevention, and bcrypt password hashing
- 📱 **Fully Responsive Design** optimized for mobile, tablet, and desktop
- ⚡ **Real-Time Updates** via Socket.IO and Server-Sent Events (SSE)
- 🎨 **Professional UI/UX** with custom modal dialogs and smooth animations
- 🔄 **RESTful API** endpoints for all CRUD operations
- 📊 **Advanced Analytics** dashboard with usage tracking and insights

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

### Backend Technologies
- **PHP 8.0+** - Core server-side programming language
  - Object-Oriented Programming (OOP) architecture
  - PDO for database abstraction
  - Password hashing with bcrypt (cost factor 12)
  - Session management with custom security handlers
  - Error handling and logging
  
- **MySQL 5.7+ / MariaDB 10.3+** - Relational database management system
  - 11 normalized tables (3NF)
  - Foreign key constraints with CASCADE/SET NULL
  - Indexed columns for optimized queries
  - UNIQUE constraints for data integrity
  - JSON data type support for flexible metadata
  - Full ACID compliance

- **Apache / Nginx** - Web server
  - mod_rewrite support (optional)
  - .htaccess configuration
  - Security headers configuration

### Frontend Technologies
- **HTML5** - Semantic markup structure
  - Form validation attributes
  - Accessibility (ARIA labels)
  - SEO-friendly structure
  
- **CSS3** - Modern styling and layout
  - CSS Grid for complex layouts
  - Flexbox for component alignment
  - CSS Custom Properties (variables)
  - Responsive media queries (mobile-first)
  - Smooth animations and transitions
  - Gradient backgrounds
  - Box shadows and visual effects
  
- **JavaScript (ES6+)** - Client-side interactivity
  - Vanilla JavaScript (no jQuery dependency)
  - Fetch API for AJAX requests
  - Promises and async/await
  - Event delegation
  - DOM manipulation
  - LocalStorage for client-side data
  - Form validation
  - Modal dialog system

### UI/UX Libraries & Tools
- **FullCalendar 6.1.10** - Interactive calendar component
  - Drag-and-drop event scheduling
  - Month, week, and day views
  - Event rendering with custom templates
  - Click and hover interactions
  
- **FontAwesome 6.4.0** - Professional icon library
  - 1000+ icons used throughout the app
  - Scalable vector icons
  - Consistent visual language
  
- **Google Fonts (Inter)** - Modern typography
  - Clean, readable font family
  - Multiple font weights (400-700)
  - Optimized for screen display

### Real-Time Communication Stack
- **Node.js 18+** - JavaScript runtime for real-time server
  - Event-driven architecture
  - Non-blocking I/O
  - High concurrency support
  
- **Socket.IO 4.7.2** - WebSocket library
  - Real-time bidirectional communication
  - Automatic reconnection
  - Room-based messaging
  - Fallback to polling if WebSocket unavailable
  
- **Express.js 4.18+** - Minimal web framework
  - Routing for Socket.IO endpoints
  - Middleware support
  - CORS configuration
  
- **JWT (jsonwebtoken)** - Authentication tokens
  - Secure token-based authentication
  - HMAC-SHA256 signing
  - Expiration management

### Security Technologies
- **bcrypt** - Password hashing algorithm
  - Salt generation
  - Cost factor 12 for strong security
  - One-way hashing
  
- **CSRF Tokens** - Cross-Site Request Forgery protection
  - Token generation per session
  - Token validation on POST requests
  
- **Prepared Statements (PDO)** - SQL injection prevention
  - Parameter binding
  - Query parameterization
  
- **HTML Sanitization** - XSS prevention
  - htmlspecialchars() for output escaping
  - Input validation and filtering

### Development & Testing Tools
- **Git** - Version control system
  - GitHub for remote repository
  - Branch management
  - Commit history
  
- **Playwright** - End-to-end testing framework
  - Automated browser testing
  - Multi-browser support (Chromium, Firefox, WebKit)
  - Screenshot capture
  - Test reporting
  
- **PM2** - Process manager for Node.js
  - Auto-restart on failure
  - Load balancing
  - Log management
  - Monitoring dashboard

### Deployment & Infrastructure
- **XAMPP** - Local development stack
  - Apache HTTP Server
  - MySQL/MariaDB
  - PHP interpreter
  - phpMyAdmin for database management
  
- **Shared Hosting** - Production deployment
  - cPanel support
  - FTP/SFTP file transfer
  - MySQL remote access
  - SSL/TLS certificates

### Data Formats & Protocols
- **JSON** - Data interchange format
  - API responses
  - Configuration files
  - Database metadata storage
  
- **RESTful API** - Architectural style
  - HTTP methods (GET, POST, DELETE)
  - JSON request/response bodies
  - Stateless communication
  
- **WebSocket** - Full-duplex communication protocol
  - Real-time updates
  - Low latency
  - Persistent connections

### File Formats Supported
- **Images**: JPEG, JPG, PNG, GIF, WebP
- **Maximum Upload Size**: 5MB per image
- **Image Processing**: PHP GD library for validation

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

### Detailed Database Schema

#### Core Tables

1. **vw_users** - User accounts and authentication
   - Stores user credentials (bcrypt hashed passwords)
   - Role-based access control (user/admin)
   - Email uniqueness constraint
   - Timestamps for account creation

2. **vw_clothes** - Individual clothing items
   - Links to user_id (owner)
   - Categories: Tops, Bottoms, Shoes, Accessories
   - Favorite and laundry status tracking
   - Wear count and last worn date
   - Image path storage

3. **vw_outfits** - Outfit combinations
   - References to 4 clothing items (top, bottom, shoe, accessory)
   - SET NULL on clothing deletion (outfit preserved)
   - Favorite status and wear tracking
   - Optional outfit title/name

4. **vw_outfits_planned** - Calendar scheduling
   - Links outfits to specific dates
   - Season hints (Spring, Summer, Fall, Winter, All)
   - Personal notes per plan
   - UNIQUE constraint (user + outfit + date)

5. **vw_shared_outfits** - Public sharing system
   - Time-limited share tokens (7 days default)
   - Unique token generation
   - Expiration tracking
   - Public visibility toggle

6. **vw_password_resets** - Password recovery
   - One-time use tokens
   - 30-minute expiration
   - UNIQUE token constraint
   - Deleted after successful reset

7. **vw_audit_log** - Activity logging
   - Tracks all user actions
   - JSON metadata storage
   - Preserved even if user deleted
   - Security monitoring

8. **vw_planner_updates** - Real-time sync
   - One row per user
   - Last update timestamp
   - Triggers Socket.IO events
   - Auto-update on change

#### Feature Tables

9. **vw_collections** - Custom groupings
   - User-created collections
   - Named groups (e.g., "Travel Capsule")
   - Timestamp tracking

10. **vw_collection_items** - Junction table
    - Links clothing/outfits to collections
    - Polymorphic relationship (item_type ENUM)
    - Many-to-many relationship

#### Security Tables

11. **vw_login_attempts** - Rate limiting
    - IP address tracking (IPv6 support)
    - Success/failure status
    - 15-minute rolling window
    - Brute force prevention

### Entity Relationship Diagram

```
┌─────────────┐
│  vw_users   │
│ (id PK)     │
└──────┬──────┘
       │
       ├───────────────────────────────────────┐
       │                                       │
       ▼                                       ▼
┌─────────────┐                        ┌──────────────┐
│ vw_clothes  │                        │ vw_outfits   │
│ (id PK)     │◄───┐                   │ (id PK)      │
│ user_id FK  │    │                   │ user_id FK   │
└─────────────┘    │                   │ top_id FK    │────┐
                   │                   │ bottom_id FK │────┼──►(References vw_clothes)
                   │                   │ shoe_id FK   │────┤
                   └───────────────────│ accessory_id │────┘
                                       └──────┬───────┘
                                              │
                                              ├──────────────┐
                                              ▼              ▼
                                    ┌──────────────────┐  ┌──────────────────┐
                                    │ vw_outfits_      │  │ vw_shared_       │
                                    │ planned          │  │ outfits          │
                                    │ (id PK)          │  │ (id PK)          │
                                    │ user_id FK       │  │ outfit_id FK     │
                                    │ outfit_id FK     │  │ user_id FK       │
                                    │ planned_for DATE │  │ token UNIQUE     │
                                    └──────────────────┘  │ expires_at       │
                                                          └──────────────────┘
```

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
```

---

## 🚀 Deployment

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

#### Access Admin Panel
- **Login Credentials**: admin@example.com / Admin123!
- Access via Dashboard → "Admin Panel" button
- Requires admin role in database

#### Admin Dashboard Features

**1. User Management**
   - **View All Users**
     - Complete list of registered users
     - User details: name, email, role, registration date
     - Last login tracking
     - Account status
   
   - **Role Management**
     - Promote users to admin role
     - Demote admins to user role
     - One-click role toggle
     - Instant changes with confirmation
   
   - **User Deletion**
     - Delete user accounts
     - CASCADE deletion of all user data
     - Confirmation modal before deletion
     - Cannot delete own account

**2. System Analytics**
   - **User Growth Metrics**
     - 30-day user registration trends
     - Daily signup statistics
     - Total user count
     - Active user percentage
   
   - **Content Statistics**
     - Total clothing items uploaded (system-wide)
     - Total outfits created (all users)
     - Average items per user
     - Category distribution charts
   
   - **Engagement Metrics**
     - Most active users (by content creation)
     - Top 5 users by outfit count
     - User activity heatmap
     - Login frequency statistics

**3. Content Overview**
   - **System-Wide Content**
     - View all outfits across all users
     - View all clothing items
     - Category breakdown statistics
     - Popular color trends
   
   - **Moderation Tools**
     - Review recent uploads
     - Monitor shared content
     - Track user activity patterns

**4. Security Monitoring**
   - **Audit Log Viewer**
     - Recent user actions
     - Security events
     - Failed login attempts
     - Suspicious activity alerts
   
   - **Login Attempt Tracking**
     - IP-based rate limiting status
     - Blocked IP addresses
     - Brute force detection
     - Failed authentication logs

**5. Database Management**
   - **Quick Stats**
     - Total database records
     - Storage usage
     - Table sizes
     - Index efficiency
   
   - **Demo Data Tools**
     - Seed demo accounts
     - Generate sample data
     - Clear demo data
     - Reset demo accounts

#### Admin-Only API Endpoints
```
GET  /src/admin/dashboard.php      - Admin dashboard view
GET  /src/admin/users.php          - List all users
POST /src/admin/toggle_role.php    - Toggle user role
POST /src/admin/delete_user.php    - Delete user account
GET  /src/admin/analytics.php      - System analytics data
POST /src/admin/seed_admin.php     - Create admin account
POST /src/admin/seed_sample.php    - Generate demo data
```

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

---

## 📁 Project Structure

```
Virtual_Wardrobe/
│
├── 📂 public/                       # Public web root (document root)
│   ├── index.php                   # Landing page with authentication
│   ├── share.php                   # Public outfit sharing page
│   │
│   ├── 📂 css/                     # Stylesheets
│   │   ├── styles.css             # Main application styles
│   │   ├── auth.css               # Login/register page styles
│   │   └── landing.css            # Landing page styles
│   │
│   ├── 📂 js/                      # Client-side JavaScript
│   │   └── main.js                # Core JavaScript functionality
│   │
│   ├── 📂 uploads/                 # User uploaded images (write permissions required)
│   │   └── [user_id]_[timestamp]_[filename].jpg
│   │
│   ├── 📂 images/                  # Static site images
│   │   └── logo, icons, etc.
│   │
│   └── 📂 errors/                  # Error pages
│       ├── 403.php                # Forbidden
│       ├── 404.php                # Not Found
│       └── 500.php                # Internal Server Error
│
├── 📂 src/                          # Application source code (protected)
│   ├── config.php                  # Configuration & database connection
│   ├── dashboard.php               # Main user dashboard
│   ├── ErrorHandler.php            # Error handling class
│   ├── Security.php                # Security utilities class
│   │
│   ├── 📂 auth/                    # Authentication system
│   │   ├── login.php              # Login handler
│   │   ├── register.php           # Registration handler
│   │   ├── logout.php             # Logout handler
│   │   ├── forgot.php             # Password reset request
│   │   └── reset.php              # Password reset handler
│   │
│   ├── 📂 clothes/                 # Wardrobe management
│   │   ├── list.php               # View all clothing items
│   │   ├── upload.php             # Upload new item
│   │   ├── delete.php             # Delete item
│   │   ├── toggle.php             # Toggle favorite/laundry status
│   │   └── clear_laundry.php      # Clear all laundry items
│   │
│   ├── 📂 outfits/                 # Outfit management
│   │   ├── list.php               # View all outfits
│   │   ├── create.php             # Create new outfit
│   │   ├── delete.php             # Delete outfit
│   │   ├── toggle.php             # Toggle favorite status
│   │   ├── wear.php               # Log outfit wear
│   │   ├── share.php              # Generate share link
│   │   └── unshare.php            # Revoke share link
│   │
│   ├── 📂 planner/                 # Outfit planning system
│   │   ├── calendar.php           # Calendar view with drag-and-drop
│   │   ├── list.php               # List view of plans
│   │   ├── events.php             # Get planned events (JSON API)
│   │   ├── plan.php               # Create new plan
│   │   ├── update.php             # Update plan notes/season
│   │   ├── move.php               # Move plan to different date
│   │   ├── delete.php             # Delete plan
│   │   └── stream.php             # SSE stream for real-time updates
│   │
│   ├── 📂 collections/             # Collections feature
│   │   ├── list.php               # View collections
│   │   ├── create.php             # Create new collection
│   │   ├── delete.php             # Delete collection
│   │   ├── add_item.php           # Add item to collection
│   │   └── remove_item.php        # Remove item from collection
│   │
│   ├── 📂 stats/                   # Statistics & analytics
│   │   └── list.php               # User statistics dashboard
│   │
│   ├── 📂 admin/                   # Admin panel (admin role only)
│   │   ├── dashboard.php          # Admin dashboard
│   │   ├── users.php              # User management
│   │   ├── analytics.php          # System analytics
│   │   ├── toggle_role.php        # Change user role
│   │   ├── delete_user.php        # Delete user account
│   │   ├── seed_admin.php         # Create admin account
│   │   ├── seed_sample.php        # Generate demo data
│   │   └── check_demo.php         # Check if demo mode
│   │
│   └── 📂 templates/               # Reusable template components
│       ├── header.php             # Header with navigation
│       └── footer.php             # Footer with scripts
│
├── 📂 sql/                          # Database scripts
│   ├── db_init.sql                # Main database schema (11 tables)
│   └── migrate.sql                # Migration scripts
│
├── 📂 database/                     # Additional database files
│   ├── schema.sql                 # Schema documentation
│   ├── migrations.sql             # Database migrations
│   └── migrations_*.sql           # Version-specific migrations
│
├── 📂 assets/                       # Static assets
│   ├── 📂 css/                    # Additional stylesheets
│   │   └── style.css
│   └── 📂 js/                     # Additional JavaScript
│       ├── wardrobe.js
│       └── outfits.js
│
├── 📂 node/socket-server/           # Real-time WebSocket server
│   ├── server.js                  # Socket.IO server implementation
│   ├── package.json               # Node.js dependencies
│   ├── playwright.config.js       # E2E test configuration
│   ├── generate_secret.js         # Generate API secrets
│   ├── test_emit.js               # Test emit functionality
│   ├── push_secret.sh             # Push secrets to GitHub
│   │
│   ├── 📂 tests/                  # Playwright E2E tests
│   │   ├── dashboard.spec.js     # Dashboard tests
│   │   ├── emit.spec.js          # Emit endpoint tests
│   │   ├── hmac.spec.js          # HMAC validation tests
│   │   ├── multi-user.spec.js    # Multi-user tests
│   │   └── helpers.js            # Test utilities
│   │
│   └── 📂 logs/                   # Server logs
│
├── 📂 docs/                         # Documentation
│   ├── ERD_DIAGRAM.md             # Entity Relationship Diagram (detailed)
│   ├── ERD_DIAGRAM.puml           # PlantUML source for ERD
│   ├── FRAMING_TEMPLATE.md        # Project framing document
│   ├── SUBMISSION_CHECKLIST.md    # Submission requirements checklist
│   ├── PDF_CONVERSION_GUIDE.md    # Guide to create PDF documentation
│   └── PLANTUML_GUIDE.md          # PlantUML usage instructions
│
├── 📂 docker/                       # Docker configuration (optional)
│   └── 📂 php/
│       └── Dockerfile
│
├── 🔧 Configuration Files
│   ├── .env.example               # Environment variables template
│   ├── .gitignore                 # Git ignore patterns
│   ├── docker-compose.yml         # Docker composition
│   ├── docker-compose.override.yml
│   ├── Makefile                   # Make commands
│   ├── start_all.bat              # Windows startup script
│   └── start_all.ps1              # PowerShell startup script
│
├── 📚 Documentation Files
│   ├── README.md                  # This file
│   ├── DEPLOYMENT_GUIDE.md        # Production deployment guide
│   ├── ENHANCEMENT_PLAN.md        # Future enhancements
│   ├── IMPLEMENTATION_SUMMARY.md  # Technical implementation details
│   └── Virtual_Wardrobe_Complete_Guide.md
│
└── 📄 Other Files
    ├── php.pid                    # PHP server process ID
    └── php-process-id             # Process tracking
```

### Key Directory Purposes

**🌐 Public Directory (`public/`)**
- Web server document root
- Contains all publicly accessible files
- Entry point: `index.php`
- Upload storage: `uploads/`

**🔒 Source Directory (`src/`)**
- Protected application logic
- Should NOT be web-accessible (configure .htaccess or server)
- Contains sensitive configuration files
- MVC-inspired structure

**💾 Database Directory (`sql/`)**
- Schema definition files
- Migration scripts
- Database initialization
- All tables use `vw_` prefix

**⚡ Node Server (`node/socket-server/`)**
- Optional real-time features
- Socket.IO WebSocket server
- JWT authentication
- E2E test suite

**📖 Documentation (`docs/`)**
- Project documentation
- ER diagrams and schemas
- Submission materials
- Setup guides

---
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

## � License

Educational project for academic purposes.

---

## 👥 Project Information

**Project**: Virtual Wardrobe & Outfit Planner  
**Year**: 2025  
**Technologies**: PHP, MySQL, JavaScript, Socket.IO, FullCalendar  
**Live URL**: http://169.239.251.102:341/~tomoh.ikfingeh/Virtual_Wardrobe/public/index.php

---

**Last Updated**: December 2025  
**Version**: 2.0  
**Status**: ✅ Production Ready


---

##  Complete Feature List

### User Features (100+ Features)
-  User registration with validation
-  Secure login with bcrypt
-  Password reset via email
-  Session management
-  Upload clothing items
-  Categorize by type
-  Mark favorites
-  Track laundry status
-  Create outfit combinations
-  Calendar-based planning
-  Drag-and-drop scheduling
-  Outfit sharing
-  Statistics dashboard
-  Collections management
-  Real-time updates
-  Responsive design
-  Mobile-friendly interface

### Admin Features
-  User management dashboard
-  Role assignment (user/admin)
-  Delete user accounts
-  System analytics
-  Content moderation
-  Activity monitoring
-  Security auditing
-  Demo data management

---

##  Academic Project Details

**Course**: Database Management Systems  
**Submission**: Final Project  
**Status**: Complete and Production-Ready

**Project Requirements Met**:
-  Modern responsive frontend (HTML5, CSS3, JavaScript)
-  Backend programming (PHP 8.0+)
-  Database design (11 normalized tables)
-  CRUD operations (Create, Read, Update, Delete)
-  Security implementation (OWASP compliant)
-  Clean code practices
-  Live server deployment
-  Documentation (ERD, README, guides)

**Technical Achievements**:
- 11 database tables with proper relationships
- 13 foreign key constraints
- 100+ implemented features
- Real-time WebSocket integration
- RESTful API endpoints
- End-to-end testing suite
- Professional UI/UX design
- Mobile-responsive layout

---

**Maintained By**: Claude Tomoh  
**GitHub**: https://github.com/claudetomoh/Virtual_Wardrobe  
**Live Demo**: http://169.239.251.102:341/~tomoh.ikfingeh/Virtual_Wardrobe/public/index.php

*Built with  for fashion enthusiasts and organized wardrobes*
