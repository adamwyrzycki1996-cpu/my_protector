# MyProtector - Visual Architecture Guide

## For Zoom Call Review

This document provides visual diagrams and flowcharts to help during the review meeting.

---

## 1. System Overview

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           MYPROTECTOR PLATFORM                                 │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                           VISITORS                                       │   │
│  │    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                │   │
│  │    │   Browse     │  │  Write       │  │   Search     │                │   │
│  │    │   Reviews    │  │  Review      │  │   Company    │                │   │
│  │    └──────┬───────┘  └──────┬───────┘  └──────┬───────┘                │   │
│  │           │                 │                 │                        │   │
│  └───────────┼─────────────────┼─────────────────┼────────────────────────┘   │
│              │                 │                 │                            │
│              ▼                 ▼                 ▼                            │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                      PUBLIC INTERFACE                                   │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │   │
│  │  │  Homepage   │  │   Company    │  │   Review     │  │   Blog      │   │   │
│  │  │   (Hero)    │  │   Profile    │  │   Page       │  │   Posts     │   │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘   │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                      │                                        │
│                                      ▼                                        │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                     REGISTERED USER AREA                                 │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │   │
│  │  │ Individual  │  │  Business   │  │  Reseller   │  │   Admin     │   │   │
│  │  │  Dashboard  │  │  Dashboard  │  │  Dashboard  │  │  Dashboard  │   │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘   │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                      BACKEND SERVICES                                    │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │   │
│  │  │   Email     │  │   Trust     │  │   Search    │  │   Widget    │   │   │
│  │  │   System    │  │   Engine    │  │   Engine    │  │   Generator │   │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘   │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Traffic Light System Flow

```
                              COMPANY PROFILE
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │     REQUIREMENTS CHECK        │
                    │  ┌─────────────────────────┐  │
                    │  │ □ Insurance Added       │  │
                    │  │ □ Terms URL Added       │  │
                    │  │ □ Promise Page Added    │  │
                    │  │ □ Admin Verified        │  │
                    │  │ □ Reviews ≥ 50          │  │
                    │  │ □ Rating ≥ 4.5         │  │
                    │  └─────────────────────────┘  │
                    └───────────────────────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
              ┌───────────┐   ┌───────────┐   ┌───────────┐
              │  ALL MET  │   │ PARTIAL   │   │  NONE     │
              │           │   │           │   │           │
              └─────┬─────┘   └─────┬─────┘   └─────┬─────┘
                    │               │               │
                    ▼               ▼               ▼
              ┌───────────┐   ┌───────────┐   ┌───────────┐
              │   🟢 GREEN │   │  🟡 YELLOW│   │   🔴 RED  │
              │  WALKING   │   │  SHOPPING │   │    BAD    │
              │  Verified  │   │ Unverified│   │ Risky     │
              └───────────┘   └───────────┘   └───────────┘
                    │               │               │
                    └───────────────┼───────────────┘
                                    │
                                    ▼
                          TRUST DISPLAY ON
                          COMPANY PROFILE PAGE
```

---

## 3. Review Submission Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    REVIEW SUBMISSION FLOW                        │
└─────────────────────────────────────────────────────────────────┘

    ┌─────────┐         ┌─────────────┐         ┌─────────────┐
    │ Visitor │         │  Company    │         │   Email     │
    │   on    │────────▶│   Page      │────────▶│  Invitation │
    │ Site    │         └─────────────┘         └─────────────┘
    └─────────┘                                       │
         │                                            │
         │ Click "Write Review"                       │
         ▼                                            │
    ┌─────────────┐                                    │
    │  Review     │◀───────────────────────────────────┘
    │   Form      │
    └─────────────┘
         │
         │ Submit Review
         ▼
    ┌─────────────┐
    │  Validate   │──────── Error ──────▶ Show Form Again
    │   Input     │
    └─────────────┘
         │
         │ Valid
         ▼
    ┌─────────────┐
    │   Review    │──────── Auto-Approve? ──▶ Yes ──▶ Publish
    │   Status    │
    └─────────────┘
         │
         │ No
         ▼
    ┌─────────────┐
    │  Admin      │──────── Approve ──▶ Publish
    │   Queue     │         │
    └─────────────┘         │
                            │ Reject
                            ▼
                       Show Rejection
```

---

## 4. User Role Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                      USER REGISTRATION FLOW                      │
└─────────────────────────────────────────────────────────────────┘

                    ┌─────────────────┐
                    │   Landing Page   │
                    │  "Sign Up" CTA   │
                    └────────┬────────┘
                             │
                ┌────────────┼────────────┐
                │            │            │
                ▼            ▼            ▼
        ┌──────────┐  ┌──────────┐  ┌──────────┐
        │Individual│  │ Business │  │ Reseller  │
        │ Register │  │  Claim   │  │  Apply   │
        └────┬─────┘  └────┬─────┘  └────┬─────┘
             │             │             │
             ▼             ▼             ▼
        ┌──────────┐  ┌──────────┐  ┌──────────┐
        │  Email   │  │  Admin   │  │  Admin   │
        │Confirm   │  │ Verify   │  │ Approve  │
        └────┬─────┘  └────┬─────┘  └────┬─────┘
             │             │             │
             ▼             ▼             ▼
        ┌──────────┐  ┌──────────┐  ┌──────────┐
        │Individual│  │ Business │  │ Reseller │
        │Dashboard │  │ Dashboard│  │ Dashboard│
        └──────────┘  └──────────┘  └──────────┘
```

---

## 5. Dashboard Menu Structure

### Admin Dashboard Menu
```
📊 Dashboard (Overview)
├── 📈 Analytics
├── 📝 Reviews
│   ├── All Reviews
│   ├── Pending Approval
│   ├── Flagged Reviews
│   └── AI Moderation
├── 🏢 Companies
│   ├── All Companies
│   ├── Pending Claims
│   └── Blacklist
├── 👥 Users
│   ├── All Users
│   ├── Individuals
│   ├── Businesses
│   ├── Resellers
│   └── Support Agents
├── 🎫 Support
│   ├── Tickets
│   └── Responses
├── 💰 Payments
│   ├── Reseller Commissions
│   ├── Invoices
│   └── Transactions
├── 📧 Email
│   ├── Templates
│   ├── Campaigns
│   └── Logs
├── 🔧 Settings
│   ├── General
│   ├── SEO
│   ├── Widgets
│   └── API Keys
└── 📋 Audit Log
```

### Business Dashboard Menu
```
🏢 Company Profile
├── 📊 Overview (Trust Score, Reviews)
├── ⭐ Reviews
│   ├── All Reviews
│   └── Write Response
├── 📈 Analytics
│   ├── Rating Trends
│   └── Review Sources
├── 🎯 Marketing
│   ├── Invite Customers
│   ├── Widget Codes
│   └── Share Links
├── 👥 Team
│   └── Manage Users
├── ⚙️ Settings
│   ├── Company Info
│   ├── Documents
│   ├── Notifications
│   └── Billing
└── 💬 Support
```

### Individual Dashboard Menu
```
👤 My Account
├── 📝 My Reviews
│   ├── Written Reviews
│   └── Drafts
├── ⭐ Helpful Marks
├── 🎫 My Tickets
├── 📧 Notifications
├── 🔐 Security
│   ├── Change Password
│   └── 2FA
└── ⚙️ Preferences
```

---

## 6. Page Hierarchy

```
HOME PAGE
├── Hero Section (Trust Signal Demo)
├── Search Bar (Find Companies)
├── Featured Categories
├── Top Rated Companies
├── Recent Reviews
├── Widget Preview
├── Trust Signal Explainer
├── About Section
├── Developer/Co-Founder Section
└── Footer

COMPANY PROFILE PAGE
├── Header (Name, Logo, Traffic Light)
├── Trust Badge (🟢 Walking / 🟡 Shopping / 🔴 Bad)
├── Rating Summary (Stars, Count, Trend)
├── Key Metrics (Response Rate, Avg Response Time)
├── Reviews List
│   ├── Review Cards
│   ├── Response from Company
│   └── Helpful/Report buttons
├── Write Review CTA
├── About Company
├── Contact Information
└── Widget Embed Code

REVIEW PAGE
├── Company Header
├── Review Title & Content
├── Star Rating
├── Helpful Count
├── Verified Badge (if applicable)
├── Posted Date
├── Company Response (if any)
└── Share Buttons

ABOUT US PAGE
├── Mission Statement
├── Team Section
│   ├── [Developer Name] - Co-Founder
│   │   ├── Photo
│   │   ├── Bio
│   │   ├── LinkedIn
│   │   └── Other Links
│   └── Other Team Members
├── Company Values
└── Contact Information

DASHBOARD PAGES
├── Sidebar Navigation
├── Main Content Area
│   ├── Stats Cards
│   ├── Action Buttons
│   └── Content Lists
└── Footer with Support Links
```

---

## 7. Widget Types

### Widget 1: Classic Badge
```
┌─────────────────────────────────┐
│  ⭐⭐⭐⭐⭐  4.5/5             │
│  Based on 234 reviews           │
│  [Visit MyProtector]           │
└─────────────────────────────────┘
Size: 250x120px
Use: Product pages, Footer
```

### Widget 2: Mini Badge
```
┌─────────────────────┐
│  ⭐⭐⭐⭐⭐ 4.5  │
└─────────────────────┘
Size: 150x60px
Use: Cart, Sidebar
```

### Widget 3: Reviews Slider
```
┌─────────────────────────────────────────────────┐
│  Customer Reviews                    [Prev][Next]│
│  ┌───────────────────────────────────────────┐  │
│  │ ⭐⭐⭐⭐⭐ "Excellent service!"            │  │
│  │ John D. • Verified Purchase               │  │
│  │ "Great experience overall..."              │  │
│  └───────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
Size: 300x200px
Use: Homepage, Sidebar
```

### Widget 4: Popup/Lightbox (Planned)
```
┌─────────────────────────────────────────────────┐
│  See all [Company] reviews on MyProtector       │
│  ┌───────────────────────────────────────────┐  │
│  │      [Review Summary Stats]               │  │
│  │  [See All Reviews →]                      │  │
│  └───────────────────────────────────────────┘  │
│  [Remind Me Later] [Dismiss]                    │
└─────────────────────────────────────────────────┘
Trigger: User inactivity
Use: All pages
```

---

## 8. Email Template Structure

### Stage 1 Emails (Basic)
1. **Welcome Email** - New registration
2. **Email Verification** - Confirm email address
3. **Password Reset** - Forgotten password
4. **Review Invitation** - Request review from customer
5. **Review Confirmation** - Review submitted successfully
6. **Review Published** - Review is live
7. **Review Response** - Business responded to review

### Stage 2+ Emails (40+ Templates)
```
CATEGORY: User
├── Welcome series (3 emails)
├── Password reset
├── Email verification
├── Profile updated
└── Account deleted

CATEGORY: Reviews
├── Invitation (initial)
├── Invitation (reminder)
├── Thank you
├── Published notification
├── Response notification
├── Helpful notification
├── Report confirmation
└── Moderation decision

CATEGORY: Business
├── Claim request
├── Claim approved
├── Trust status change
├── Widget download
└── Analytics summary

CATEGORY: Reseller
├── Application received
├── Application approved
├── New referral
├── Commission earned
├── Payout notification
└── Monthly report

CATEGORY: Support
├── Ticket received
├── Ticket assigned
├── Response added
├── Ticket resolved
└── Satisfaction survey

CATEGORY: Marketing
├── Newsletter
├── Feature announcement
├── Seasonal campaign
└── Re-engagement
```

---

## 9. Integration Points

```
┌─────────────────────────────────────────────────────────────────┐
│                     INTEGRATION ARCHITECTURE                     │
└─────────────────────────────────────────────────────────────────┘

EXTERNAL SERVICES:
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ WooCommerce  │    │   Spotify    │    │  Other APIs  │
│   (Orders)   │    │  (Music)    │    │              │
└──────┬───────┘    └──────┬───────┘    └──────┬───────┘
       │                   │                   │
       └───────────────────┼───────────────────┘
                           │
                           ▼
                 ┌─────────────────────┐
                 │   MyProtector API  │
                 │   Integration      │
                 │      Layer         │
                 └──────────┬──────────┘
                            │
       ┌────────────────────┼────────────────────┐
       │                    │                    │
       ▼                    ▼                    ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Reviews    │    │   Widgets   │    │   Trust     │
│   System    │    │   Generator │    │   Signal    │
└─────────────┘    └─────────────┘    └─────────────┘
```

---

## 10. Development Phases Timeline

```
PHASE 1 (Week 1-2) - Foundation - $750 Milestone
├── Database Setup
├── User Roles
├── Theme Foundation
├── All Branding Changes
├── Individual Dashboard
├── Business Dashboard
├── Admin Dashboard
├── Support Dashboard
├── Email System (6 templates)
├── 3 Review Widgets
├── WooCommerce Plugin
├── SEO Configuration
├── Blog Setup
└── Developer Profile

PHASE 2 (Week 3-4) - Core Functionality
├── Review Moderation
├── Trust Algorithm
├── Company Features
├── Analytics
└── Full Email System

PHASE 3 (Week 5-6) - Advanced Features
├── AI Moderation
├── Reseller System
├── API Documentation
├── Blacklist System
└── Bug Reporting

PHASE 4 (Week 7-8) - Integration & Polish
├── Payments
├── Invoicing
├── Social Media
├── Live Chat
├── Performance
└── Go-Live
```

---

## 11. Required Changes Checklist (Stage 1)

### Branding Changes
- [ ] Logo everywhere
- [ ] "MyProtector" in all headings
- [ ] "MyProtector" in meta tags
- [ ] "MyProtector" in button text
- [ ] "MyProtector" in URLs
- [ ] "MyProtector" in footer
- [ ] "MyProtector" in emails
- [ ] Remove "Trustpilot" references
- [ ] Favicon/Icon changes
- [ ] Social share images

### Content Changes
- [ ] Homepage hero text
- [ ] About page
- [ ] Contact page
- [ ] FAQ page
- [ ] How it works page
- [ ] Pricing page (if any)
- [ ] Footer links
- [ ] Error pages
- [ ] Email templates

### User-Facing Text
- [ ] Button labels
- [ ] Form labels
- [ ] Placeholder text
- [ ] Error messages
- [ ] Success messages
- [ ] Notification text
- [ ] Dashboard labels

---

*Visual Architecture Guide - For Zoom Call Review*
*Document Version: 1.0 - 2026-06-02*