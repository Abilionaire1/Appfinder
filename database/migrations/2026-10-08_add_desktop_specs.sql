-- Run once on existing AppFinder databases before deploying the related PHP changes.
ALTER TABLE apps
    ADD COLUMN min_ram DECIMAL(4,1) UNSIGNED NULL DEFAULT NULL AFTER platforms,
    ADD COLUMN recommended_ram DECIMAL(4,1) UNSIGNED NULL DEFAULT NULL AFTER min_ram,
    ADD COLUMN min_os VARCHAR(100) NULL DEFAULT NULL AFTER recommended_ram,
    ADD COLUMN winget_id VARCHAR(150) NULL DEFAULT NULL AFTER min_os;
