# Admin Dashboard Quick Start Guide

## Prerequisites

- Node.js 18+ and npm
- Laravel backend server running (http://localhost:8000)
- Admin account credentials

## Getting Started in 3 Steps

### Step 1: Start the Backend

Ensure your Laravel Date API is running:

```bash
cd /home/anonynoman/Desktop/date-api
php artisan serve
```

The API will be available at `http://localhost:8000`

### Step 2: Configure the Admin Dashboard

Navigate to the admin-ui folder and set up environment:

```bash
cd /home/anonynoman/Desktop/date-api/admin-ui

# Check .env.local exists (should be pre-configured)
cat .env.local
```

Expected `.env.local` content:
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
NEXT_PUBLIC_API_VERSION=v1
```

If `.env.local` doesn't exist, create it with the above content.

### Step 3: Start the Admin Dashboard

```bash
npm run dev
```

The dashboard will be available at **http://localhost:3000**

## First Login

1. Navigate to http://localhost:3000
2. Enter admin email and password
3. Click "Sign in"
4. You'll be redirected to the dashboard

## Dashboard Navigation

Once logged in, use the sidebar to navigate:

| Section | Purpose |
|---------|---------|
| 📊 Dashboard | View platform statistics |
| 👥 Users | Manage users (ban, unban, delete) |
| 📝 Posts | Moderate posts (flag, unflag, delete) |
| 🚩 Flagged Content | Review and moderate flagged posts |
| 📋 Activity Log | View admin action history |
| ⚙️ Settings | Configure platform settings |

## Common Tasks

### Ban a User

1. Go to Users section
2. Find the user in the list
3. Click "Ban" button
4. Enter ban reason in prompt
5. User status changes to "Banned"

### Flag a Post

1. Go to Posts section
2. Find the post you want to flag
3. Click "Flag" button
4. Enter flag reason in prompt
5. Post appears in Flagged Content section

### View Activity Log

1. Go to Activity Log section
2. See all admin actions in chronological order
3. Color-coded by action type:
   - Green: Create
   - Blue: Update
   - Red: Delete/Ban
   - Purple: Flag

### Update Platform Settings

1. Go to Settings section
2. Modify desired settings:
   - Site name
   - Site URL
   - Max upload size
   - Maintenance mode
   - Email notifications
3. Click "Save Settings"

## Troubleshooting

### "Failed to Login"

**Issue**: Cannot log in with admin credentials

**Solution**:
1. Verify backend server is running: `php artisan serve`
2. Check `.env.local` has correct API_BASE_URL
3. Verify admin credentials exist in database
4. Check Laravel logs: `tail -f storage/logs/laravel.log`

### "Failed to load statistics"

**Issue**: Dashboard shows error when loading

**Solution**:
1. Check CORS settings on backend
2. Verify admin token is valid
3. Check backend API endpoint: GET `/admin/statistics`
4. See backend logs for error details

### "Network Error"

**Issue**: Cannot connect to API

**Solution**:
1. Ensure backend is running: `cd /home/anonynoman/Desktop/date-api && php artisan serve`
2. Check firewall isn't blocking port 8000
3. Verify `.env.local` has correct base URL
4. Test API manually: `curl http://localhost:8000/api/v1/admin/statistics`

### Blank Page or No Navigation

**Issue**: Dashboard loads but shows nothing

**Solution**:
1. Check browser console (F12) for errors
2. Hard refresh page (Ctrl+Shift+R)
3. Clear browser cache
4. Verify token is stored: Open DevTools → Application → Local Storage

## Development Commands

```bash
cd admin-ui

# Start development server
npm run dev

# Build for production
npm run build

# Run production server
npm start

# Run linter (if configured)
npm run lint

# Check for type errors
npm run type-check
```

## API Endpoints Used

The admin dashboard consumes these endpoints:

```
POST   /admin/login
GET    /admin/dashboard
GET    /admin/statistics
GET    /admin/users
GET    /admin/users/{id}
POST   /admin/users/{id}/ban
POST   /admin/users/{id}/unban
DELETE /admin/users/{id}
PUT    /admin/users/{id}
GET    /admin/posts
GET    /admin/posts/{id}
DELETE /admin/posts/{id}
POST   /admin/posts/{id}/flag
POST   /admin/posts/{id}/unflag
GET    /admin/flagged-content
GET    /admin/activity-log
GET    /admin/settings
PUT    /admin/settings
```

## Security Notes

- ✅ All API requests include Bearer token authentication
- ✅ Tokens are stored securely in localStorage
- ✅ Logout clears token automatically
- ✅ 401 responses redirect to login
- ✅ Forms validate input before submission
- ✅ Sensitive actions require confirmation

## Performance Tips

- Dashboard loads statistics on mount (auto-refresh every 30s optional)
- User/post lists paginate (10 items per page)
- Components use React hooks for efficient rendering
- Minimize browser extensions that might interfere with CORS

## Browser DevTools

Access admin-specific info:

```javascript
// Check authentication
localStorage.getItem('adminToken')

// Clear authentication (useful for testing)
localStorage.removeItem('adminToken')
location.reload()

// Monitor API calls
// Open DevTools → Network tab → filter by "admin"
```

## Next Steps

1. **Explore Dashboard**: Check statistics and overview
2. **Manage Users**: Practice banning/unbanning users
3. **Review Posts**: Check and moderate user-generated posts
4. **Check Activity**: See log of all admin actions
5. **Configure Settings**: Update platform configuration

## Additional Resources

- **Full Documentation**: See [ADMIN_DASHBOARD.md](ADMIN_DASHBOARD.md)
- **API Documentation**: See [API_ENDPOINTS.md](api-endpoints.txt)
- **Backend Code**: See [Laravel Code](/app)
- **Admin API Service**: See [lib/api.ts](admin-ui/lib/api.ts)

## Support

If you encounter issues:

1. Check the browser console (F12) for error messages
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify both servers are running
4. Check network connectivity
5. Try clearing cache and refreshing

---

**Ready to go!** 🚀 Visit http://localhost:3000 to access your admin dashboard.
