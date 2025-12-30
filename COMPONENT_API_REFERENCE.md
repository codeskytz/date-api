# Admin Dashboard Component API Reference

## Core Components

### LoginPage
**File**: `components/LoginPage.tsx`

Login interface for admin authentication.

**Props**: None (uses `useAuth` hook internally)

**Exports**:
```tsx
export default function LoginPage(): JSX.Element
```

**Usage**:
```tsx
import LoginPage from '@/components/LoginPage';

function MyApp() {
  const { isAuthenticated } = useAuth();
  if (!isAuthenticated) return <LoginPage />;
}
```

**Features**:
- Email/password form fields
- Form submission handling
- Error message display
- Loading state during submission
- Automatic redirect on success

**Dependencies**:
- `@/lib/auth-context` (useAuth)
- `@/lib/api` (adminLogin)

---

### AdminDashboard
**File**: `components/AdminDashboard.tsx`

Main container managing dashboard layout and navigation.

**Props**: None

**Exports**:
```tsx
export default function AdminDashboard(): JSX.Element
```

**Usage**:
```tsx
import AdminDashboard from '@/components/AdminDashboard';

export default function Home() {
  return <AdminDashboard />;
}
```

**Internal State**:
```tsx
const [activeTab, setActiveTab] = useState('dashboard');
```

**Renders**:
- Navbar (with page title)
- Sidebar (with navigation)
- Dynamic content based on `activeTab`

**Available Tabs**:
- `dashboard` → Dashboard component
- `users` → UserManagement component
- `posts` → PostManagement component
- `flagged` → FlaggedContent component
- `activity` → ActivityLog component
- `settings` → Settings component

---

### Dashboard
**File**: `components/Dashboard.tsx`

Statistics overview page.

**Props**: None

**Exports**:
```tsx
export default function Dashboard(): JSX.Element
```

**State**:
```tsx
const [stats, setStats] = useState<Statistics | null>(null);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
```

**API Calls**:
- `getAdminStatistics()` - Fetch on mount

**Displays**:
- Total Users (blue card)
- Total Posts (green card)
- Flagged Content (red card)
- Recent Activity (purple card)

**Types**:
```tsx
interface Statistics {
  total_users?: number;
  total_posts?: number;
  total_flagged_content?: number;
  recent_activity?: number;
}
```

---

### UserManagement
**File**: `components/UserManagement.tsx`

User management interface.

**Props**: None

**Exports**:
```tsx
export default function UserManagement(): JSX.Element
```

**State**:
```tsx
const [users, setUsers] = useState<User[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
const [page, setPage] = useState(1);
const [actionLoading, setActionLoading] = useState<string | null>(null);
```

**API Calls**:
- `listUsers(page, 10)` - Fetch users
- `banUser(userId, reason)` - Ban a user
- `unbanUser(userId)` - Unban a user
- `deleteUser(userId)` - Delete a user

**Types**:
```tsx
interface User {
  id: string;
  name?: string;
  email: string;
  is_banned?: boolean;
  created_at?: string;
}
```

**Features**:
- Paginated user list
- Status badge (Active/Banned)
- Ban/unban with reason prompt
- Delete with confirmation
- Real-time state updates

---

### PostManagement
**File**: `components/PostManagement.tsx`

Post moderation interface.

**Props**: None

**Exports**:
```tsx
export default function PostManagement(): JSX.Element
```

**State**:
```tsx
const [posts, setPosts] = useState<Post[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
const [page, setPage] = useState(1);
const [actionLoading, setActionLoading] = useState<string | null>(null);
```

**API Calls**:
- `listPosts(page, 10)` - Fetch posts
- `deletePost(postId)` - Delete post
- `flagPost(postId, reason)` - Flag for review
- `unflagPost(postId)` - Remove flag

**Types**:
```tsx
interface Post {
  id: string;
  title?: string;
  content?: string;
  user_id?: string;
  is_flagged?: boolean;
  created_at?: string;
}
```

**Features**:
- Card-based post layout
- Flag/unflag with reason
- Delete with confirmation
- Date display with created_at
- Pagination controls

---

### FlaggedContent
**File**: `components/FlaggedContent.tsx`

Flagged content review interface.

**Props**: None

**Exports**:
```tsx
export default function FlaggedContent(): JSX.Element
```

**State**:
```tsx
const [items, setItems] = useState<FlaggedItem[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
const [page, setPage] = useState(1);
const [actionLoading, setActionLoading] = useState<string | null>(null);
```

**API Calls**:
- `listFlaggedContent(page, 10)` - Fetch flagged items
- `unflagPost(itemId)` - Unflag content
- `deletePost(itemId)` - Delete content

**Types**:
```tsx
interface FlaggedItem {
  id: string;
  post_id?: string;
  title?: string;
  content?: string;
  reason?: string;
  flagged_at?: string;
}
```

**Features**:
- Red-highlighted cards for flagged items
- Display flag reason
- Show flagged timestamp
- Unflag and delete actions
- Pagination

---

### ActivityLog
**File**: `components/ActivityLog.tsx`

Admin action history and timeline.

**Props**: None

**Exports**:
```tsx
export default function ActivityLog(): JSX.Element
```

**State**:
```tsx
const [activities, setActivities] = useState<ActivityEntry[]>([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
const [page, setPage] = useState(1);
```

**API Calls**:
- `getActivityLog(page, 20)` - Fetch activity log

**Types**:
```tsx
interface ActivityEntry {
  id: string;
  action: string;
  admin_id?: string;
  target_type?: string;
  target_id?: string;
  details?: string;
  created_at?: string;
}
```

**Action Badges**:
- CREATE → Green
- UPDATE → Blue
- DELETE → Red
- BAN → Yellow
- FLAG → Purple

**Features**:
- Chronological timeline
- Color-coded action types
- Admin user information
- Timestamp display
- Action details
- Pagination

---

### Settings
**File**: `components/Settings.tsx`

Platform settings management.

**Props**: None

**Exports**:
```tsx
export default function Settings(): JSX.Element
```

**State**:
```tsx
const [settings, setSettings] = useState<Settings | null>(null);
const [loading, setLoading] = useState(true);
const [saving, setSaving] = useState(false);
const [error, setError] = useState('');
const [success, setSuccess] = useState('');
```

**API Calls**:
- `getAdminSettings()` - Fetch settings
- `updateAdminSettings(settings)` - Save settings

**Types**:
```tsx
interface Settings {
  site_name?: string;
  site_url?: string;
  max_upload_size?: number;
  maintenance_mode?: boolean;
  email_notifications?: boolean;
  [key: string]: any;
}
```

**Form Fields**:
- Site Name (text input)
- Site URL (URL input)
- Max Upload Size (number input, MB)
- Maintenance Mode (checkbox)
- Email Notifications (checkbox)

**Features**:
- Load settings on mount
- Update with form changes
- Save with loading state
- Success/error feedback
- Instant field updates

---

## UI Components

### Sidebar
**File**: `components/Sidebar.tsx`

Left navigation menu.

**Props**:
```tsx
interface SidebarProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}
```

**Exports**:
```tsx
export default function Sidebar(props: SidebarProps): JSX.Element
```

**Usage**:
```tsx
import Sidebar from '@/components/Sidebar';

function MyDashboard() {
  const [activeTab, setActiveTab] = useState('dashboard');
  return <Sidebar activeTab={activeTab} onTabChange={setActiveTab} />;
}
```

**Navigation Items**:
- Dashboard (📊)
- Users (👥)
- Posts (📝)
- Flagged Content (🚩)
- Activity Log (📋)
- Settings (⚙️)

**Features**:
- Active tab highlighting
- Icon indicators
- Hover effects
- Easy navigation

---

### Navbar
**File**: `components/Navbar.tsx`

Top navigation bar.

**Props**:
```tsx
interface NavbarProps {
  title: string;
}
```

**Exports**:
```tsx
export default function Navbar(props: NavbarProps): JSX.Element
```

**Usage**:
```tsx
import Navbar from '@/components/Navbar';

function MyApp() {
  return <Navbar title="Dashboard" />;
}
```

**Features**:
- Display page title
- Logout button
- Clean, minimal design

---

### LoadingSpinner
**File**: `components/LoadingSpinner.tsx`

Reusable loading indicator.

**Props**: None

**Exports**:
```tsx
export default function LoadingSpinner(): JSX.Element
```

**Usage**:
```tsx
import LoadingSpinner from '@/components/LoadingSpinner';

function MyComponent() {
  if (loading) return <LoadingSpinner />;
  return <div>Content</div>;
}
```

**Features**:
- Centered spinner
- Smooth animations
- Consistent sizing
- 12px diameter with 4px border

---

## Service Layer

### API Service (`lib/api.ts`)

**Authentication Functions**:
```tsx
// Login
function adminLogin(email: string, password: string): Promise<{ token: string }>

// Token management
function setAdminToken(token: string): void
function getAdminToken(): string | null
function clearAdminToken(): void
function getHeaders(): HeadersInit
```

**Admin Endpoints**:
```tsx
// Dashboard
function getAdminDashboard(): Promise<any>
function getAdminStatistics(): Promise<Statistics>

// Users
function listUsers(page: number, perPage: number): Promise<{ data: User[] }>
function getUser(id: string): Promise<User>
function updateUser(id: string, data: object): Promise<User>
function banUser(id: string, reason: string): Promise<void>
function unbanUser(id: string): Promise<void>
function deleteUser(id: string): Promise<void>

// Posts
function listPosts(page: number, perPage: number): Promise<{ data: Post[] }>
function getPost(id: string): Promise<Post>
function deletePost(id: string): Promise<void>
function flagPost(id: string, reason: string): Promise<void>
function unflagPost(id: string): Promise<void>

// Content Management
function listFlaggedContent(page: number, perPage: number): Promise<{ data: any[] }>
function getActivityLog(page: number, perPage: number): Promise<{ data: ActivityEntry[] }>
function getAdminSettings(): Promise<Settings>
function updateAdminSettings(data: object): Promise<void>
```

---

### Authentication Context (`lib/auth-context.tsx`)

**Context Type**:
```tsx
interface AuthContextType {
  isAuthenticated: boolean;
  token: string | null;
  setToken: (token: string) => void;
  logout: () => void;
}
```

**Exports**:
```tsx
// Provider component
export const AuthProvider: React.FC<{ children: React.ReactNode }>

// Hook for consuming context
export const useAuth: () => AuthContextType
```

**Usage**:
```tsx
import { AuthProvider, useAuth } from '@/lib/auth-context';

// In app/layout.tsx
<AuthProvider>
  {children}
</AuthProvider>

// In components
function MyComponent() {
  const { isAuthenticated, setToken, logout } = useAuth();
}
```

**Features**:
- Global auth state
- Token persistence (localStorage)
- Auto-load on mount
- Loading state during init

---

## Type Definitions

### User
```tsx
interface User {
  id: string;
  name?: string;
  email: string;
  is_banned?: boolean;
  created_at?: string;
}
```

### Post
```tsx
interface Post {
  id: string;
  title?: string;
  content?: string;
  user_id?: string;
  is_flagged?: boolean;
  created_at?: string;
}
```

### Statistics
```tsx
interface Statistics {
  total_users?: number;
  total_posts?: number;
  total_flagged_content?: number;
  recent_activity?: number;
}
```

### Settings
```tsx
interface Settings {
  site_name?: string;
  site_url?: string;
  max_upload_size?: number;
  maintenance_mode?: boolean;
  email_notifications?: boolean;
  [key: string]: any;
}
```

---

## Hooks Usage

### useAuth
```tsx
import { useAuth } from '@/lib/auth-context';

function MyComponent() {
  const { isAuthenticated, token, setToken, logout } = useAuth();
  
  if (!isAuthenticated) {
    return <p>Not authenticated</p>;
  }
  
  return (
    <button onClick={logout}>Logout</button>
  );
}
```

### useState (Component State)
```tsx
const [data, setData] = useState(initialValue);
const [loading, setLoading] = useState(true);
const [error, setError] = useState('');
```

### useEffect (Side Effects)
```tsx
useEffect(() => {
  // Run on mount
  fetchData();
}, []); // Empty dependency array = mount only

useEffect(() => {
  // Run when page changes
  fetchData();
}, [page]); // Re-run when page changes
```

---

## Error Handling Pattern

All components follow consistent error handling:

```tsx
const handleAction = async () => {
  setActionLoading(id);
  setError('');
  try {
    await apiFunction();
    setSuccess('Action completed');
  } catch (err) {
    setError(err instanceof Error ? err.message : 'Action failed');
  } finally {
    setActionLoading(null);
  }
};
```

Error display:
```tsx
{error && (
  <div className="rounded-md bg-red-50 p-4 text-red-700">
    {error}
  </div>
)}
```

---

## Performance Considerations

- Components use `useState` for local state
- API calls in `useEffect` hooks
- Pagination prevents loading large datasets
- Loading states prevent duplicate requests
- Confirmation dialogs prevent accidental deletions
- Form validation before API submission

---

## Browser API Usage

- **localStorage**: Token persistence
  ```tsx
  localStorage.setItem('adminToken', token);
  localStorage.getItem('adminToken');
  localStorage.removeItem('adminToken');
  ```

- **fetch**: API communication
  ```tsx
  const response = await fetch(url, {
    headers: getHeaders(),
  });
  ```

- **FormEvent**: Form handling
  ```tsx
  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
  };
  ```

---

## Version Information

- **Next.js**: 16.1.1
- **React**: 19.2.3
- **TypeScript**: Latest
- **Tailwind CSS**: 4.x
- **Node**: 18+

---

This reference covers all components, props, state, and API interactions for the admin dashboard.
