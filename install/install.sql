
-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  name VARCHAR(120) NOT NULL,
  role ENUM('admin','editor','moderator') DEFAULT 'admin',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Riddles
CREATE TABLE IF NOT EXISTS riddles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(190) UNIQUE NOT NULL,
  title VARCHAR(200) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  answer MEDIUMTEXT NOT NULL,
  difficulty ENUM('easy','medium','hard','difficult') DEFAULT 'easy',
  length ENUM('short','long','simple') DEFAULT 'short',
  status ENUM('draft','published') DEFAULT 'published',
  views INT UNSIGNED NOT NULL DEFAULT 0,
  up_votes INT UNSIGNED NOT NULL DEFAULT 0,
  down_votes INT UNSIGNED NOT NULL DEFAULT 0,
  published_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  FULLTEXT KEY ft (title, body, answer)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS riddle_category (
  riddle_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (riddle_id, category_id),
  FOREIGN KEY (riddle_id) REFERENCES riddles(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tags
CREATE TABLE IF NOT EXISTS tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  slug VARCHAR(80) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS riddle_tag (
  riddle_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (riddle_id, tag_id),
  FOREIGN KEY (riddle_id) REFERENCES riddles(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Votes
CREATE TABLE IF NOT EXISTS votes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  riddle_id INT NOT NULL,
  direction TINYINT NOT NULL,  -- 1=up, -1=down
  ip VARBINARY(16) NULL,
  ua_hash CHAR(64) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_vote (riddle_id, ip, ua_hash),
  FOREIGN KEY (riddle_id) REFERENCES riddles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pages
CREATE TABLE IF NOT EXISTS pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) UNIQUE NOT NULL,
  title VARCHAR(160) NOT NULL,
  body MEDIUMTEXT NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Settings
CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(120) PRIMARY KEY,
  v MEDIUMTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Translations cache
CREATE TABLE IF NOT EXISTS translations (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  hash CHAR(64) UNIQUE NOT NULL,
  source_text MEDIUMTEXT NOT NULL,
  translated_text MEDIUMTEXT NOT NULL,
  source_lang VARCHAR(10) DEFAULT NULL,
  target_lang VARCHAR(10) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed minimal pages
INSERT IGNORE INTO pages(slug,title,body) VALUES
('terms','Terms','<p>Terms will be here.</p>'),
('privacy','Privacy','<p>Privacy policy will be here.</p>'),
('advertising','Advertising','<p>Advertising info.</p>'),
('contact','Contact','<p>Contact us.</p>'),
('app','SolveClone App','<p>Install this web app from your device menu.</p>');

-- Seed sample categories
INSERT IGNORE INTO categories(name,slug) VALUES
('Logic','logic'),
('Math','math'),
('Wordplay','wordplay');
