-- ══════════════════════════════════════════════════════════════════
--  BudgetSync — Schéma complet (v2)  |  Base : budsync
-- ══════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS budsync
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE budsync;

-- ── 1. Utilisateurs ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    nom                VARCHAR(100) NOT NULL,
    prenom             VARCHAR(100) NOT NULL,
    email              VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe       VARCHAR(255) NOT NULL,
    role               ENUM('admin','utilisateur') DEFAULT 'utilisateur',
    statut             ENUM('actif','en_attente','bloque','suspendu') DEFAULT 'en_attente',
    date_inscription   DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME DEFAULT NULL
);

-- ── 2. Demandes de suppression ────────────────────────────────────
CREATE TABLE IF NOT EXISTS demandes_suppression (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL UNIQUE,
    motif        TEXT,
    nom_utilisateur VARCHAR(100) NULL DEFAULT NULL,
    prenom_utilisateur VARCHAR(100) NULL DEFAULT NULL,
    statut       ENUM('en_attente','validee','refusee') DEFAULT 'en_attente',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 3. Notifications ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    message     TEXT NOT NULL,
    lue         TINYINT(1) DEFAULT 0,
    date_notif  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 4. Catégories ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nom       VARCHAR(100) NOT NULL,
    icone     VARCHAR(50)  DEFAULT 'bi-tag',
    couleur   VARCHAR(20)  DEFAULT '#6366f1',
    user_id   INT          DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 5. Budgets ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS budgets (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(150) NOT NULL,
    description     TEXT,
    type            ENUM('individuel','partage') DEFAULT 'individuel',
    periode         ENUM('mensuel','hebdomadaire','personnalise') DEFAULT 'mensuel',
    date_debut      DATE NOT NULL,
    date_fin        DATE NOT NULL,
    plafond_global  DECIMAL(10,2) DEFAULT NULL,
    seuil_alerte    INT DEFAULT 80,
    proprietaire_id INT NOT NULL,
    statut          ENUM('actif','depasse','proche_limite','expire') DEFAULT 'actif',
    date_creation   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proprietaire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 6. Membres d'un budget ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS budget_membres (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    budget_id   INT NOT NULL,
    user_id     INT NOT NULL,
    role        ENUM('proprietaire','membre') DEFAULT 'membre',
    date_ajout  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_membre (budget_id, user_id),
    FOREIGN KEY (budget_id) REFERENCES budgets(id)      ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 7. Plafonds par catégorie ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS budget_plafonds (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    budget_id    INT NOT NULL,
    categorie_id INT NOT NULL,
    plafond      DECIMAL(10,2) NOT NULL,
    UNIQUE KEY uq_plafond (budget_id, categorie_id),
    FOREIGN KEY (budget_id)    REFERENCES budgets(id)    ON DELETE CASCADE,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ── 8. Transactions ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS transactions (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL,
    budget_id        INT DEFAULT NULL,
    categorie_id     INT DEFAULT NULL,
    type             ENUM('revenu','depense') NOT NULL,
    montant          DECIMAL(10,2) NOT NULL,
    description      VARCHAR(255),
    date_transaction DATE NOT NULL,
    commentaire      TEXT,
    date_creation    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (budget_id)    REFERENCES budgets(id)      ON DELETE SET NULL,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)   ON DELETE SET NULL
);

-- ── 9. Invitations ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS invitations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    budget_id       INT NOT NULL,
    invite_par      INT NOT NULL,
    email_invite    VARCHAR(150) NOT NULL,
    statut          ENUM('en_attente','accepte','refuse') DEFAULT 'en_attente',
    token           VARCHAR(64) UNIQUE,
    date_invitation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id)  REFERENCES budgets(id)      ON DELETE CASCADE,
    FOREIGN KEY (invite_par) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── 10. Alertes ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS alertes (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    budget_id         INT NOT NULL,
    user_id           INT NOT NULL,
    type              ENUM('seuil','depassement') NOT NULL,
    seuil_pourcentage INT DEFAULT 80,
    message           TEXT,
    lue               TINYINT(1) DEFAULT 0,
    date_alerte       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (budget_id) REFERENCES budgets(id)      ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ══════════════════════════════════════════════════════════════════
--  SEEDS
-- ══════════════════════════════════════════════════════════════════
-- admin@budget.local / password
INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('Admin','Système','admin@budget.local',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin','actif');

INSERT IGNORE INTO categories (nom, icone, couleur, user_id) VALUES
('Alimentation','bi-basket',       '#f59e0b', NULL),
('Transport',   'bi-car-front',    '#3b82f6', NULL),
('Logement',    'bi-house',        '#8b5cf6', NULL),
('Santé',       'bi-heart-pulse',  '#ef4444', NULL),
('Loisirs',     'bi-controller',   '#10b981', NULL),
('Études',      'bi-mortarboard',  '#6366f1', NULL),
('Vêtements',   'bi-bag',          '#ec4899', NULL),
('Épargne',     'bi-piggy-bank',   '#14b8a6', NULL),
('Salaire',     'bi-cash-coin',    '#22c55e', NULL),
('Autres',      'bi-three-dots',   '#94a3b8', NULL);