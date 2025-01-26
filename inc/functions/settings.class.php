<?php
trait Settings {

    public function settingsCustomisation() {
        return array(
            'Top Bar' => array(
                $this->settingsOption('input', 'Styling[websiteTitle]', ['label' => 'Website Title']),
                $this->settingsOption('input', 'Styling[websiteTitleFontSize]', ['label' => 'Font size when using title for logo', 'value' => '42px', 'width' => '3']),
                $this->settingsOption('checkbox', 'Styling[websiteTitleNotLogo]', ['label' => 'Use website title instead of logo', 'width' => '3']),
                $this->settingsOption('input', 'Styling[logo-sm][Image]', ['label' => 'Logo Image (Small)']),
                $this->settingsOption('input', 'Styling[logo-sm][CSS]', ['label' => 'Logo CSS (Small)']),
                $this->settingsOption('input', 'Styling[logo-lg][Image]', ['label' => 'Logo Image (Large)']),
                $this->settingsOption('input', 'Styling[logo-lg][CSS]', ['label' => 'Logo Image (CSS)'])
            ),
            'Side Bar' => array(
                $this->settingsOption('checkbox', 'Styling[sidebar][expandOnHover]', ['label' => 'Expand sidebar on hover']),
                $this->settingsOption('checkbox', 'Styling[sidebar][collapseByDefault]', ['label' => 'Collapse sidebar by default'])
            ),
            'Favicon' => array(
                $this->settingsOption('input', 'Styling[favicon][Image]', ['label' => 'Favicon'])
            ),
            'Homepage' => array(
				$this->settingsOption('code-editor', 'Styling[html][homepage]', ['label' => 'Homepage HTML', 'mode' => 'html', 'value' => $this->config->get('Styling', 'html')['homepage']]),
                $this->settingsOption('code-editor', 'Styling[html][about]', ['label' => 'About HTML', 'mode' => 'html', 'value' => $this->config->get('Styling', 'html')['about']])
            ),
            'Custom CSS' => array(
				$this->settingsOption('code-editor', 'Styling[css][custom]', ['label' => 'Custom CSS', 'mode' => 'css', 'value' => $this->config->get('Styling', 'css')['custom']]),
            )
	    );
    }

    public function settingsGeneral() {
        $AuthSettings = array(
            "LDAP Configuration" => array(
                $this->settingsOption('title', 'ldapGeneralSettings', ['text' => 'General']),
                $this->settingsOption('checkbox', 'LDAP[enabled]', ['label' => 'Enable LDAP']),
                $this->settingsOption('checkbox', 'LDAP[AutoCreateUsers]', ['label' => 'Auto-Create Users']),
                $this->settingsOption('hr'),
                $this->settingsOption('title', 'ldapConnectionSettings', ['text' => 'LDAP Connection']),
                $this->settingsOption('input', 'LDAP[ldap_server]', ['label' => 'LDAP Server', 'placeholder' => 'ldap://fqdn:389']),
                $this->settingsOption('input', 'LDAP[service_dn]', ['label' => 'LDAP Bind Username', 'placeholder' => 'cn=read-only-admin,dc=example,dc=com']),
                $this->settingsOption('password', 'LDAP[service_password]', ['label' => 'LDAP Bind Password', 'placeholder' => '*********']),
                $this->settingsOption('input', 'LDAP[user_dn]', ['label' => 'User DN', 'placeholder' => 'dc=example,dc=com']),
                $this->settingsOption('input', 'LDAP[base_dn]', ['label' => 'Base DN', 'placeholder' => 'dc=example,dc=com']),
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

        $cronJobTableAttributes = [
            'url' => '/api/cron/jobs',
            'data-field' => 'data',
            'toggle' => 'table',
            'sort-name' => 'last_run',
            'sort-order' => 'asc',
            'response-handler' => 'responseHandler',
        ];
    
        $cronJobTableColumns = [
            [
                'field' => 'source',
                'title' => 'Source'
            ],
            [
                'field' => 'name',
                'title' => 'Name'
            ],
            [
                'field' => 'status',
                'title' => 'Status'
            ],
            [
                'field' => 'message',
                'title' => 'Message'
            ],
            [
                'field' => 'last_run',
                'title' => 'Last Ran'
            ]
        ];

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
                $this->settingsOption('accordion', 'AuthProviders', ['id' => 'AuthProviders', 'label' => 'Authentication Providers', 'options' => $AuthSettings, 'width' => '12'])
            ),
            'Security' => array(
                $this->settingsOption('password-alt', 'Security[salt]', ['label' => 'Salt']),
                $this->settingsOption('input', 'Security[Headers][X-Frame-Options]', ['label' => 'X-Frame-Options', 'placeholder' => 'SAMEORIGIN']),
                $this->settingsOption('input', 'Security[Headers][CSP][Frame-Source]', ['label' => 'Content Security Policy: Frame Source', 'placeholder' => 'self']),
                $this->settingsOption('input', 'Security[Headers][CSP][Connect-Source]', ['label' => 'Content Security Policy: Connect Source', 'placeholder' => 'self']),
            ),
            'Cron' => array(
                $this->settingsOption('bootstrap-table', 'cronJobTable', ['id' => 'cronJobTable', 'columns' => $cronJobTableColumns, 'dataAttributes' => $cronJobTableAttributes, 'width' => '12']),
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
            'buttons' => 'pluginsTableButtons',
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
                'field' => 'requires',
                'title' => 'Dependencies',
                'dataAttributes' => ['sortable' => 'false', 'visible' => 'false', 'formatter' => 'pluginRequirementsFormatter'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'pluginActionEvents', 'formatter' => 'pluginActionFormatter'],
            ]
        ];

        return array(
            'Manage' => array(
                $this->settingsOption('bootstrap-table', 'PluginTable', ['id' => 'pluginsTable', 'columns' => $PluginsTableColumns, 'dataAttributes' => $PluginsTableAttributes, 'width' => '12']),
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
                'dataAttributes' => ['events' => 'dashboardActionEvents', 'formatter' => 'editAndDeleteActionFormatter'],
            ]
        ];

        $DashboardsTableAttributes = $TableAttributes;
        $DashboardsTableAttributes['url'] = '/api/dashboards';
        $DashboardsTableAttributes['buttons'] = 'dashboardsTableButtons';

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
            'Tabs' => array(
                $this->settingsOption('bootstrap-table', 'dashboardsTable', ['id' => 'dashboardsTable', 'columns' => $DashboardsTableColumns, 'dataAttributes' => $DashboardsTableAttributes, 'width' => '12']),
            ),
            'Widgets' => array(
                $this->settingsOption('bootstrap-table', 'widgetsTable', ['id' => 'widgetsTable', 'columns' => $WidgetTableColumns, 'dataAttributes' => $WidgetTableAttributes, 'width' => '12']),
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
                $this->settingsOption('selectwithtable', 'Widgets', ['label' => 'Enabled Widgets', 'options' => $WidgetList, 'class' => 'widgetSelect select-multiple', 'width' => '12', 'id' => 'widgetSelect'])
            )
	    );
    }

    public function settingsAccessControl() {
        $TableAttributes = [
            'data-field' => 'data',
            'toggle' => 'table',
            'search' => 'true',
            'pagination' => 'true',
            'filter-control' => 'true',
            'filter-control-visible' => 'false',
            'show-filter-control-switch' => 'true',
            'show-refresh' => 'true',
            'pagination' => 'true',
            'toolbar' => '#toolbar',
            'sortable' => 'true',
            'sort-name' => 'Name',
            'sort-order' => 'asc',
            'show-columns' => 'true',
            'show-export' => 'true',
            'page-size' => '25',
            'response-handler' => 'responseHandler',
        ];

        $UsersTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'id',
                'title' => 'ID',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'username',
                'title' => 'Username',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'firstname',
                'title' => 'First Name',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'surname',
                'title' => 'Surname',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'email',
                'title' => 'Email',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'groups',
                'title' => 'Group(s)',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input', 'formatter' => 'groupsFormatter'],
            ],
            [
                'field' => 'type',
                'title' => 'Type',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'select'],
            ],
            [
                'field' => 'lastlogin',
                'title' => 'Last Login Date',
                'dataAttributes' => ['sortable' => 'false', 'filter-control' => 'input', 'formatter' => 'datetimeFormatter'],
            ],
            [
                'field' => 'created',
                'title' => 'Creation Date',
                'dataAttributes' => ['sortable' => 'false', 'filter-control' => 'input', 'visible' => 'false', 'formatter' => 'datetimeFormatter'],
            ],
            [
                'field' => 'passwordexpires',
                'title' => 'Password Expiry Date',
                'dataAttributes' => ['sortable' => 'false', 'filter-control' => 'input', 'visible' => 'false', 'formatter' => 'datetimeFormatter'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'userActionEvents', 'formatter' => 'editAndDeleteActionFormatter'],
            ]
        ];

        $UsersTableAttributes = $TableAttributes;
        $UsersTableAttributes['url'] = '/api/users';
        $UsersTableAttributes['buttons'] = 'usersTableButtons';
        $UsersTableAttributes['buttons-order'] = 'btnAddUser,btnBulkDelete,refresh,columns,export,filterControlSwitch';

        $GroupsTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'id',
                'title' => 'ID',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'Name',
                'title' => 'Group Name',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'Description',
                'title' => 'Group Description',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'groupsActionEvents', 'formatter' => 'groupActionFormatter'],
            ]
        ];

        $GroupsTableAttributes = $TableAttributes;
        $GroupsTableAttributes['url'] = '/api/rbac/groups';
        $GroupsTableAttributes['buttons'] = 'groupsTableButtons';
        $GroupsTableAttributes['buttons-order'] = 'btnAddGroup,refresh,columns,export,filterControlSwitch';

        $RolesTableColumns = [
            [
                'field' => 'state',
                'title' => 'State',
                'dataAttributes' => ['checkbox' => 'true'],
            ],
            [
                'field' => 'name',
                'title' => 'Role Name',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'field' => 'description',
                'title' => 'Role Description',
                'dataAttributes' => ['sortable' => 'true', 'filter-control' => 'input'],
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'rolesActionEvents', 'formatter' => 'roleActionFormatter'],
            ]
        ];

        $RolesTableAttributes = $TableAttributes;
        $RolesTableAttributes['url'] = '/api/rbac/roles';
        $RolesTableAttributes['buttons'] = 'rolesTableButtons';
        $RolesTableAttributes['buttons-order'] = 'btnAddRole,refresh,columns,export,filterControlSwitch';


        return array(
            'Users' => array(
                $this->settingsOption('bootstrap-table', 'usersTable', ['id' => 'usersTable', 'columns' => $UsersTableColumns, 'dataAttributes' => $UsersTableAttributes, 'width' => '12']),
            ),
            'Groups' => array(
                $this->settingsOption('bootstrap-table', 'groupsTable', ['id' => 'groupsTable', 'columns' => $GroupsTableColumns, 'dataAttributes' => $GroupsTableAttributes, 'width' => '12']),
            ),
            'Roles' => array(
                $this->settingsOption('bootstrap-table', 'rolesTable', ['id' => 'rolesTable', 'columns' => $RolesTableColumns, 'dataAttributes' => $RolesTableAttributes, 'width' => '12']),
            ),
	    );
    }

    public function settingsUser() {
        $Groups = $this->auth->getRBACGroups(false,true);
        $GroupItems = array_map(function($item) {
            return [
                "id" => $item['id'],
                "title" => $item['Name'],
                "description" => $item['Description'],
                "checkbox" => "true"
            ];
        }, $Groups);


        $PasswordSettings = array(
            "Reset Password" => array(
                $this->settingsOption('password-alt', 'userPassword', ['label' => 'Password']),
                $this->settingsOption('password-alt', 'userPassword2', ['label' => 'Confirm Password'])
            )
        );

        $MFASettings = array(
            "Multi Factor Authentication" => array(
                $this->settingsOption('html', 'mfaUserSettings', ['html' => '<div id="mfaUserSettings"></div>', 'width' => '12'])
            )
        );

        return array(
            'General' => array(
                $this->settingsOption('input', 'userUsername', ['label' => 'Username']),
                $this->settingsOption('input', 'userFirstName', ['label' => 'First Name']),
                $this->settingsOption('input', 'userLastName', ['label' => 'Surname']),
                $this->settingsOption('input', 'userEmail', ['label' => 'Email']),
                $this->settingsOption('hr'),
                $this->settingsOption('accordion', 'PasswordReset', ['id' => 'PasswordReset', 'options' => $PasswordSettings, 'width' => '12']),
                $this->settingsOption('hr'),
                $this->settingsOption('accordion', 'MFASettings', ['id' => 'MFASettings', 'options' => $MFASettings, 'width' => '12']),
                $this->settingsOption('hr'),
                $this->settingsOption('input', 'userType', ['label' => 'Type', 'attr' => 'disabled readonly']),
                $this->settingsOption('input', 'userLastLogin', ['label' => 'Last Login', 'attr' => 'disabled readonly']),
                $this->settingsOption('input', 'userPasswordExpires', ['label' => 'Password Expires', 'attr' => 'disabled readonly']),
                $this->settingsOption('input', 'userCreated', ['label' => 'User Created', 'attr' => 'disabled readonly']),
                $this->settingsOption('input', 'userId', ['attr' => 'hidden'])
            ),
            'Groups' => array(
                $this->settingsOption('listgroup', 'groupList', ['items' => $GroupItems, 'width' => '12'])
            )
        );
    }

    public function settingsNewUser() {
        $PasswordSettings = array(
            "Reset Password" => array(
                $this->settingsOption('password-alt', 'userPassword', ['label' => 'Password']),
                $this->settingsOption('password-alt', 'userPassword2', ['label' => 'Confirm Password'])
            )
        );

        return array(
            'General' => array(
                $this->settingsOption('input', 'userUsername', ['label' => 'Username']),
                $this->settingsOption('input', 'userFirstName', ['label' => 'First Name']),
                $this->settingsOption('input', 'userLastName', ['label' => 'Surname']),
                $this->settingsOption('input', 'userEmail', ['label' => 'Email']),
                $this->settingsOption('password-alt', 'userPassword', ['label' => 'Password']),
                $this->settingsOption('password-alt', 'userPassword2', ['label' => 'Confirm Password']),
                $this->settingsOption('checkbox', 'expire', ['label' => 'Require Password Reset At First Login'])
            )
        );
    }

    public function settingsGroup() {
        $Roles = $this->auth->getRBACRoles();
        $RoleItems = array_map(function($item) {
            return [
                "id" => $item['id'],
                "title" => $item['name'],
                "description" => $item['description'],
                "checkbox" => "true"
            ];
        }, $Roles);

        return array(
            "General" => array(
                $this->settingsOption('input', 'groupName', ['label' => 'Group Name', 'width' => '4']),
                $this->settingsOption('input', 'groupDescription', ['label' => 'Group Description', 'width' => '8']),
                $this->settingsOption('hr'),
                $this->settingsOption('html', 'groupRolesSelectTitle', ['html' => '<h4>Group Roles</h4><p>Enable or Disable the following roles to provide granular control to specific areas of PHP Extensible Framework.</p>', 'width' => '12']),
                $this->settingsOption('listgroup', 'roleList', ['items' => $RoleItems, 'width' => '12']),
                $this->settingsOption('input', 'groupId', ['attr' => 'hidden'])
            )
        );
    }

    public function settingsRole() {
        return array(
            "General" => array(
                $this->settingsOption('input', 'roleName', ['label' => 'Role Name', 'width' => '12']),
                $this->settingsOption('input', 'roleDescription', ['label' => 'Role Description', 'width' => '12']),
                $this->settingsOption('input', 'roleId', ['attr' => 'hidden'])
            )
        );
    }

    public function settingsPages() {
        $TableAttributes = [
            'data-field' => 'data',
            'toggle' => 'table',
            'search' => 'true',
            'filter-control' => 'true',
            'show-refresh' => 'true',
            'pagination' => 'true',
            'toolbar' => '#toolbar',
            'show-columns' => 'true',
            'page-size' => '25',
            'response-handler' => 'responseHandler',
        ];

        $CombinedTableColumns = [
            [
                'field' => 'dragHandle',
                'dataAttributes' => ['width' => '25px']
            ],
            [
                'field' => 'Icon',
                'title' => 'Icon',
                'dataAttributes' => ['formatter' => 'pageIconFormatter']
            ],
            [
                'field' => 'Name',
                'title' => 'Name'
            ],
            [
                'field' => 'Title',
                'title' => 'Title'
            ],
            [
                'field' => 'Url',
                'title' => 'URL',
                'dataAttributes' => ['visible' => 'false'],
            ],
            [
                'field' => 'ACL',
                'title' => 'Role'
            ],
            [
                'field' => 'LinkType',
                'title' => 'Type'
            ],
            [
                'field' => 'isDefault',
                'title' => 'Default',
                'dataAttributes' => ['width' => '25px', 'formatter' => 'booleanTickCrossFormatter']
            ],
            [
                'title' => 'Actions',
                'dataAttributes' => ['events' => 'pageActionEvents', 'formatter' => 'pageActionFormatter'],
            ]
        ];

        $CombinedTableAttributes = $TableAttributes;
        $CombinedTableAttributes['url'] = '/api/pages/root';
        $CombinedTableAttributes['buttons'] = 'pagesTableButtons';
        $CombinedTableAttributes['buttons-order'] = 'btnAddPage';
        $CombinedTableAttributes['detail-formatter'] = 'menuDetailFormatter';
        $CombinedTableAttributes['detail-view'] = 'true';
        $CombinedTableAttributes['reorderable-rows'] = 'true';
        $CombinedTableAttributes['row-attributes'] = 'pagesRowAttributes';
        $CombinedTableAttributes['row-style'] = 'pagesRowStyle';
        $CombinedTableAttributes['drag-handle'] = '>tbody>tr>td:nth-child(2)';
        $CombinedTableAttributes['response-handler'] = 'dragHandlerResponseHandler';

        $CombinedTableEvents = [
            'onExpandRow' => 'pagesInitializeMenuTable',
            'onReorderRow' => 'pagesRowOnReorderRow'
        ];

        return array(
            'Manage' => array(
                $this->settingsOption('bootstrap-table', 'combinedTable', ['id' => 'combinedTable', 'columns' => $CombinedTableColumns, 'dataAttributes' => $CombinedTableAttributes, 'events' => $CombinedTableEvents, 'width' => '12']),
            )
	    );
    }

    public function settingsPage() {
        $AppendNone = array(
            [
                "name" => 'None',
                "value" => ''
            ]
        );

        $AvailablePagesSelect = array_merge($AppendNone,array_map(function($item) {
            $Prefix = $item['plugin'] ? 'Plugin: ' : '';
            $PageName = $Prefix ? $Prefix . $item['plugin'] . ' / ' . $item['filename'] : $item['directory'] . ' / ' . $item['filename'];
            $PageValue = $Prefix ? 'plugin/' . $item['directory'] . '/' . $item['filename'] : $item['directory'] . '/' . $item['filename'];
            return [
                "name" => $PageName,
                "value" => $PageValue
            ];
        }, $this->pages->getAllAvailablePages()));

        $AvailableMenusSelect = array_merge($AppendNone,array_map(function($item) {
            return [
                "name" => $item['Name'],
                "value" => $item['Name']
            ];
        }, $this->pages->getByType('Menu')));
        
        return array(
            "General" => array(
                $this->settingsOption('select', 'pageType', ['label' => 'Type', 'options' => array(array("name" => 'Link', "value" => 'Link'),array("name" => 'Menu', "value" => 'Menu'))]),
                $this->settingsOption('select', 'pageLinkType', ['label' => 'Link Type', 'options' => array(array("name" => 'Native', "value" => 'Native'),array("name" => 'iFrame', "value" => 'iFrame'),array("name" => 'New Window', "value" => 'NewWindow')), 'noRow' => 'true']),
                $this->settingsOption('input', 'pageName', ['label' => 'Name', 'noRow' => 'true']),
                $this->settingsOption('input', 'pageTitle', ['label' => 'Title', 'noRow' => 'true']),
                $this->settingsOption('select', 'pageStub', ['label' => 'Page', 'options' => $AvailablePagesSelect, 'noRow' => 'true']),
                $this->settingsOption('input', 'pageUrl', ['label' => 'URL', 'noRow' => 'true']),
                $this->settingsOption('auth', 'pageRole', ['label' => 'Role', 'noRow' => 'true']),
                $this->settingsOption('select', 'pageMenu', ['label' => 'Menu', 'noRow' => 'true', 'options' => $AvailableMenusSelect]),
                $this->settingsOption('select', 'pageSubMenu', ['label' => 'Sub Menu', 'noRow' => 'true', 'options' => $AppendNone]),
                $this->settingsOption('hr'),
                $this->settingsOption('input', 'pageIcon', ['label' => 'Icon']),
                $this->settingsOption('select', 'pageImage', ['label' => 'Image', 'attr' => '', 'options' => $this->getAllImagesForSelect()]),
                $this->settingsOption('checkbox', 'pageDefault', ['label' => 'Default Page']),
                $this->settingsOption('input', 'pageId', ['attr' => 'hidden'])
            )
        );
    }

    public function settingsNotifications() {
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

        $NewsTableColumns = [
            [
                'field' => 'title',
                'title' => 'Title'
            ],
            [
                'field' => 'content',
                'title' => 'Content',
                'dataAttributes' => ['formatter' => 'readMoreFormatter']
            ],
            [
                'field' => 'created',
                'title' => 'Created',
                'dataAttributes' => ['formatter' => 'datetimeFormatter', 'width' => '220px']
            ],
            [
                'field' => 'updated',
                'title' => 'Updated',
                'dataAttributes' => ['formatter' => 'datetimeFormatter', 'width' => '220px', 'visible' => 'false']
            ],
            [
                'field' => 'actions',
                'title' => 'Actions',
                'dataAttributes' => ['formatter' => 'editAndDeleteActionFormatter', 'events' => 'newsActionEvents']
            ]
        ];

        $NewsTableAttributes = $TableAttributes;
        $NewsTableAttributes['url'] = '/api/notifications/news';
        $NewsTableAttributes['buttons'] = 'newsTableButtons';
        $NewsTableAttributes['buttons-order'] = 'btnAddNews';

        return array(
            'News' => array(
                $this->settingsOption('bootstrap-table', 'newsTable', ['id' => 'newsTable', 'columns' => $NewsTableColumns, 'dataAttributes' => $NewsTableAttributes, 'width' => '12']),
            ),
            'SMTP' => array(

            ),
            'Webhooks' => array(

            )
	    );
    }

    public function settingsNews($id = null) {
        $newsItem = [
            'title' => '',
            'content' => '',
            'id' => '',
            'created' => '',
            'updated' => ''
        ];
        if ($id) {
            $newsItem = $this->notifications->getNewsById($id) ?? '';
        }
        return array(
            'General' => array(
                $this->settingsOption('input', 'newsTitle', ['label' => 'News Item Title', 'value' => $newsItem['title']]),
                $this->settingsOption('hr'),
                $this->settingsOption('codeeditor', 'newsContent', ['label' => 'News Content', 'mode' => 'html', 'value' => $newsItem['content']]),
                $this->settingsOption('input', 'newsId', ['attr' => 'hidden', 'value' => $newsItem['id']])
            )
	    );
    }
}