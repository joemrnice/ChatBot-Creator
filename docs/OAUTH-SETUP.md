# ChatBot Creator Studio — Social Authentication (OAuth 2.0) Setup Guide

ChatBot Creator Studio supports native social authentication via **GitHub**, **Google**, and **Facebook**, allowing users to register and sign in with a single click.

All OAuth providers use secure state validation, email verification checks, and safe account linking.

---

## 1. GitHub OAuth Configuration

1. Log into GitHub and go to **Settings > Developer Settings > OAuth Apps**.
2. Click **New OAuth App**.
3. Fill in the fields:
   - **Application Name**: `ChatBot Creator Studio`
   - **Homepage URL**: `https://your-domain.com`
   - **Authorization Callback URL**:
     ```text
     https://your-domain.com/auth/github/callback
     ```
4. Click **Register Application**.
5. Generate a **Client Secret**.
6. In your `.env`, configure:
   ```env
   GITHUB_CLIENT_ID=your_github_client_id
   GITHUB_CLIENT_SECRET=your_github_client_secret
   GITHUB_REDIRECT_URI=https://your-domain.com/auth/github/callback
   ```

---

## 2. Google OAuth Configuration

1. Open the [Google Cloud Console](https://console.cloud.google.com/).
2. Create or select a project.
3. Navigate to **APIs & Services > Credentials**.
4. Configure your **OAuth Consent Screen**:
   - User Type: **External**
   - Scopes: `.../auth/userinfo.email`, `.../auth/userinfo.profile`, `openid`
5. Click **Create Credentials > OAuth Client ID**:
   - Application Type: **Web Application**
   - Authorized Redirect URIs:
     ```text
     https://your-domain.com/auth/google/callback
     ```
6. In your `.env`, configure:
   ```env
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   GOOGLE_REDIRECT_URI=https://your-domain.com/auth/google/callback
   ```

---

## 3. Facebook OAuth Configuration

1. Visit [Meta for Developers](https://developers.facebook.com/).
2. Click **My Apps > Create App > Authenticate and request data from users with Facebook Login**.
3. Under **Facebook Login > Settings**, add the Valid OAuth Redirect URI:
   ```text
   https://your-domain.com/auth/facebook/callback
   ```
4. Copy the **App ID** and **App Secret** from **App Settings > Basic**.
5. In your `.env`, configure:
   ```env
   FACEBOOK_CLIENT_ID=your_facebook_app_id
   FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
   FACEBOOK_REDIRECT_URI=https://your-domain.com/auth/facebook/callback
   ```

---

## 4. Graceful Fallback & Safe Account Linking

- **Independent Credentials**: If one or more OAuth providers are left blank or disabled in `.env`, the social login button is cleanly hidden or returns a helpful message without breaking ordinary email/password authentication.
- **Account Linking Security**: Accounts are linked only when the OAuth provider certifies that the user's email address has been verified. Unverified third-party emails are never automatically merged into existing user accounts.
