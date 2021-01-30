# Theatre Planner
The theatre planner is a web application suited for theatre groups who strive to organize their practices.

It allows an admin to create users, assign Roles to users and assign scenes to roles. This way the director always knows which scenes can be practiced at which dates.

## Installation

To install theatre planner, simly dopy all the file onto your webserver and  open the url - it has a setup wizard that will take care of everything for you. Just make sure your webserver you meet the requirements.

### Requirements

I have tested it on my local machine with the following configuration, however I think it works with older versions also (e.g. PHP 7.3)

- PHP-Version 8.0.0
- Write Access for `/php/config.php` and `/browserconfig.xml` (and read access for all files, of course)
- Functioning `mail()`
- MariaDB 10.4.17
- database user with at least `SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP` Privileges for given database

## Support

If you encounter any problems, just file an issue at the [issue tracker](https://github.com/m4lt3/theatre_planner/issues)

## Contibute

Feel free to contribute to the project!

## Acknowledgements

The following third party contents have been used:

- [Semantic UI](https://semantic-ui.com)
- [Datetime picker](https://github.com/xdan/datetimepicker)
- [Mail template](https://assets.wildbit.com/postmark/templates/dist/password_reset.html)
- [Cookie and mask icons by fontawesome](https://fontawesome.com/license/free)

## License

[GPL-3.0 License](./LICENSE.md)
