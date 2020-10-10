# PollMe
A PHP-based web app for creating online polls. Features include:
- Shareable links with unique word-based IDs.
- Robust login/signup system which allows users to maintain a list of created polls.
- The option to restrict duplicate votes based on either browser session or IP address.

## Deployment

The `poll_app.sql` file is provided to set up a MySQL database, and `poll_app_words.sql` to populate the "words" table which is used to generate unique IDs.

The credentials in `dbh.class` are currently configured for use with an AWS RDS instance.
