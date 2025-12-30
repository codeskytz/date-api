# Admin Dashboard - Next.js Admin Panel

A production-ready admin dashboard for the Date API platform built with **Next.js 16.1.1**, **React 19.2.3**, and **Tailwind CSS 4**.

## 🚀 Quick Start

```bash
# Install dependencies
npm install

# Verify .env.local exists with:
# NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
# NEXT_PUBLIC_API_VERSION=v1

# Start development server
npm run dev
```

Visit **http://localhost:3000** and log in with your admin credentials.

## 📋 Features

- ✅ **Authentication** - Email/password login with token persistence
- ✅ **Dashboard** - Real-time statistics and platform overview
- ✅ **User Management** - List, ban, unban, delete users with pagination
- ✅ **Post Management** - View, flag, unflag, and delete posts
- ✅ **Flagged Content** - Review and moderate flagged posts
- ✅ **Activity Log** - Track all admin actions with timestamps
- ✅ **Settings** - Configure platform settings
- ✅ **Responsive** - Works on desktop, tablet, and mobile

## 🔧 Commands

```bash
npm run dev      # Start dev server
npm run build    # Build for production
npm start        # Run production server
npm run lint     # Run linter (if configured)
```

## 📁 Project Structure

```
components/     # UI components (LoginPage, Dashboard, etc.)
lib/            # API service and auth context
app/            # Next.js app directory
.env.local      # Environment configuration
```

## 🔐 Authentication

- Email/password login
- Token persistence via localStorage
- Auto-redirect based on auth status
- Logout clears token and redirects to login

## 📊 Dashboard Pages

- **Dashboard** - Statistics overview
- **Users** - User management (ban/unban/delete)
- **Posts** - Post moderation (flag/unflag/delete)
- **Flagged Content** - Review flagged posts
- **Activity Log** - Admin action timeline
- **Settings** - Platform configuration

## 🔌 API Integration

16 admin endpoints implemented:
- Authentication (1)
- Dashboard (2)
- Users (6)
- Posts (5)
- Content (2)

## 📚 Documentation

Full documentation available:
- [ADMIN_DASHBOARD.md](../ADMIN_DASHBOARD.md) - Complete guide
- [ADMIN_QUICK_START.md](../ADMIN_QUICK_START.md) - Quick setup
- [COMPONENT_API_REFERENCE.md](../COMPONENT_API_REFERENCE.md) - API reference
- [IMPLEMENTATION_SUMMARY.md](../IMPLEMENTATION_SUMMARY.md) - Summary

## ⚙️ Environment

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
NEXT_PUBLIC_API_VERSION=v1
```

## 🎨 Tech Stack

- **Next.js 16.1.1** - React framework
- **React 19.2.3** - UI library
- **Tailwind CSS 4** - Styling
- **TypeScript** - Type safety

## 📱 Browser Support

- Chrome/Edge ✅
- Firefox ✅
- Safari ✅
- Mobile browsers ✅

## 🚀 Status

✅ **Production Ready** - All 16 admin endpoints implemented with full documentation.

---

For full documentation, see the guides linked above.
