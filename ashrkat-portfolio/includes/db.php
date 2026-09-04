<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if (DB_DRIVER === 'mysql') {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } else {
        $dir = dirname(SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . SQLITE_PATH, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    install_schema($pdo);
    seed_defaults($pdo);

    return $pdo;
}

function install_schema(PDO $pdo): void
{
    $isSqlite = DB_DRIVER !== 'mysql';
    $pk = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $ts = $isSqlite ? "TEXT DEFAULT (datetime('now'))" : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

    $statements = [
        "CREATE TABLE IF NOT EXISTS admins (
            id $pk,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at $ts
        )",
        "CREATE TABLE IF NOT EXISTS login_attempts (
            id $pk,
            ip VARCHAR(64) NOT NULL,
            created_at $ts
        )",
        "CREATE TABLE IF NOT EXISTS settings (
            id $pk,
            site_name VARCHAR(150) NOT NULL DEFAULT 'أشرقت كريم',
            role_title VARCHAR(150) NOT NULL DEFAULT 'مصممة جرافيك | استراتيجية تسويق رقمي',
            tagline VARCHAR(255) NOT NULL DEFAULT 'حيث يلتقي الإبداع البصري بالنتائج التسويقية',
            about_kicker VARCHAR(100) NOT NULL DEFAULT 'الملف الشخصي',
            about_text TEXT,
            vision_kicker VARCHAR(100) NOT NULL DEFAULT 'رؤيتي في العمل',
            vision_text TEXT,
            cta_heading VARCHAR(150) NOT NULL DEFAULT 'لنبدأ التعاون',
            cta_text TEXT,
            email VARCHAR(150) NOT NULL DEFAULT 'ashrkatkareem@gmail.com',
            phone VARCHAR(50) NOT NULL DEFAULT '0543375687',
            footer_note VARCHAR(255) NOT NULL DEFAULT 'أشرقت كريم — مصممة جرافيك | استراتيجية تسويق رقمي',
            color_dark VARCHAR(20) NOT NULL DEFAULT '#15141d',
            color_light VARCHAR(20) NOT NULL DEFAULT '#faf7f2',
            color_gold VARCHAR(20) NOT NULL DEFAULT '#d4a72c',
            color_emerald VARCHAR(20) NOT NULL DEFAULT '#14b88a',
            color_ink VARCHAR(20) NOT NULL DEFAULT '#241f2e'
        )",
        "CREATE TABLE IF NOT EXISTS skill_categories (
            id $pk,
            title VARCHAR(150) NOT NULL,
            icon_key VARCHAR(40) NOT NULL DEFAULT 'megaphone',
            sort_order INT NOT NULL DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS skill_items (
            id $pk,
            category_id INT NOT NULL,
            body VARCHAR(255) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            FOREIGN KEY (category_id) REFERENCES skill_categories(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS packages (
            id $pk,
            name VARCHAR(150) NOT NULL,
            subtitle VARCHAR(150),
            audience VARCHAR(255),
            features TEXT,
            edge_text TEXT,
            is_highlight INT NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS case_studies (
            id $pk,
            title VARCHAR(150) NOT NULL,
            challenge TEXT,
            solution TEXT,
            result TEXT,
            image_path VARCHAR(255),
            sort_order INT NOT NULL DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS gallery (
            id $pk,
            image_path VARCHAR(255) NOT NULL,
            caption VARCHAR(255),
            sort_order INT NOT NULL DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS testimonials (
            id $pk,
            quote TEXT NOT NULL,
            author VARCHAR(150),
            rating INT NOT NULL DEFAULT 5,
            sort_order INT NOT NULL DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS messages (
            id $pk,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50),
            body TEXT NOT NULL,
            is_read INT NOT NULL DEFAULT 0,
            created_at $ts
        )",
    ];

    foreach ($statements as $sql) {
        $pdo->exec($sql);
    }
}

function seed_defaults(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM settings')->fetch()['c'];
    if ($count === 0) {
        $pdo->exec("INSERT INTO settings (id) VALUES (1)");
    }

    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM skill_categories')->fetch()['c'];
    if ($count === 0) {
        $cats = [
            ['مهارات التسويق', 'megaphone', [
                'بناء الاستراتيجيات التسويقية للسوشيال ميديا',
                'إدارة الحسابات وجدولة المحتوى',
                'كتابة محتوى إعلاني وتسويقي مؤثر',
                'تحليل الأداء وإعداد التقارير الدورية',
                'التخطيط للحملات الإعلانية الموسمية',
            ]],
            ['التصميم والتنفيذ', 'brush', [
                'بناء الهوية البصرية الكاملة',
                'تصميم منشورات السوشيال ميديا',
                'تصميم القوائم والمطبوعات',
                'تصميم بنرات وإعلانات رقمية',
                'إنتاج محتوى فيديو قصير',
            ]],
            ['الأدوات', 'tools', [
                'Adobe Photoshop', 'Adobe Illustrator', 'Canva Pro',
                'Adobe Premiere / Capcut', 'Meta Business Suite',
                'أدوات جدولة المحتوى وتحليل البيانات',
            ]],
        ];
        $catStmt = $pdo->prepare('INSERT INTO skill_categories (title, icon_key, sort_order) VALUES (?, ?, ?)');
        $itemStmt = $pdo->prepare('INSERT INTO skill_items (category_id, body, sort_order) VALUES (?, ?, ?)');
        foreach ($cats as $i => $cat) {
            $catStmt->execute([$cat[0], $cat[1], $i]);
            $catId = (int) $pdo->lastInsertId();
            foreach ($cat[2] as $j => $item) {
                $itemStmt->execute([$catId, $item, $j]);
            }
        }
    }

    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM packages')->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO packages (name, subtitle, audience, features, edge_text, is_highlight, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(['بداية قوية', 'STARTER PRESENCE', 'أصحاب المشاريع الناشئة، الكافيهات الصغيرة، والمتاجر الإلكترونية الجديدة',
            "8 تصاميم شهريًا (منشورات وستوريهات)\nكتابة محتوى إبداعي لكل تصميم\nجدولة المنشورات على المنصات\nتقرير أداء شهري مختصر",
            'انطلاقة احترافية بهوية واضحة تبني حضورك الرقمي من أول شهر.', 0, 0]);
        $stmt->execute(['نمو متصاعد', 'GROWTH ENGINE', 'المطاعم والكافيهات والمتاجر الإلكترونية النشطة',
            "16 تصميمًا شهريًا (منشورات وستوريهات وعروض)\nإدارة كاملة للحساب والرد على الاستفسارات\nمحتوى تسويقي مبني على استراتيجية شهرية\nفيديو قصير شهري وتقرير أداء تفصيلي",
            'منظومة متكاملة تجمع التصميم والإدارة والتحليل في يد واحدة.', 0, 1]);
        $stmt->execute(['علامة رائدة', 'SIGNATURE BRAND', 'العلامات الطموحة التي تسعى للريادة والتميز',
            "24+ تصميمًا شهريًا لكل قنوات التواصل\nإدارة شاملة وخطة محتوى متكاملة\n2-3 فيديوهات قصيرة احترافية شهريًا\nإدارة حملات إعلانية ممولة",
            'شراكة استراتيجية متكاملة بأداء يُقاس بأرقام حقيقية، مع جلسة استشارية وتقرير تحليلي شامل شهريًا.', 1, 2]);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM case_studies')->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO case_studies (title, challenge, solution, result, sort_order) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['مطعم وكافيه أندريا',
            'افتقار الحساب لهوية بصرية موحّدة أثّر على مستوى التفاعل، وصعّب على الجمهور التعرف على العلامة بسرعة وسط زحمة المحتوى.',
            'إعادة بناء الهوية البصرية بألوان وخطوط متسقة، وإنتاج محتوى فيديو قصير يبرز الأطباق والأجواء، مع تنظيم إدارة الردود والتعليقات.',
            'حضور بصري متناسق واحترافي، وارتفاع ملحوظ في التفاعل والوصول.', 0]);
        $stmt->execute(['صيدلية مشراف',
            'الحاجة إلى تحديث الصورة الرقمية وتحسين التفاعل مع الجمهور بما يتناسب مع طبيعة القطاع الصحي ويبني ثقة العملاء.',
            'تصميم هوية بصرية أنيقة وواضحة، مع محتوى وفيديوهات توعوية وتعريفية بالمنتجات، وإدارة دقيقة للتفاعل مع الاستفسارات.',
            'مظهر رقمي احترافي وموثوق، وتحسّن واضح في معدلات التفاعل والوصول.', 1]);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM testimonials')->fetch()['c'];
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO testimonials (quote, author, rating, sort_order) VALUES (?, ?, ?, ?)');
        $stmt->execute(['من أكثر الأشياء اللي أعجبتنا في التعامل مع أشرقت هو الالتزام التام بالمواعيد المتفق عليها، كل تصميم يوصلنا في وقته بدون أي تأخير.', 'صاحب مطعم', 5, 0]);
        $stmt->execute(['التصاميم اللي قدمتها أشرقت كانت أعلى من توقعاتنا، احترافية ومتسقة، وتعكس هوية مشروعنا بدقة.', 'صاحبة متجر إلكتروني', 5, 1]);
        $stmt->execute(['بعد التعاون مع أشرقت لاحظنا زيادة حقيقية في عدد الطلبات والاستفسارات، والمحتوى انعكس مباشرة على مبيعاتنا.', 'مالك كافيه', 5, 2]);
        $stmt->execute(['معدل التفاعل على حساباتنا ارتفع بشكل ملحوظ بعد ما بدأنا نشتغل مع أشرقت، وهذا كان له أثر مباشر في زيادة الوعي بعلامتنا.', 'صاحب علامة تجارية ناشئة', 5, 3]);
    }

    $settings = $pdo->query('SELECT about_text, vision_text, cta_text FROM settings WHERE id = 1')->fetch();
    if ($settings && $settings['about_text'] === null) {
        $pdo->prepare('UPDATE settings SET about_text = ?, vision_text = ?, cta_text = ? WHERE id = 1')->execute([
            'أنا أشرقت كريم، مصممة جرافيك ومسؤولة تسويق رقمي أؤمن بأن التصميم الجميل وحده لا يكفي، فالقيمة الحقيقية تكمن في التصميم الذي يبيع، ويترك أثرًا، ويحوّل المتابع إلى عميل. أجمع بين الفكر التسويقي والحس الإبداعي لأقدم لعملائي حلولًا بصرية مدروسة تُترجم إلى نتائج ملموسة: مبيعات أعلى، تفاعل أقوى، وهوية تُحفَر في ذهن الجمهور.',
            'رؤيتي أن أكون الشريك الذي تعتمد عليه العلامات التجارية والمشاريع الصغيرة والمتوسطة لبناء حضور رقمي قوي ومستدام. أساعد أصحاب الأعمال على تحويل أفكارهم إلى هوية بصرية متكاملة، وتحويل حساباتهم إلى منصات جذب حقيقية للعملاء.',
            'إذا كنت تبحث عن شريك يفهم لغة التسويق ولغة التصميم معًا، ويحوّل رؤيتك إلى نتائج حقيقية على أرض الواقع، فأنا هنا لبدء هذا التعاون معك.',
        ]);
    }
}
