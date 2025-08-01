## v0.9.0
- Rewrite settings modal to be dynamically generated to ensure flexibility
- Fix bug when saving Role descriptions
- Fix bug with toasts in some instances

## v0.8.9
- Add internal function to query page by Url

## v0.8.8
- Minor refactor to toast function
- Minor bugfixes
- Add SMTP notification support for core & plugins
- Add Pushover notification support for core & plugins
- Add Website URL configuration option

## v0.8.7
- Add support for custom notice on login page

## v0.8.6
- Remove DNS Toolbox from Core Framework

## v0.8.5
- Bug fixes for adding/removing RBAC Roles from Groups

## v0.8.4
- Add support for custom javascript

## v0.8.3
- Refactor way that JWT tokens are created
- Added ability to generate API Tokens
- Both JWT Session & API Tokens can be revoked via User Profile menu
- Highlight active session token
- Move image/logo customisation to image select dropdowns
- Fix page content height on mobile
- Fix plugin sort ordering to alphabetical

## v0.8.2
- Give roles friendly names and replace unique identifier with 'slug'

## v0.8.1
- Add scheduled backups
- Various bugfixes
- Reduce default font size on mobile to 14px
- Move logging options in configuration file
- Log Cleanup schedule is now configurable

## v0.8.0
- Add Cron Job Tracking
- Various additions to customisation settings
- Bugfixes

## v0.7.9
- Add 2FA TOTP support

## v0.7.8
- Add plugin dependency support
- Add News Feed widget
- Bugfix to filtering Web Tracking reports
- Add support for setting default page

## v0.7.7
- Upgrade to Bootstrap 5.3.3
- Introduce support for BS themes
- Fix nested nav tabs on mobile
- Overhaul pages

## v0.7.6
- Major overhaul to UX/UI

## v0.7.5
- Various improvements and bugfixes

## v0.7.4
- Move password inputs to encrypted fields

## v0.7.3
- Various style changes & improvements

## v0.7.2
- Minor updates to prep for iFrame support
- Bugfixes & cleanup
- Fix cron in Docker deployments
- Add initial hooks support

## v0.7.1
- Add plugin management
- Add/Remove Plugin Repository URLs
- Install/Uninstall/Reinstall Plugins from Github
- Add update checking

## v0.7.0
### BREAKING CHANGE
- Add page ordering and support for preference changes by draggable rows
- This requires a new 'Weight' column to be created in the database which is not done automatically.
- You can either create the new Weight column manually, or drop the pages table and allow it to be re-created automatically.
- Database migration scripts are included for any version after this one, to avoid future breaking changes.

## v0.6.9
- Various improvements to plugin configuration
- Include new pages discovery

## v0.6.8
- Add additional style customisation options
- Add LDAP Support
- Improvements to web tracking

## v0.6.7
- Cleanup IB tools into new plugin
- Various API improvements
- Expand plugin support for JS/CSS/Cron
- Move navigation/pages configuration to DB
- Create configuration page for managing navigation links

## v0.6.6
- Finalize move to V2 API

## v0.6.5
- Complete native API conversion from V1 to V2
- Move pages from static content to be generated via API endpoints instead
- Enable initial plugin support
- Move IB pages/functions to new plugin

## v0.6.4
- Initial release of new V2 API

## v0.6.3
- Enable support for managing roles

## v0.6.2
- Overhaul Role Based Access & migrate from JSON to DB
- Remove RBAC requirement for separate Menu permissions
- Move NavBar links to dynamic generation

## v0.6.1
- Enable filter support for web tracking dashboard
- Minor cleanup and data conversion

## v0.6.0
- Add additional logging
- Add additional reporting

## v0.5.9
- Add support for clickable chart filtering within reporting
- Add support for custom time range filtering within reporting
- Swap out Start/End date to a single range date/time picker for assessments

## v0.5.8
- Add realms chart to assessment reporting
- Minor visual bugfixes

## v0.5.7
- Implement temporary workaround to enable Threat Actor generation in EU whilst it is not aligned to US

## v0.5.6
- Various bugfixes

## v0.5.5
- Add password reset functionality at first logon

## v0.5.4
- Add ability to query related IOCs for Threat Actors
- Add new assessment reporting dashboard

## v0.5.3
- Minor style changes, cleanup & bugfixes

## v0.5.2
- Add CNAME & Reverse Lookup to DNS Toolbox

## v0.5.1
- More UI Updates
- Move DNS Toolbox to native class and fix some minor bugs

## v0.5.0
- Threat Actor slides are now automated when using the security assessment generator, with one slide created for each threat actor found.
- Add additional error checking when Infoblox APIs timeout
- Various bugfixes/cleanup
- Add text based status to security assessment generator
- Move generation to be a background task to avoid idle timeouts
- Swapped progress spinner to something more infoblox
- Move large number of functions to new classes
- Implement new authentication library
- Implemented SAML Authentication for SSO
- Implemented new configuration pages for Threat Actors/Security Assessment Templates
- Implemented new configuration pages for Users
- Implement new user profile page
- Overhaul UI

## v0.4.6
- Fix bug where invalid API Keys would not always be reported

## v0.4.5
- Minor url changes
- Fix redirect bug when logging in from root site
- Cleanup whitespace
- Minor styling changes

## v0.4.4
- RBAC changes to include a group for none-authenticated users

## v0.4.3
- Enable export of table data for Threat Actors
- Minor changes to logging
- Update configuration via POST instead of GET
- General cleanup

## v0.4.2
- Minor fixes to session expiry redirect

## v0.4.1
- Add initial admin authentication and framework ready for SAML/OAuth/LDAP

## v0.4
- UI Improvements

## v0.3
- Refactor lots of code, add new Threat Actor page & ability to encrypt/save API keys for temporary re-use

## v0.2
- Initial release of the new UI

## v0.1.4
- Add dynamic up/down arrows to lookalike domain stats

## v0.1.3
- Replace Date/Time picker from `datetime-local` to new library to ensure support for time entry across all browsers

## v0.1.2
- Add cron scheduler to automatically cleanup reports after 4 hours

## v0.1.1
- Various API updates & added progress bar

## v0.1
- Initial Release, includes base Web Portal for deployment in Azure Websites & populates 52 metrics + 5 charts