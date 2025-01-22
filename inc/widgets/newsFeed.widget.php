<?php
// Define Custom HTML Widgets
class NewsFeed implements WidgetInterface {
    private $phpef;
    private $widgetConfig;
    private $db;

    public function __construct($phpef) {
        $this->phpef = $phpef;
        $this->buildWidgetConfig();
        $this->db = $this->phpef->db;
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                date TEXT NOT NULL,
                content TEXT NOT NULL
            )
        ");
    }

    private function buildWidgetConfig() {
        $this->widgetConfig = $this->phpef->config->get('Widgets','News Feed');
        $this->widgetConfig['enabled'] = $this->widgetConfig['enabled'] ?? false;
        $this->widgetConfig['auth'] = $this->widgetConfig['auth'] ?? null;
        $this->widgetConfig['newsExpandFirst'] = $this->widgetConfig['newsExpandFirst'] ?? true;
        $this->widgetConfig['newsHeaderEnabled'] = $this->widgetConfig['newsHeaderEnabled'] ?? true;
        $this->widgetConfig['newsHeader'] = $this->widgetConfig['newsHeader'] ?? 'News & Updates';
        $this->widgetConfig['newsItemsDisplayed'] = $this->widgetConfig['newsItemsDisplayed'] ?? 5;
    }

    public function settings() {
        $customHTMLQty = 5;
        $SettingsArr = [];
        $SettingsArr['info'] = [
            'name' => 'News Feed',
            'description' => 'News Feed Widget',
			'image' => ''
        ];
        $SettingsArr['Settings'] = [
            'Widget Settings' => [
				$this->phpef->settingsOption('enable', 'enabled'),
				$this->phpef->settingsOption('auth', 'auth', ['label' => 'Role Required']),
                $this->phpef->settingsOption('checkbox', 'newsHeaderEnabled', ['label' => 'Toggle the news header displayed above the news widget', 'attr' => 'checked']),
                $this->phpef->settingsOption('checkbox', 'newsExpandFirst', ['label' => 'Expand the latest news item', 'attr' => 'checked']),
                $this->phpef->settingsOption('input', 'newsHeader', ['label' => 'The header displayed above the news widget', 'placeholder' => 'News & Updates']),
                $this->phpef->settingsOption('number', 'newsItemsDisplayed', ['label' => 'Number of news items displayed in the widget']),
            ]
        ];
        return $SettingsArr;
    }

    public function render() {
        if ($this->phpef->auth->checkAccess($this->widgetConfig['auth']) !== false && $this->widgetConfig['enabled']) {
            // Fetch news items from the SQLite database
            $newsItems = $this->fetchNewsItems($this->widgetConfig['newsItemsDisplayed']);

            $output = '';
            if ($this->widgetConfig['newsHeaderEnabled']) {
                $NewsHeader = $this->widgetConfig['newsHeader'];
                $output = <<<EOF
                <div class="col-md-12 homepage-item-collapse" data-bs-toggle="collapse" href="#news-collapse" data-bs-parent="#news" aria-expanded="true" aria-controls="news-collapse">
                    <h4 class="float-left homepage-item-title"><span lang="en">$NewsHeader</span></h4>
                    <h4 class="float-left">&nbsp;</h4>
                    <hr class="hr-alt ml-2">
                </div>
                <div class="panel-collapse collapse show" id="news-collapse" aria-labelledby="news-heading" role="tabpanel" aria-expanded="true" style="">
                EOF;
            }

            $output .= '<div class="accordion" id="newsAccordion">';
            $count = 0;
            foreach ($newsItems as $index => $newsItem) {
                $expand = ($count == 0 && $this->widgetConfig['newsExpandFirst']);
                $btnExpand = $expand ? "" : "collapsed";
                $bodyExpand = $expand ? "" : "collapse";
                $output .= <<<EOF
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{$index}">
                        <button class="accordion-button {$btnExpand}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{$index}" aria-expanded="true" aria-controls="collapse{$index}">
                            <span>{$newsItem['title']}</span>
                            <span class="alt-text">{$newsItem['date']}</span>
                        </button>
                    </h2>
                    <div id="collapse{$index}" class="accordion-collapse {$bodyExpand}" aria-labelledby="heading{$index}" data-bs-parent="#newsAccordion">
                        <div class="accordion-body">
                            {$newsItem['content']}
                        </div>
                    </div>
                </div>
                EOF;
                $count++;
            }
            $output .= '</div>';
            if ($this->widgetConfig['newsHeaderEnabled']) {
                $output .= '</div>';
            }

            return $output;
        }
    }

    private function fetchNewsItems($limit) {
        $stmt = $this->db->prepare("SELECT title, date, content FROM news ORDER BY date DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Register Custom HTML Widgets
$phpef->dashboard->registerWidget('News Feed', new NewsFeed($phpef));