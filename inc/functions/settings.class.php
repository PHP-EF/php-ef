<?php
trait Settings {

    public function settingsCustomisation() {
        return array(
            'Top Bar' => array(
                $this->settingsOption('input', 'Styling[logo-sm][Image]', ['label' => 'Logo Image (Small)']),
                $this->settingsOption('input', 'Styling[logo-sm][CSS]', ['label' => 'Logo CSS (Small)']),
                $this->settingsOption('input', 'Styling[logo-lg][Image]', ['label' => 'Logo Image (Large)']),
                $this->settingsOption('input', 'Styling[logo-lg][CSS]', ['label' => 'Logo Image (CSS)']),
            ),
            'Favicon' => array(
                $this->settingsOption('input', 'Styling[favicon][Image]', ['label' => 'Favicon']),
                $this->settingsOption('input', 'Styling[websiteTitle]', ['label' => 'Website Title']),
            ),
            'Homepage' => array(
				$this->settingsOption('code-editor', 'Styling[html][homepage]', ['label' => 'Homepage HTML', 'mode' => 'html']),
                $this->settingsOption('code-editor', 'Styling[html][about]', ['label' => 'About HTML', 'mode' => 'html']),
            )
	    );
    }

    public function settingsGeneral() {
        $AuthSettings = array(
            "LDAP Configuration" => array(
                $this->settingsOption('input', 'LDAP[ldap_server]', ['label' => 'LDAP Server', 'placeholder' => 'ldap://fqdn:389']),
                $this->settingsOption('input', 'LDAP[service_dn]', ['label' => 'LDAP Bind Username', 'placeholder' => 'cn=read-only-admin,dc=example,dc=com']),
                $this->settingsOption('password', 'LDAP[service_password]', ['label' => 'LDAP Bind Password', 'placeholder' => '*********']),
                $this->settingsOption('input', 'LDAP[user_dn]', ['label' => 'User DN', 'placeholder' => 'dc=example,dc=com']),
                $this->settingsOption('input', 'LDAP[base_dn]', ['label' => 'Base DN', 'placeholder' => 'dc=example,dc=com']),
                $this->settingsOption('checkbox', 'LDAP[enabled]', ['label' => 'Enable LDAP']),
                $this->settingsOption('checkbox', 'LDAP[AutoCreateUsers]', ['label' => 'Auto-Create Users']),
                $this->settingsOption('hr'),
                $this->settingsOption('title', 'ldapUserAttributeMapping', ['text' => 'User Attribute Mapping']),
                $this->settingsOption('input', 'LDAP[attributes][Username]', ['label' => 'Username Attribute', 'placeholder' => 'sAMAccountName']),
                $this->settingsOption('input', 'LDAP[attributes][FirstName]', ['label' => 'First Name Attribute', 'placeholder' => 'givenName']),
                $this->settingsOption('input', 'LDAP[attributes][LastName]', ['label' => 'Last Name Attribute', 'placeholder' => 'sn']),
                $this->settingsOption('input', 'LDAP[attributes][Email]', ['label' => 'Email Attribute', 'placeholder' => 'mail']),
                $this->settingsOption('input', 'LDAP[attributes][Groups]', ['label' => 'Groups Attribute', 'placeholder' => 'memberOf']),
                $this->settingsOption('input', 'LDAP[attributes][DN]', ['label' => 'Distinguished Name Attribute', 'placeholder' => 'distinguishedName'])
            ),
            "SAML Configuration" => array(
                $this->settingsOption('title', 'samlGeneralSettings', ['text' => 'General']),
                $this->settingsOption('checkbox', 'SAML[enabled]', ['label' => 'Enable SAML']),
                $this->settingsOption('checkbox', 'SAML[AutoCreateUsers]', ['label' => 'Auto-Create Users']),
                $this->settingsOption('checkbox', 'SAML[strict]', ['label' => 'Use Strict Mode']),
                $this->settingsOption('checkbox', 'SAML[debug]', ['label' => 'Use Debug Mode']),
                $this->settingsOption('hr'),
                $this->settingsOption('title', 'samlSPSettings', ['text' => 'SP Configuration']),
                $this->settingsOption('input', 'SAML[sp][entityId]', ['label' => 'Entity ID']),
                $this->settingsOption('input', 'SAML[sp][assertionConsumerService]', ['label' => 'Assertion Consumer Service URL']),
                $this->settingsOption('input', 'SAML[sp][singleLogoutService][url]', ['label' => 'Single Logout Service URL']),
                $this->settingsOption('blank'),
                $this->settingsOption('textbox', 'SAML[sp][privateKey][url]', ['label' => 'Private Key']),
                $this->settingsOption('textbox', 'SAML[idp][x509cert]', ['label' => 'X.509 Certificate']),
                $this->settingsOption('hr'),
                $this->settingsOption('title', 'samlIdPSettings', ['text' => 'IdP Configuration']),
                $this->settingsOption('input', 'SAML[idp][entityId]', ['label' => 'Entity ID']),
                $this->settingsOption('input', 'SAML[idp][singleSignOnService][url]', ['label' => 'Single Sign-On Service URL']),
                $this->settingsOption('input', 'SAML[idp][singleLogoutService][url]', ['label' => 'Single Logout Service URL']),
                $this->settingsOption('textbox', 'SAML[idp][x509cert]', ['label' => 'X.509 Certificate']),
                $this->settingsOption('hr'),
                $this->settingsOption('title', 'samlUserAttributeMapping', ['text' => 'SAML Attribute Mapping']),
                $this->settingsOption('input', 'SAML[attributes][Username]', ['label' => 'Username Attribute', 'placeholder' => 'sAMAccountName']),
                $this->settingsOption('input', 'SAML[attributes][FirstName]', ['label' => 'First Name Attribute', 'placeholder' => 'givenName']),
                $this->settingsOption('input', 'SAML[attributes][LastName]', ['label' => 'Last Name Attribute', 'placeholder' => 'sn']),
                $this->settingsOption('input', 'SAML[attributes][Email]', ['label' => 'Email Attribute', 'placeholder' => 'mail']),
                $this->settingsOption('input', 'SAML[attributes][Groups]', ['label' => 'Groups Attribute', 'placeholder' => 'memberOf']),
            )
        );

        return array(
            'System' => array(
                $this->settingsOption('input', 'System[logfilename]', ['label' => 'Log File Name']),
                $this->settingsOption('input', 'System[logdirectory]', ['label' => 'Log Directory']),
                $this->settingsOption('select', 'System[loglevel]', ['label' => 'Log Level', 'options' => array(array("name" => 'Debug', "value" => 'Debug'),array("name" => 'Info', "value" => 'Info'),array("name" => 'Warning', "value" => 'Warning'))]),
                $this->settingsOption('input', 'System[logretention]', ['label' => 'Log Retention']),
                $this->settingsOption('input', 'System[CURL-Timeout]', ['label' => 'CURL Timeout']),
                $this->settingsOption('input', 'System[CURL-ConnectTimeout]', ['label' => 'CURL Timeout on Connect'])
            ),
            'Authentication' => array(
                $this->settingsOption('accordion', 'AuthProviders', ['id' => 'AuthProviders', 'label' => 'Authentication Providers', 'options' => $AuthSettings, 'override' => 'col-md-12']),
            ),
            'Security' => array(
                $this->settingsOption('password-alt', 'Security[salt]', ['label' => 'Salt']),
                $this->settingsOption('input', 'Security[Headers][X-Frame-Options]', ['label' => 'X-Frame-Options', 'placeholder' => 'SAMEORIGIN']),
                $this->settingsOption('input', 'Security[Headers][CSP][Frame-Source]', ['label' => 'Content Security Policy: Frame Source', 'placeholder' => 'self']),
                $this->settingsOption('input', 'Security[Headers][CSP][Connect-Source]', ['label' => 'Content Security Policy: Connect Source', 'placeholder' => 'self']),
            )
	    );
    }

    public function settingsPlugins() {
        $PluginsTableAttributes = [
            'url' => '/api/plugins/available',
            'data-field' => 'data',
            'toggle' => 'table',
            'search' => 'true',
            'filter-control' => 'true',
            'show-refresh' => 'true',
            'pagination' => 'true',
            'toolbar' => '#toolbar',
            'sort-name' => 'Name',
            'sort-order' => 'asc',
            'show-columns' => 'true',
            'page-size' => '25',
            'buttons' => 'pluginsButtons',
            'response-handler' => 'responseHandler',
        ];

        $PluginsTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'name',
                'title' => 'Name',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'author',
                'title' => 'Author',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'description',
                'title' => 'Description',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'category',
                'title' => 'Category',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'version',
                'title' => 'Version',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'online_version',
                'title' => 'Online Version',
                'dataAttributes' => ['sortable' => 'true', 'visible' => 'false'],
            ],
            [
                'field' => 'link',
                'title' => 'Link',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'status',
                'title' => 'Status',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'source',
                'title' => 'Source',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'branch',
                'title' => 'Branch',
                'dataAttributes' => ['sortable' => 'true', 'visible' => 'false'],
            ],
            [
                'field' => 'contact',
                'title' => 'Contact',
                'dataAttributes' => ['sortable' => 'true', 'visible' => 'false'],
            ],
            [
                'field' => 'last_updated',
                'title' => 'Last Updated',
                'dataAttributes' => ['sortable' => 'true', 'visible' => 'false'],
            ],
            [
                'field' => 'release_date',
                'title' => 'Release Date',
                'dataAttributes' => ['sortable' => 'true', 'visible' => 'false'],
            ],
            [
                'field' => 'update',
                'title' => 'Update',
                'dataAttributes' => ['sortable' => 'true', 'formatter' => 'pluginUpdatesFormatter'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'pluginActionEvents', 'formatter' => 'pluginActionFormatter'],
            ]
        ];

        return array(
            'Manage' => array(
                $this->settingsOption('bootstrap-table', 'PluginTable', ['id' => 'pluginsTable', 'columns' => $PluginsTableColumns, 'dataAttributes' => $PluginsTableAttributes, 'override' => 'col-md-12']),
            ),
            'Marketplace' => array(
                $this->settingsOption('enable', 'PluginMarketplaceEnabled', ['label' => 'Enable Plugin Marketplace'])
            ),
	    );
    }

    public function settingsDashboards() {
        $TableAttributes = [
            'data-field' => 'data',
            'toggle' => 'table',
            'search' => 'true',
            'filter-control' => 'true',
            'show-refresh' => 'true',
            'pagination' => 'true',
            'toolbar' => '#toolbar',
            'sort-name' => 'Name',
            'sort-order' => 'asc',
            'show-columns' => 'true',
            'page-size' => '25',
            'response-handler' => 'responseHandler',
        ];

        $DashboardsTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'Name',
                'title' => 'Name',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'Description',
                'title' => 'Description',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'dashboardActionEvents', 'formatter' => 'dashboardActionFormatter'],
            ]
        ];

        $DashboardsTableAttributes = $TableAttributes;
        $DashboardsTableAttributes['url'] = '/api/dashboards';
        $DashboardsTableAttributes['buttons'] = 'dashboardButtons';

        $WidgetTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'info.name',
                'title' => 'Name',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'field' => 'info.description',
                'title' => 'Description',
                'dataAttributes' => ['sortable' => 'true'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'widgetActionEvents', 'formatter' => 'widgetActionFormatter'],
            ]
        ];

        $WidgetTableAttributes = $TableAttributes;
        $WidgetTableAttributes['url'] = '/api/dashboards/widgets';
        $WidgetTableAttributes['buttons'] = 'widgetButtons';

        return array(
            'Dashboards' => array(
                $this->settingsOption('bootstrap-table', 'dashboardsTable', ['id' => 'dashboardsTable', 'columns' => $DashboardsTableColumns, 'dataAttributes' => $DashboardsTableAttributes, 'override' => 'col-md-12']),
            ),
            'Widgets' => array(
                $this->settingsOption('bootstrap-table', 'widgetsTable', ['id' => 'widgetsTable', 'columns' => $WidgetTableColumns, 'dataAttributes' => $WidgetTableAttributes, 'override' => 'col-md-12']),
            ),
	    );
    }

    public function settingsDashboard() {
        $AppendNone = array(
            [
                "name" => 'None',
                "value" => ''
            ]
        );
        $WidgetList = array_merge($AppendNone,array_map(function($item) {
            return [
                "name" => $item['info']['name'],
                "value" => $item['info']['name']
            ];
        }, $this->dashboard->getWidgets()));

        return array(
            'Settings' => array(
                $this->settingsOption('input', 'Name', ['label' => 'Dashboard Name']),
                $this->settingsOption('input', 'Description', ['label' => 'Dashboard Description']),
                $this->settingsOption('auth', 'Auth', ['label' => 'Role Required']),
                $this->settingsOption('enable', 'Enabled')
            ),
            'Widgets' => array(
                $this->settingsOption('selectwithtable', 'Widgets', ['label' => 'Enabled Widgets', 'options' => $WidgetList, 'class' => 'widgetSelect select-multiple', 'override' => 'col-md-8', 'id' => 'widgetSelect'])
            )
	    );
    }

}