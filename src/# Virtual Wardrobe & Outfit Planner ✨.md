# Virtual Wardrobe & Outfit Planner ✨

A sophisticated web application for organizing your wardrobe digitally and creating outfit combinations with live previews and animations.

## 🌟 Features

### Core Features
- **🔐 User Authentication**: Secure registration and login with password hashing
- **📸 Smart Upload**: Upload clothing items with category and color metadata
- **👗 Digital Wardrobe**: Interactive gallery with real-time search and filtering
- **👔 Outfit Builder**: Create outfits with live preview functionality
- **📊 Dashboard**: View statistics and recent additions
- **📱 Responsive Design**: Optimized for desktop and mobile devices

### Advanced Features
- **🎨 Animated UI**: Smooth animations and transitions throughout
- **🔍 Real-time Search**: Instant filtering as you type
- **🖼️ Image Lightbox**: Click images for full-screen view
- **📈 Statistics**: Track wardrobe size and outfit count
- **🎯 Category Breakdown**: Visual representation of your wardrobe
- **⚡ Live Preview**: See outfits before saving

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3 (with animations), Vanilla JavaScript
- **Server**: Apache (XAMPP/WAMP/LAMP)
- **Fonts**: Inter from Google Fonts

## 📦 Installation

### Prerequisites
- XAMPP/WAMP/LAMP installed
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Quick Setup

1. **Install XAMPP**
   - Download from [apachefriends.org](https://www.apachefriends.org)
   - Install and start Apache + MySQL services

2. **Deploy Project**
   ```
   Copy project to: C:\xampp\htdocs\Virtual_Wardrobe
   ```

3. **Create Database**
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Import: `sql/db_init.sql`
   - Database `wardrobe_app` will be created automatically

4. **Configure (Optional)**
   - Edit `src/config.php` if needed
   - Default credentials work with XAMPP:
     ```php
     $DB_HOST = '127.0.0.1';
     $DB_NAME = 'wardrobe_app';
     $DB_USER = 'root';
     $DB_PASS = '';
     ```

5. **Set Permissions**
   - Ensure `public/uploads/` is writable
   - Windows/XAMPP: Usually automatic
   - Linux: `chmod 755 public/uploads/`

6. **Launch Application**
   - Visit: `http://localhost/Virtual_Wardrobe/public/index.php`
   - Register your account and start organizing!

## 📖 Usage Guide

### Getting Started
1. **Register**: Create your account with name, email, and password
2. **Login**: Access your personalized wardrobe
3. **Upload Items**: Add clothing items with photos and details
4. **Browse Wardrobe**: Use search and filters to find items
5. **Create Outfits**: Mix and match items with live preview
6. **View Dashboard**: Track your wardrobe statistics

### Features Walkthrough

#### Wardrobe Management
- Click "Add New Item" to upload clothes
- Categorize by: Tops, Bottoms, Shoes, Accessories
- Add color information for better organization
- Use real-time search to find items instantly
- Filter by category with dropdown menu
- Click images to view in full-screen lightbox
- Delete items you no longer own

#### Outfit Creation
- Select items from different categories
- See live preview as you build
- Add optional title for easy identification
- Save and view all your outfit combinations
- Delete outfits you no longer want

#### Dashboard
- View total items and outfits
- See category breakdown
- Check recently added items
- Monitor your style score

## 🎨 Design Features

- **Gradient Backgrounds**: Animated purple-blue gradients
- **Floating Shapes**: Subtle background animations
- **Smooth Transitions**: Professional hover effects
- **Card-based UI**: Modern, clean interface
- **Responsive Grid**: Adapts to any screen size
- **Interactive Forms**: Real-time validation and feedback
- **Loading States**: Visual feedback during operations

## 📁 Project Structure

```
Virtual_Wardrobe/
├── public/
│   ├── uploads/          # User-uploaded images
│   ├── css/
│   │   └── styles.css    # Enhanced animations & styling
│   ├── js/
│   │   └── main.js       # Interactive features
│   └── index.php         # Landing page
├── src/
│   ├── auth/
│   │   ├── login.php     # User login
│   │   ├── register.php  # User registration
│   │   └── logout.php    # Session termination
│   ├── clothes/
│   │   ├── upload.php    # Upload items
│   │   ├── list.php      # Wardrobe gallery
│   │   └── delete.php    # Remove items
│   ├── outfits/
│   │   ├── create.php    # Outfit builder
│   │   ├── list.php      # Saved outfits
│   │   └── delete.php    # Remove outfits
│   ├── templates/
│   │   ├── header.php    # Common header
│   │   └── footer.php    # Common footer
│   ├── config.php        # Database connection
│   └── dashboard.php     # Statistics & overview
├── sql/
│   └── db_init.sql       # Database schema
└── README.md
```

## 🔒 Security Features

- ✅ Password hashing with `password_hash()`
- ✅ Prepared statements (SQL injection prevention)
- ✅ Session-based authentication
- ✅ File type validation (MIME check)
- ✅ File size limits (5MB max)
- ✅ XSS protection with `htmlspecialchars()`
- ✅ Ownership checks on delete operations
- ✅ Secure file naming (random generation)

## 🐛 Troubleshooting

**Database Connection Error**
- Verify MySQL is running in XAMPP Control Panel
- Check credentials in `src/config.php`
- Ensure database was created from `sql/db_init.sql`

**File Upload Issues**
- Check `public/uploads/` folder exists
- Verify folder is writable
- Confirm `upload_max_filesize` in `php.ini` (default 5MB)

**Images Not Displaying**
- Verify files exist in `public/uploads/`
- Check image paths in database
- Confirm web server has read permissions

**Blank Page/White Screen**
- Enable error reporting in `php.ini`
- Check Apache error logs
- Verify PHP version is 7.4 or higher

**Animations Not Working**
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify `main.js` is loading correctly

## 🚀 Performance Tips

- Regularly clean up unused images
- Optimize uploaded images before upload
- Use modern image formats (WebP)
- Enable browser caching for static assets
- Consider CDN for production deployment

## 🌐 Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## 📈 Future Enhancements

- [ ] Multiple images per clothing item
- [ ] AI-powered outfit suggestions
- [ ] Weather-based recommendations
- [ ] Social sharing features
- [ ] Calendar integration
- [ ] Clothing cost tracking
- [ ] Wear frequency analytics
- [ ] Virtual try-on (AR)
- [ ] Export wardrobe as PDF
- [ ] Collaborative outfit planning

## 📝 Development Notes

- Built with vanilla PHP (no frameworks)
- Uses PDO for database operations
- Modern CSS with animations and gradients
- Responsive grid layouts
- Mobile-first design approach
- Progressive enhancement philosophy

## 🤝 Contributing

This is an educational project. Suggestions for improvements:
1. Fork the repository
2. Create feature branch
3. Commit changes with clear messages
4. Test thoroughly
5. Submit pull request

## 📄 License

Educational project for coursework demonstration.

## 👨‍💻 Support

For questions or issues:
- Check troubleshooting section
- Review code comments
- Consult project documentation
- Test in fresh XAMPP installation

---

**Made with ❤️ for fashion-conscious developers**

Organize smarter, dress better! ✨👗👔
