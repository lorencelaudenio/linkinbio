# 🚀 MyBio — WordPress Link-in-Bio Builder

🌐 Website: https://mybio.page.gd/

MyBio is a lightweight Link-in-Bio system (Linktree-style) built for WordPress using WPCode snippets.  
It allows users to create profile pages, add unlimited links, and track clicks in a simple dashboard.

---

# ⚙️ Requirements

Before installing, make sure you already have:

- A hosting provider (cPanel or similar)
- A working WordPress installation
- WPCode installed and activated

---

# 📦 Installation Guide

## 1. Add the Code to WPCode

1. Go to your WordPress Admin Dashboard
2. Install and activate WPCode plugin
3. Go to:

```text
WPCode → Add Snippet
```

4. Click “Add New Snippet”
5. Select:

```text
Code Type: PHP Snippet
```

6. Copy and paste the full MyBio code into the editor
7. Click **Save & Activate**

---

## 2. Create Required Pages

You must create the following pages in WordPress:

### 🧑 Dashboard Page

- Go to `Pages → Add New`
- Title:

```text
Dashboard
```

- Add this shortcode:

```text
[lv_dashboard]
```

- Publish the page

---

### 🔐 Login Page

Create a page named:

```text
Login
```

Add shortcode:

```text
[lv_login]
```

---

### 📝 Register Page

Create a page named:

```text
Register
```

Add shortcode:

```text
[lv_register]
```

---

# 🌐 How It Works

Once installed:

- Users can register and login
- Each user gets a personal profile page:

```text
/u/username
```

- Users can manage links inside the dashboard
- Clicks are automatically tracked via:

```text
/go/ID
```

---

# ✨ Features

- User registration & login system
- Personal bio/profile pages
- Unlimited link management
- Click tracking analytics
- AJAX inline link editing
- Custom database tables
- Lightweight WordPress integration (via WPCode)

---

# 🔁 Important Notes

After installation, go to:

```text
Settings → Permalinks → Save Changes
```

to activate custom URLs.

- Make sure WPCode snippet is **Active**
- Do not paste code inside theme files — only use WPCode

---

# 📊 URL Structure

### Profile Page

```text
/u/username
```

### Link Redirect Tracking

```text
/go/{id}
```

---

# ⚠️ Disclaimer

This project is a custom WordPress-based system and requires a working hosting environment.  
It is not a standalone SaaS platform.
