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