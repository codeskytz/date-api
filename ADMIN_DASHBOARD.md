# Admin Dashboard Documentation

## Overview

A full-featured admin dashboard built with **Next.js 16.1.1** and **React 19.2.3** for managing the Date API platform. The dashboard provides a complete UI for all 16 admin endpoints with authentication, user management, post moderation, and activity tracking.

## Features

- ✅ **Authentication**: Email/password login with token persistence
- ✅ **Dashboard**: Real-time statistics and platform overview
- ✅ **User Management**: List, ban, unban, delete users
- ✅ **Post Management**: View, flag, unflag, and delete posts
- ✅ **Flagged Content**: Review and moderate flagged posts
- ✅ **Activity Log**: Track all admin actions with detailed timeline
- ✅ **Admin Settings**: Configure platform settings
- ✅ **Responsive Design**: Works on desktop, tablet, and mobile

## Project Structure

```
admin-ui/
├── app/
│   ├── layout.tsx          # Root layout with AuthProvider
│   ├── page.tsx            # Home page (auth router)
│   └── globals.css         # Global styles
├── components/
│   ├── LoginPage.tsx       # Login form
│   ├── AdminDashboard.tsx  # Main dashboard container
│   ├── Dashboard.tsx       # Statistics overview
│   ├── UserManagement.tsx  # User CRUD operations
│   ├── PostManagement.tsx  # Post management interface
│   ├── FlaggedContent.tsx  # Flagged posts review
│   ├── ActivityLog.tsx     # Admin action history
│   ├── Settings.tsx        # Platform settings
│   ├── Sidebar.tsx         # Left navigation
│   ├── Navbar.tsx          # Top navigation bar
│   └── LoadingSpinner.tsx  # Loading indicator
├── lib/
│   ├── api.ts              # API service layer (all endpoints)
│   ├── auth-context.tsx    # Authentication context
├── .env.local              # Environment configuration
├── package.json            # Dependencies
└── tailwind.config.ts      # Tailwind CSS config
```

## Installation & Setup

### 1. Install Dependencies

```bash
cd admin-ui
npm install
```

### 2. Configure Environment Variables

Create `.env.local` file:

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
NEXT_PUBLIC_API_VERSION=v1
```

### 3. Run Development Server

```bash
npm run dev
```

The dashboard will be available at `http://localhost:3000`

## API Integration

### API Service Layer (`lib/api.ts`)

All 16 admin endpoints are wrapped in TypeScript functions for type-safe API communication.

#### Authentication
- `adminLogin(email: string, password: string)` - Admin login
- `setAdminToken(token: string)` - Store token in localStorage
- `getAdminToken()` - Retrieve stored token
- `clearAdminToken()` - Remove token

#### Dashboard
- `getAdminDashboard()` - Get dashboard overview
- `getAdminStatistics()` - Get platform statistics

#### Users
- `listUsers(page: number, perPage: number)` - List all users with pagination
- `getUser(id: string)` - Get user details
- `updateUser(id: string, data: object)` - Update user information
- `banUser(id: string, reason: string)` - Ban a user
- `unbanUser(id: string)` - Unban a user
- `deleteUser(id: string)` - Delete a user permanently

#### Posts
- `listPosts(page: number, perPage: number)` - List all posts with pagination
- `getPost(id: string)` - Get post details
- `deletePost(id: string)` - Delete a post
- `flagPost(id: string, reason: string)` - Flag a post for review
- `unflagPost(id: string)` - Remove flag from a post

#### Content Management
- `listFlaggedContent(page: number, perPage: number)` - List flagged content
- `getActivityLog(page: number, perPage: number)` - Get admin activity log
- `getAdminSettings()` - Get platform settings
- `updateAdminSettings(data: object)` - Update platform settings

### Authentication Context (`lib/auth-context.tsx`)

Provides global authentication state management using React Context API.

```tsx
import { useAuth } from '@/lib/auth-context';

function MyComponent() {
  const { isAuthenticated, token, setToken, logout } = useAuth();
  
  if (!isAuthenticated) {
    return <LoginPage />;
  }
  
  return <Dashboard />;
}
```

## Component Documentation

### LoginPage

Email/password login form with error handling.

**Features:**
- Email and password inputs
- Form validation
- Error message display
- Loading state
- Auto-redirect on successful login

**Props:** None (uses `useAuth` hook)

```tsx
import LoginPage from '@/components/LoginPage';

export default function Home() {
  const { isAuthenticated } = useAuth();
  if (!isAuthenticated) return <LoginPage />;
}
```

### AdminDashboard

Main container component that manages tab navigation and renders appropriate content.

**Features:**
- Sidebar navigation
- Top navbar
- Dynamic content rendering
- Page title updates

**Props:** None

```tsx
import AdminDashboard from '@/components/AdminDashboard';

export default function App() {
  return <AdminDashboard />;
}
```

### Dashboard

Statistics overview page showing platform metrics.

**Features:**
- Total users count
- Total posts count
- Flagged content count
- Recent activity count
- Color-coded stat cards

**API Calls:**
- `getAdminStatistics()` on component mount

```tsx
import Dashboard from '@/components/Dashboard';

export default function Home() {
  return <Dashboard />;
}
```

### UserManagement

Complete user management interface.

**Features:**
- Paginated user list
- User status (Active/Banned)
- Ban/unban users with reason
- Delete users with confirmation
- Pagination controls
- Real-time state updates

**API Calls:**
- `listUsers(page, perPage)`
- `banUser(userId, reason)`
- `unbanUser(userId)`
- `deleteUser(userId)`

```tsx
import UserManagement from '@/components/UserManagement';

export default function Home() {
  return <UserManagement />;
}
```

### PostManagement

Post moderation interface.

**Features:**
- Paginated post list
- View post title and content
- Flag posts for review
- Unflag posts
- Delete posts with confirmation
- Real-time updates

**API Calls:**
- `listPosts(page, perPage)`
- `flagPost(postId, reason)`
- `unflagPost(postId)`
- `deletePost(postId)`

```tsx
import PostManagement from '@/components/PostManagement';

export default function Home() {
  return <PostManagement />;
}
```

### FlaggedContent

Flagged content review interface.

**Features:**
- View all flagged posts
- See flag reason
- See when content was flagged
- Unflag content
- Delete flagged content
- Pagination

**API Calls:**
- `listFlaggedContent(page, perPage)`
- `unflagPost(itemId)`
- `deletePost(itemId)`

```tsx
import FlaggedContent from '@/components/FlaggedContent';

export default function Home() {
  return <FlaggedContent />;
}
```

### ActivityLog

Admin action history and timeline.

**Features:**
- Chronological activity listing
- Color-coded action badges (Create, Update, Delete, Ban, Flag)
- Admin user information
- Action details
- Timestamp for each action
- Pagination

**API Calls:**
- `getActivityLog(page, perPage)`

```tsx
import ActivityLog from '@/components/ActivityLog';

export default function Home() {
  return <ActivityLog />;
}
```

### Settings

Platform settings management.

**Features:**
- Edit site name
- Configure site URL
- Set max upload size
- Enable/disable maintenance mode
- Toggle email notifications
- Success/error messages
- Loading state during save

**API Calls:**
- `getAdminSettings()` on mount
- `updateAdminSettings(data)` on form submit

```tsx
import Settings from '@/components/Settings';

export default function Home() {
  return <Settings />;
}
```

### Sidebar

Left navigation menu with tab switching.

**Props:**
```tsx
interface SidebarProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}
```

**Features:**
- Dashboard (📊)
- Users (👥)
- Posts (📝)
- Flagged Content (🚩)
- Activity Log (📋)
- Settings (⚙️)

```tsx
import Sidebar from '@/components/Sidebar';

export default function Home() {
  const [activeTab, setActiveTab] = useState('dashboard');
  return <Sidebar activeTab={activeTab} onTabChange={setActiveTab} />;
}
```

### Navbar

Top navigation bar with page title and logout button.

**Props:**
```tsx
interface NavbarProps {
  title: string;
}
```

**Features:**
- Display current page title
- Logout button with redirect
- Clean, minimal design

```tsx
import Navbar from '@/components/Navbar';

export default function Home() {
  return <Navbar title="Dashboard" />;
}
```

### LoadingSpinner

Reusable loading indicator component.

**Features:**
- Centered spinner
- Smooth animations
- Standard sizing

```tsx
import LoadingSpinner from '@/components/LoadingSpinner';

export default function Home() {
  const [loading, setLoading] = useState(true);
  
  if (loading) return <LoadingSpinner />;
  return <div>Content loaded</div>;
}
```

## Authentication Flow

1. **Initial Load**
   - App checks localStorage for existing token
   - If token exists, set `isAuthenticated = true`
   - Otherwise, redirect to login page

2. **Login**
   - User enters email and password
   - Call `adminLogin(email, password)`
   - API returns token
   - Token stored in localStorage via `setAdminToken()`
   - `isAuthenticated` set to true
   - Auto-redirect to dashboard

3. **API Requests**
   - All API calls automatically include `Authorization: Bearer {token}` header
   - Token added by `getHeaders()` function in `lib/api.ts`

4. **Logout**
   - User clicks logout button
   - Call `logout()` from `useAuth` hook
   - Token cleared from localStorage via `clearAdminToken()`
   - `isAuthenticated` set to false
   - Redirect to login page

## Styling

The dashboard uses **Tailwind CSS 4** for styling. Key design decisions:

- **Color Scheme**: Blue primary (#2563eb), gray backgrounds
- **Spacing**: 4px, 8px, 16px, 32px increments
- **Typography**: Inter/system fonts with 3-tier hierarchy (h1, h2, body)
- **Components**: Rounded corners (lg), subtle shadows, smooth transitions
- **States**: Hover effects, disabled states, loading states

### Theme Colors

| Purpose | Color | Class |
|---------|-------|-------|
| Primary | Blue-600 | `bg-blue-600` |
| Success | Green | `bg-green-100/700` |
| Warning | Yellow | `bg-yellow-100/700` |
| Danger | Red | `bg-red-100/700` |
| Info | Purple | `bg-purple-100/700` |
| Background | Gray-50 | `bg-gray-50` |

## Error Handling

Each component implements error handling:

```tsx
try {
  const data = await apiFunction();
  setData(data);
} catch (err) {
  setError(err instanceof Error ? err.message : 'Failed');
}
```

Display errors to users:

```tsx
{error && (
  <div className="rounded-md bg-red-50 p-4 text-red-700">
    {error}
  </div>
)}
```

## Loading States

- **Page Load**: Full page `<LoadingSpinner />`
- **Action Load**: Button disabled with "...ing" text
- **Form Submit**: Button disabled, text changes to "Saving..."

## Pagination

All list components support pagination:

```tsx
const [page, setPage] = useState(1);

useEffect(() => {
  const data = await listUsers(page, 10);
  setUsers(data.data);
}, [page]);

// Navigation buttons
<button onClick={() => setPage(p => p - 1)}>Previous</button>
<span>Page {page}</span>
<button onClick={() => setPage(p => p + 1)}>Next</button>
```

## Browser Compatibility

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- Mobile browsers: ✅ Responsive design

## Performance Optimization

- **Client-side Rendering**: Using `'use client'` for interactive components
- **State Management**: React hooks (useState, useEffect, useContext)
- **Memoization**: Ready for React.memo when needed
- **Data Caching**: Can be added with React Query/SWR if needed

## Future Enhancements

- [ ] Add React Query for data caching and refetching
- [ ] Implement toast notifications for success/error messages
- [ ] Add search and filter capabilities to lists
- [ ] Create reusable Modal component for confirmations
- [ ] Add CSV export for user/post data
- [ ] Implement real-time updates with WebSockets
- [ ] Add user profile page with detailed information
- [ ] Add batch operations (select multiple, bulk delete)
- [ ] Implement dark mode toggle
- [ ] Add data visualization charts

## Troubleshooting

### Login Issues
- Verify API base URL in `.env.local`
- Check if Laravel backend is running
- Verify admin credentials are correct

### API Connection Failed
- Ensure backend server is running on configured port
- Check CORS settings on backend
- Verify network connectivity

### Components Not Rendering
- Check browser console for errors
- Verify all imports are correct
- Check if data is properly fetched

### Styling Issues
- Rebuild Tailwind CSS: `npm run build`
- Clear browser cache
- Ensure `globals.css` is imported in layout.tsx

## Support

For issues or questions, refer to:
- Next.js Documentation: https://nextjs.org/docs
- React Documentation: https://react.dev
- Tailwind CSS: https://tailwindcss.com
- Backend API: See Date API documentation
