# 🌿 SustainaQuest

A web-based, gamified sustainability engagement platform designed for the APU community. SustainaQuest leverages **Artificial Intelligence** and **Digital Gamification** to encourage eco-friendly behaviors through daily challenges, competitive leaderboards, and a reward redemption system.

---

## 📘 Technical Overview

* **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
* **AI Engine:** **TensorFlow.js** with **MobileNet** model (Client-side Image Classification)
* **Backend:** PHP 
* **Database:** MySQL 
* **Server Environment:** WAMP (Apache/PHP configuration optimized for large media uploads)

---

## 👥 User Roles & Access Control

| Role | Access Level | Key Functions |
| :--- | :--- | :--- |
| 👤 **Visitor** | **Read-Only** | Can view homepage, available quests, leaderboard and the shop catalog but cannot submit evidence, redeem items or view history. |
| 🧑‍🎓 **User** | **Full Access** | Participate in quests, upload image/video evidence, earn XP/Points, redeem rewards, and track history. |
| 🧑‍💻 **Moderator** | **Verification** | Review Weekly Quest "Pending" submissions and manage quest content. |
| 👑 **Admin** | **System Control** | Manage users, configure shop inventory, view reports, and have full access of the entire system which can do all moderators can do. |

---

## 💡 Key Features

### 🤖 1. AI-Powered Quest Verification
* **Real-Time Analysis:** Daily quests are verified instantly in the browser using **TensorFlow.js**.
* **Smart Feedback:** The system analyzes the user's camera feed or upload *before* submission. If the object doesn't match the description, the upload is blocked, reducing server load and moderator backlog.

### 📹 2. Multi-Media Evidence Submission
* **Daily Quests:** Accept image uploads (optimized for quick AI scans).
* **Weekly Quests:** Accept **Video Evidence** (MP4/WebM) to document more complex tasks.

### 🛍️ 3. Secure Reward Shop
* **Transactional Integrity:** Uses **SQL Row Locking** to prevent "race conditions" where two users might try to redeem the last item simultaneously.
* **Validation:** Server-side checks ensure users have sufficient Green Points before processing any transaction.
* **Inventory Management:** Items distinguish between "Permanent" (always available) and "Limited" (rare stock).

### 🎮 4. Advanced Gamification System
* **XP & Leveling:** Users earn Experience Points (XP) alongside Green Points. Accumulating XP increases the User Level.
* **Dynamic Badges:** The system runs an automated check on every login to award badges (e.g., *Green Rookie, Sustainability God*) based on specific milestones (Level 10, 5000 Points, etc.).
* **Leaderboard:** Ranks users dynamically based on total Green Points and Levels.

### 🛡️ 5. Security & Validation
* **Access Control:** Strict session management protects user data; unauthorized direct access to scripts is blocked.
* **Data Safety:** All passwords are encrypted using **Bcrypt hashing**.
* **Input Sanitization:** All file uploads are validated for type and size to prevent malicious attacks or server crashes.

---
