# 🎴 Uma Tracker (Legacy)

![Uma Tracker Logo](https://i.imgur.com/XYZ1234.png)

Track, manage, and optimize your Uma Musume training careers

---

![Build Status](https://github.com/extremerazr/uma-tracker/workflows/tests/badge.svg)
![Laravel Version](https://img.shields.io/badge/Laravel-11+-FF2D20?logo=laravel)
![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

---

## 🌟 Features

### Training Management

- 🐎 Complete character profiles with aptitudes
- 📊 Turn-by-turn stat tracking (Speed/Stamina/Power/Guts/Wit)
- 🧠 Skill acquisition logging with SP management
- 📈 Growth rate bonus tracking

### Game-Inspired UI

- 🎴 Authentic Uma Musume visual style
- 🌙 Dark/light mode toggle
- 📱 Fully responsive mobile interface
- 🎮 Interactive training dashboard

### Data Tools

- ⬇️ Excel export for career runs
- 🔍 Skill search and autocomplete
- 📊 Stat progression visualization
- 🔄 Import/export functionality

---

## 🛠 Technology Stack

### Backend

- Laravel 11+ (legacy)
- PHP 8.2+
- MySQL/MariaDB

### Frontend

- Blade Components
- Tailwind CSS
- Alpine.js
- Laravel Livewire (partials)

### Extensions

- Laravel Excel (Maatwebsite)
- Laravel Sanctum (API)

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.0+
- Node.js 16+
- MySQL 5.7+

### Installation

```bash
# Clone repository
# (Legacy repo URL, update if needed)
git clone https://github.com/extremerazr/uma-tracker.git
cd uma-tracker

# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Set up database (edit .env first)
php artisan migrate --seed

# Run development server
php artisan serve
```

Visit `http://localhost:8000` and login with:

- 📧 `test@example.com`
- 🔑 `password`

---

## 📂 Project Structure

```text
uma-tracker/
├── app/
│   ├── Exports/          # Excel export handlers
│   ├── Http/Controllers/ # Application logic
│   ├── Models/           # Database models
│   └── View/Components/  # Blade components
├── database/
│   ├── factories/        # Test data generators
│   ├── migrations/       # Database schema
│   └── seeders/          # Initial data
├── resources/
│   ├── js/               # Alpine.js components
│   ├── views/            # Blade templates
│   └── css/              # Tailwind styles
└── routes/               # Application endpoints
```

---

## 🧩 Core Components

| Component                 | Description                                |
| ------------------------- | ------------------------------------------ |
| `<x-uma::skill-card>`     | Interactive skill management card          |
| `<x-uma::stamina-bar>`    | Animated stamina gauge                     |
| `<x-uma::stat-radial>`    | Circular stat progress indicator           |
| `<x-uma::aptitude-badge>` | Style/distance/track suitability indicator |
| `<x-uma::training-log>`   | Turn history timeline                      |

---

## 🔌 API Endpoints

### Character Management

- `GET /api/uma` - List characters
- `POST /api/uma` - Create new character
- `GET /api/uma/{id}` - Character details

### Career Tracking

- `POST /api/career` - Start new career
- `POST /api/career/{id}/stats` - Log turn stats
- `POST /api/career/{id}/skills` - Manage skills

### Data Export

- `GET /api/export/career/{id}` - Excel export
- `GET /api/export/skills` - Skill report

---

## 📊 Screenshots

![Dashboard](https://i.imgur.com/ABC123.jpg)
_Training dashboard with stat overview_

![Career View](https://i.imgur.com/DEF456.jpg)
_Detailed career progression tracking_

---

## 🛣 Roadmap (Legacy)

### v1.2 (Current)

- [x] Dark mode support
- [x] Mobile optimization
- [ ] Enhanced stat charts

### v1.3 (Next)

- [ ] AI training suggestions
- [ ] Race simulation
- [ ] Multi-language support

### v2.0 (Future)

- [ ] Team management
- [ ] Scenario builder
- [ ] Community sharing

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📜 License

MIT License - See [LICENSE](LICENSE) for details.

---

> "ウマ娘、頑張って！" - Built with ❤️ for Uma Musume fans

---

**Note:** This is a legacy/deprecated version of Uma Tracker. For the latest features, bug fixes, and documentation, please refer to the main `README.md` in this repository.
