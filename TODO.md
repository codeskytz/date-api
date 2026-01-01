# Profile Tab User Fetching - Fix TODO

## Issues Identified
- [x] API response structure mismatch between backend and frontend
- [x] Missing error handling in profile screen
- [x] No loading states while fetching data
- [x] Missing dependency in useEffect
- [x] Inconsistent API documentation

## Tasks to Complete

### 1. Fix AuthContext.tsx
- [x] Fix `refreshUser()` to correctly parse `response.data.data`
- [x] Add proper error handling in `refreshUser()`
- [x] Fix `verifyOtp()` to use correct response structure
- [x] Fix `login()` to handle response structure consistently

### 2. Fix Profile Screen (profile.tsx)
- [x] Add loading state while fetching user data
- [x] Add error state and error display UI
- [x] Add pull-to-refresh functionality
- [x] Fix useEffect dependency array
- [x] Add retry mechanism on error

### 3. Update API Documentation
- [x] Update `docs/api/user_me.json` to match actual backend response

### 4. Testing
- [ ] Test profile tab loads correctly
- [ ] Test error handling when API is unavailable
- [ ] Test pull-to-refresh functionality
- [ ] Verify all user data displays correctly

## Summary of Changes

### AuthContext.tsx
- Fixed `refreshUser()` to parse `response.data.data` correctly
- Added error re-throwing to allow caller to handle errors
- Fixed `verifyOtp()` to use correct response structure
- Fixed `login()` to handle different response structures consistently

### Profile Screen (profile.tsx)
- Added loading state with ActivityIndicator
- Added error state with retry button
- Implemented pull-to-refresh functionality using RefreshControl
- Fixed useEffect dependency array with useCallback
- Added error banner for non-critical errors
- Added comprehensive error handling

### API Documentation
- Updated `docs/api/user_me.json` to reflect actual backend response structure
- Added notes about response format and included fields
