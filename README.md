# 🌿 SustainaQuest

> **A web-based, gamified sustainability engagement platform designed for the APU community.**
 
SustainaQuest leverages **Artificial Intelligence** and **Digital Gamification** to encourage eco-friendly behaviors through daily challenges, competitive leaderboards, and a reward redemption system.

---

## 📘 Technical Overview

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Frontend** | HTML5, CSS3, JS | Vanilla JavaScript for responsive UI. |
| **AI Engine** | **TensorFlow.js** | Uses **MobileNet** model for client-side image classification. |
| **Backend** | PHP | Handles server-side logic and session management. |
| **Database** | MySQL | Relational database for storing and retrieving users, quests, and etc. |

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
* **Dynamic Badges:** Automated checks run on login to award badges (e.g., *Green Rookie, Sustainability God*) based on milestones.
* **Leaderboard:** Ranks users dynamically based on total Green Points and Levels.

### 🛡️ 5. Security & Validation
* **Access Control:** Strict session management; unauthorized direct access to scripts is blocked.
* **Encryption:** Passwords are secured using **Bcrypt hashing**.

---

## 👥 User Roles & Access Control

| Role | Access Level | Key Functions |
| :--- | :--- | :--- |
| 👤 **Visitor** | **Read-Only** | View homepage, quests, leaderboard, and shop catalog. Cannot submit evidence or redeem items. |
| 🧑‍🎓 **User** | **Full Access** | Participate in quests, upload evidence, earn XP/Points, redeem rewards, and track history. |
| 🧑‍💻 **Moderator** | **Verification** | Review Weekly Quest "Pending" submissions and manage quest content. |
| 👑 **Admin** | **System Control** | Full system access. Manage users, shop inventory, view reports, and perform all moderator actions. |

---

## 🔐 Demo Login Credentials

Use the following accounts to test the system features.

### 👑 Admin Account
| Username | Password |
| :--- | :--- |
| `adam` | `admin` |

### 🧑‍💻 Moderator Accounts
*All moderators share the same password.*
> **Password:** `password123`

* `Mod_Sarah`
* `Mod_Mike`
* `Mod_Leo`

### 🧑‍🎓 User Accounts
*All users share the same password.*
> **Password:** `password123`

<details>
<summary><strong>👇 Click to expand list of User Accounts</strong></summary>

| Username |
| :--- |
| `Adam_Super` |
| `Eco_Warrior_X` |
| `Green_Queen` |
| `Planet_Protector` |
| `Solar_Sam` |
| `Nature_Nora` |
| `Recycle_Rick` |
| `Bio_Bella` |
| `Windy_Wendy` |
| `Carbon_Carl` |
| `Clean_Air_Alice` |
| `Ocean_Orion` |
| `Litter_Larry` |
| `Plastic_Pat` |
| `Newbie_Tom` |

</details>

---

## 💻 Installation

1.  Download and install **WAMP Server**.
2.  Clone this repository into your `www` (WAMP) folder.
3.  Open **phpMyAdmin** and create a new database called "SustainaQuest" then import the `sustainaquest_db.sql` file into it.
4.  Access the site via `localhost/APU-SustainaQuest`.

---

