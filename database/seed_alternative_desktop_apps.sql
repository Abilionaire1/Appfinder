-- Desktop catalog additions for the AlternativeTo Engine.
-- Requires migrations/2026-10-08_add_desktop_specs.sql to have been run first.
-- This seed is idempotent: existing slugs are updated with the curated alternative and spec data.

INSERT INTO categories (name, slug, icon, description)
VALUES ('Utility', 'utility', 'tools', 'Utilitas Windows untuk pencarian file, arsip, berbagi layar, dan pemeliharaan perangkat.')
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET @utility_category_id := (SELECT id FROM categories WHERE slug = 'utility' LIMIT 1);

INSERT INTO apps
    (name, slug, category_id, description, developer, icon, rating, reviews_count, play_store_url,
     is_free, featured, license, alternative_to, website_url, is_open_source, version, platforms,
     min_ram, recommended_ram, min_os, winget_id)
VALUES
    ('VSCodium', 'vscodium', 4, 'Editor kode berbasis Code-OSS yang mengutamakan privasi, kompatibel dengan banyak ekstensi VS Code, serta bebas telemetri Microsoft.', 'VSCodium', 'https://vscodium.com/img/codium_cnl.svg', 4.7, 0, '', 1, 1, 'MIT License', 'Visual Studio Code, Sublime Text, JetBrains WebStorm', 'https://vscodium.com/', 1, 'Latest', 'Windows, macOS, Linux', 2.0, 4.0, 'Windows 10 64-bit', 'VSCodium.VSCodium'),
    ('DBeaver Community', 'dbeaver-community', 4, 'Klien database lintas platform untuk MySQL, PostgreSQL, SQLite, dan banyak database lain dengan editor SQL serta diagram ER.', 'DBeaver Corp', 'https://dbeaver.io/wp-content/uploads/2015/09/beaver-head.png', 4.6, 0, '', 1, 0, 'Apache 2.0', 'TablePlus, Navicat, DataGrip', 'https://dbeaver.io/', 1, 'Latest', 'Windows, macOS, Linux', 2.0, 4.0, 'Windows 10 64-bit', 'dbeaver.dbeaver'),
    ('Git', 'git', 4, 'Sistem kontrol versi terdistribusi untuk melacak perubahan kode, bekerja dalam branch, dan berkolaborasi melalui repository.', 'The Git Development Community', 'https://git-scm.com/images/logos/downloads/Git-Icon-1788C.png', 4.8, 0, '', 1, 0, 'GPL v2', 'Plastic SCM, Perforce Helix Core', 'https://git-scm.com/', 1, 'Latest', 'Windows, macOS, Linux', 1.0, 2.0, 'Windows 7 64-bit', 'Git.Git'),
    ('OBS Studio', 'obs-studio', 3, 'Studio open-source untuk merekam layar, membuat video, dan live streaming dengan scene, audio mixer, serta plugin komunitas.', 'OBS Project', 'https://obsproject.com/assets/images/new_icon_small-r.png', 4.8, 0, '', 1, 1, 'GPL v2', 'Streamlabs Desktop, XSplit Broadcaster, Bandicam', 'https://obsproject.com/', 1, 'Latest', 'Windows, macOS, Linux', 4.0, 8.0, 'Windows 10 64-bit', 'OBSProject.OBSStudio'),
    ('Kdenlive', 'kdenlive', 3, 'Editor video non-linear open-source dengan multi-track timeline, proxy editing, efek, dan rendering untuk proyek kreatif.', 'KDE', 'https://kdenlive.org/wp-content/uploads/2020/07/kdenlive-logo.png', 4.6, 0, '', 1, 0, 'GPL v2+', 'Adobe Premiere Pro, Final Cut Pro, Filmora', 'https://kdenlive.org/', 1, 'Latest', 'Windows, macOS, Linux', 4.0, 8.0, 'Windows 10 64-bit', 'KDE.Kdenlive'),
    ('HandBrake', 'handbrake', 3, 'Transcoder video open-source untuk mengonversi dan mengompres video ke format modern dengan preset perangkat yang praktis.', 'HandBrake Team', 'https://handbrake.fr/img/icon.png', 4.7, 0, '', 1, 0, 'GPL v2', 'Wondershare UniConverter, WinX Video Converter', 'https://handbrake.fr/', 1, 'Latest', 'Windows, macOS, Linux', 2.0, 4.0, 'Windows 10 64-bit', 'HandBrake.HandBrake'),
    ('ONLYOFFICE Desktop Editors', 'onlyoffice-desktop-editors', 2, 'Suite dokumen gratis dengan editor teks, spreadsheet, dan presentasi serta kompatibilitas format Microsoft Office yang kuat.', 'Ascensio System SIA', 'https://upload.wikimedia.org/wikipedia/commons/9/9e/ONLYOFFICE_Logo.svg', 4.5, 0, '', 1, 0, 'AGPL v3', 'Microsoft 365, WPS Office', 'https://www.onlyoffice.com/desktop.aspx', 1, 'Latest', 'Windows, macOS, Linux', 2.0, 4.0, 'Windows 10 64-bit', 'ONLYOFFICE.DesktopEditors'),
    ('PDF24 Creator', 'pdf24-creator', 2, 'Toolkit PDF gratis untuk menggabungkan, memisahkan, mengonversi, mengompres, dan melindungi dokumen secara lokal di Windows.', 'geek software GmbH', 'https://www.pdf24.org/favicon.ico', 4.7, 0, '', 1, 0, 'Freeware', 'Adobe Acrobat Pro, Foxit PDF Editor', 'https://www.pdf24.org/', 0, 'Latest', 'Windows', 2.0, 4.0, 'Windows 7 64-bit', 'geeksoftwareGmbH.PDF24Creator'),
    ('Everything', 'everything', @utility_category_id, 'Pencarian file Windows sangat cepat yang mengindeks nama dan lokasi berkas sehingga hasil muncul seketika.', 'voidtools', 'https://www.voidtools.com/ES-Logo.png', 4.8, 0, '', 1, 0, 'Freeware', 'Windows Search, Copernic Desktop Search', 'https://www.voidtools.com/', 0, 'Latest', 'Windows', 0.5, 1.0, 'Windows 7', 'voidtools.Everything'),
    ('PeaZip', 'peazip', @utility_category_id, 'Pengelola arsip open-source untuk membuka, membuat, mengenkripsi, dan mengekstrak banyak format file kompresi.', 'Giorgio Tani', 'https://peazip.github.io/favicon.ico', 4.6, 0, '', 1, 0, 'LGPL v3', 'WinRAR, WinZip, 7-Zip', 'https://peazip.github.io/', 1, 'Latest', 'Windows, Linux', 1.0, 2.0, 'Windows 7 64-bit', 'Giorgiotani.Peazip'),
    ('ShareX', 'sharex', @utility_category_id, 'Alat tangkapan layar dan perekaman layar open-source dengan workflow anotasi, OCR, upload, serta otomatisasi yang lengkap.', 'ShareX Team', 'https://getsharex.com/img/ShareX_Logo.png', 4.8, 0, '', 1, 0, 'GPL v3', 'Snagit, Greenshot Pro, Loom', 'https://getsharex.com/', 1, 'Latest', 'Windows', 1.0, 2.0, 'Windows 10 64-bit', 'ShareX.ShareX'),
    ('BleachBit', 'bleachbit', @utility_category_id, 'Utilitas pembersihan open-source untuk menghapus cache, berkas sementara, dan data aplikasi secara transparan.', 'BleachBit', 'https://www.bleachbit.org/sites/default/files/bleachbit.svg', 4.4, 0, '', 1, 0, 'GPL v3', 'CCleaner, CleanMyPC', 'https://www.bleachbit.org/', 1, 'Latest', 'Windows, Linux', 1.0, 2.0, 'Windows 10 64-bit', 'BleachBit.BleachBit')
ON DUPLICATE KEY UPDATE
    alternative_to = VALUES(alternative_to),
    min_ram = VALUES(min_ram),
    recommended_ram = VALUES(recommended_ram),
    min_os = VALUES(min_os),
    winget_id = VALUES(winget_id),
    updated_at = CURRENT_TIMESTAMP;
